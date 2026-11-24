<?php

namespace App\Services;

use App\Models\Challenge;
use App\Models\ChallengeSubmission;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\UserDisciplineProgress;
use App\Models\UserSkillProficiency;
use App\Models\UserTitle;
use App\Models\UserWorldTrigger;
use Illuminate\Support\Collection;

class ProgressionService
{
    /**
     * Record a passing graded submission and award XP, discipline rank,
     * skill proficiency, badges, titles, and unlocked world triggers.
     *
     * @return array{
     *     xpAwarded: int,
     *     firstCompletion: bool,
     *     disciplineRank: string,
     *     unlockedBadges: list<string>,
     *     unlockedTitles: list<string>,
     *     unlockedTriggerId: string|null,
     * }
     */
    public function recordPassedSubmission(User $user, Challenge $challenge): array
    {
        $alreadySolved = ChallengeSubmission::query()
            ->where('user_id', $user->id)
            ->where('challenge_id', $challenge->id)
            ->where('mode', 'submit')
            ->where('status', 'passed')
            ->exists();

        $this->ensureStarterUnlocks($user);

        /** @var UserDisciplineProgress $disciplineProgress */
        $disciplineProgress = UserDisciplineProgress::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'discipline' => $challenge->discipline,
            ],
            [
                'xp' => 0,
                'rank' => 'Novice',
                'completed_count' => 0,
                'current_streak' => 0,
                'best_streak' => 0,
            ],
        );

        $xpAwarded = 0;
        $unlockedBadges = [];
        $unlockedTitles = [];
        $unlockedTriggerId = null;

        if (! $alreadySolved) {
            $xpAwarded = $challenge->xp_reward;
            $disciplineProgress->xp += $xpAwarded;
            $disciplineProgress->completed_count += 1;
            $disciplineProgress->current_streak += 1;
            $disciplineProgress->best_streak = max(
                $disciplineProgress->best_streak,
                $disciplineProgress->current_streak,
            );
            $disciplineProgress->rank = $this->rankForXp($disciplineProgress->xp);
            $disciplineProgress->save();

            /** @var UserSkillProficiency $skill */
            $skill = UserSkillProficiency::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'skill_key' => $challenge->skill_key,
                ],
                [
                    'proficiency' => 0,
                    'completed_count' => 0,
                ],
            );

            $skill->completed_count += 1;
            $skill->proficiency = min(100, $skill->completed_count * 25);
            $skill->save();

            $triggerKey = $challenge->terminal_key ?: $challenge->map_trigger_id;

            if ($triggerKey !== null && $triggerKey !== '') {
                $trigger = UserWorldTrigger::query()->firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'trigger_id' => $triggerKey,
                    ],
                    [
                        'challenge_id' => $challenge->id,
                        'unlocked_at' => now(),
                    ],
                );

                $unlockedTriggerId = $trigger->trigger_id;
            }

            $unlockedBadges = $this->evaluateBadges($user);
            $unlockedTitles = $this->evaluateTitles($user);
        }

        return [
            'xpAwarded' => $xpAwarded,
            'firstCompletion' => ! $alreadySolved,
            'disciplineRank' => $disciplineProgress->rank,
            'unlockedBadges' => $unlockedBadges,
            'unlockedTitles' => $unlockedTitles,
            'unlockedTriggerId' => $unlockedTriggerId,
        ];
    }

    public function resetDisciplineStreak(User $user, string $discipline): void
    {
        UserDisciplineProgress::query()
            ->where('user_id', $user->id)
            ->where('discipline', $discipline)
            ->update(['current_streak' => 0]);
    }

    public function ensureStarterUnlocks(User $user): void
    {
        UserBadge::query()->firstOrCreate(
            ['user_id' => $user->id, 'badge_key' => 'localhost-pioneer'],
            ['unlocked_at' => now()],
        );

        $hasEquippedTitle = UserTitle::query()
            ->where('user_id', $user->id)
            ->where('equipped', true)
            ->exists();

        UserTitle::query()->firstOrCreate(
            ['user_id' => $user->id, 'title_key' => 'localhost-apprentice'],
            [
                'equipped' => ! $hasEquippedTitle,
                'unlocked_at' => now(),
            ],
        );
    }

    public function rankForXp(int $xp): string
    {
        return match (true) {
            $xp >= 600 => 'Master',
            $xp >= 350 => 'Artisan',
            $xp >= 150 => 'Apprentice',
            default => 'Novice',
        };
    }

    /**
     * @return list<string>
     */
    private function evaluateBadges(User $user): array
    {
        $newlyUnlocked = [];
        $progressByDiscipline = UserDisciplineProgress::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('discipline');

        $foundationsTotal = max(1, Challenge::query()->where('discipline', 'foundations')->count() ?: 8);

        $rules = [
            'syntax-awakened' => $this->completedFor($progressByDiscipline, 'foundations') >= $foundationsTotal,
            'logic-badge' => $this->completedFor($progressByDiscipline, 'programmer') >= 1,
            'artisan-badge' => $this->completedFor($progressByDiscipline, 'developer') >= 1,
            'harbor-badge' => $this->completedFor($progressByDiscipline, 'maintainer') >= 1,
        ];

        foreach ($rules as $badgeKey => $eligible) {
            if ($eligible && $this->unlockBadge($user, $badgeKey)) {
                $newlyUnlocked[] = $badgeKey;
            }
        }

        if ($rules['logic-badge'] && $rules['artisan-badge'] && $rules['harbor-badge']) {
            if ($this->unlockBadge($user, 'stack-master')) {
                $newlyUnlocked[] = 'stack-master';
            }
        }

        return $newlyUnlocked;
    }

    /**
     * @return list<string>
     */
    private function evaluateTitles(User $user): array
    {
        $newlyUnlocked = [];
        $progressByDiscipline = UserDisciplineProgress::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('discipline');

        $rules = [
            'algorithm-scout' => $this->xpFor($progressByDiscipline, 'programmer') >= 150,
            'artisan-builder' => $this->xpFor($progressByDiscipline, 'developer') >= 150,
            'harbor-steward' => $this->xpFor($progressByDiscipline, 'maintainer') >= 150,
            'stack-architect' => $this->completedFor($progressByDiscipline, 'programmer') >= 1
                && $this->completedFor($progressByDiscipline, 'developer') >= 1
                && $this->completedFor($progressByDiscipline, 'maintainer') >= 1,
        ];

        foreach ($rules as $titleKey => $eligible) {
            if ($eligible && $this->unlockTitle($user, $titleKey)) {
                $newlyUnlocked[] = $titleKey;
            }
        }

        return $newlyUnlocked;
    }

    private function unlockBadge(User $user, string $badgeKey): bool
    {
        $badge = UserBadge::query()->firstOrCreate(
            ['user_id' => $user->id, 'badge_key' => $badgeKey],
            ['unlocked_at' => now()],
        );

        return $badge->wasRecentlyCreated;
    }

    private function unlockTitle(User $user, string $titleKey): bool
    {
        $title = UserTitle::query()->firstOrCreate(
            ['user_id' => $user->id, 'title_key' => $titleKey],
            ['equipped' => false, 'unlocked_at' => now()],
        );

        return $title->wasRecentlyCreated;
    }

    /**
     * @param  Collection<string, UserDisciplineProgress>  $progressByDiscipline
     */
    private function completedFor(Collection $progressByDiscipline, string $discipline): int
    {
        $record = $progressByDiscipline->get($discipline);

        return $record !== null ? $record->completed_count : 0;
    }

    /**
     * @param  Collection<string, UserDisciplineProgress>  $progressByDiscipline
     */
    private function xpFor(Collection $progressByDiscipline, string $discipline): int
    {
        $record = $progressByDiscipline->get($discipline);

        return $record !== null ? $record->xp : 0;
    }
}
