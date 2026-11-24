<?php

namespace App\Services;

use App\Models\Challenge;
use App\Models\ChallengeSubmission;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\UserDisciplineProgress;
use App\Models\UserSkillProficiency;
use App\Models\UserTitle;

class PlayerProgressOverview
{
    /**
     * Hydrate the player progress overview from live database tables,
     * falling back cleanly to starter defaults for brand-new accounts.
     *
     * @return array{
     *     summary: array{
     *         playerName: string,
     *         equippedTitle: string,
     *         level: int,
     *         totalXp: int,
     *         nextLevelXp: int,
     *         completedChallenges: int,
     *         totalChallenges: int,
     *         currentRegion: string,
     *     },
     *     foundations: array{
     *         completed: int,
     *         total: int,
     *         xp: int,
     *         badgeUnlocked: bool,
     *     },
     *     paths: list<array{
     *         id: string,
     *         name: string,
     *         district: string,
     *         direction: string,
     *         rank: string,
     *         xp: int,
     *         nextRankXp: int,
     *         completed: int,
     *         total: int,
     *         accent: 'emerald'|'indigo'|'amber',
     *         focus: string,
     *     }>,
     *     skills: list<array{
     *         id: string,
     *         name: string,
     *         proficiency: int,
     *         levelLabel: string,
     *         pathHint: string,
     *     }>,
     *     badges: list<array{
     *         id: string,
     *         name: string,
     *         district: string,
     *         requirement: string,
     *         unlocked: bool,
     *     }>,
     *     titles: list<array{
     *         id: string,
     *         name: string,
     *         unlockCondition: string,
     *         unlocked: bool,
     *         equipped: bool,
     *     }>,
     * }
     */
    public function forUser(?User $user): array
    {
        $disciplineRecords = $user !== null
            ? UserDisciplineProgress::query()->where('user_id', $user->id)->get()->keyBy('discipline')
            : collect();

        $skillRecords = $user !== null
            ? UserSkillProficiency::query()->where('user_id', $user->id)->get()->keyBy('skill_key')
            : collect();

        $unlockedBadges = $user !== null
            ? UserBadge::query()->where('user_id', $user->id)->pluck('badge_key')->flip()
            : collect();

        $userTitles = $user !== null
            ? UserTitle::query()->where('user_id', $user->id)->get()->keyBy('title_key')
            : collect();

        $completedChallenges = $user !== null
            ? ChallengeSubmission::query()
                ->where('user_id', $user->id)
                ->where('mode', 'submit')
                ->where('status', 'passed')
                ->distinct('challenge_id')
                ->count('challenge_id')
            : 0;

        $challengeCountsByDiscipline = Challenge::query()
            ->selectRaw('discipline, COUNT(*) as aggregate')
            ->groupBy('discipline')
            ->pluck('aggregate', 'discipline');

        $totalChallenges = max(1, (int) $challengeCountsByDiscipline->sum() ?: 23);

        /** @var UserDisciplineProgress|null $foundationsRecord */
        $foundationsRecord = $disciplineRecords->get('foundations');
        $foundationsCompleted = $foundationsRecord !== null ? $foundationsRecord->completed_count : 0;
        $foundationsXp = $foundationsRecord !== null ? $foundationsRecord->xp : 0;
        $foundationsTotal = max(1, (int) ($challengeCountsByDiscipline->get('foundations') ?? 8));

        $totalXp = (int) $disciplineRecords->sum('xp');
        $level = max(1, intdiv($totalXp, 500) + 1);
        $nextLevelXp = $level * 500;

        $titleCatalog = [
            [
                'id' => 'localhost-apprentice',
                'name' => 'Localhost Apprentice',
                'unlockCondition' => 'Starter title granted upon entering Localhost Town.',
                'starter' => true,
            ],
            [
                'id' => 'algorithm-scout',
                'name' => 'Algorithm Scout',
                'unlockCondition' => "Earn 150+ XP on the Programmer's Path.",
                'starter' => false,
            ],
            [
                'id' => 'artisan-builder',
                'name' => 'Artisan Builder',
                'unlockCondition' => "Earn 150+ XP on the Developer's Path.",
                'starter' => false,
            ],
            [
                'id' => 'harbor-steward',
                'name' => 'Harbor Steward',
                'unlockCondition' => "Earn 150+ XP on the Maintainer's Path.",
                'starter' => false,
            ],
            [
                'id' => 'stack-architect',
                'name' => 'Stack Architect',
                'unlockCondition' => 'Complete at least one challenge in every discipline.',
                'starter' => false,
            ],
        ];

        /** @var UserTitle|null $equippedTitleRecord */
        $equippedTitleRecord = $userTitles->firstWhere('equipped', true);
        $equippedTitleKey = $equippedTitleRecord !== null
            ? $equippedTitleRecord->title_key
            : 'localhost-apprentice';
        $equippedTitleName = 'Localhost Apprentice';

        $titles = [];
        foreach ($titleCatalog as $entry) {
            $unlocked = $entry['starter'] || $userTitles->has($entry['id']);
            $equipped = $unlocked && $entry['id'] === $equippedTitleKey;
            if ($equipped) {
                $equippedTitleName = $entry['name'];
            }

            $titles[] = [
                'id' => $entry['id'],
                'name' => $entry['name'],
                'unlockCondition' => $entry['unlockCondition'],
                'unlocked' => $unlocked,
                'equipped' => $equipped,
            ];
        }

        $pathDefinitions = [
            [
                'id' => 'programmer',
                'name' => "Programmer's Path",
                'district' => 'Algorithm Woods',
                'direction' => 'East',
                'accent' => 'emerald',
                'focus' => 'Logic, algorithms, data structures, and computational thinking.',
            ],
            [
                'id' => 'developer',
                'name' => "Developer's Path",
                'district' => 'Artisan City',
                'direction' => 'North',
                'accent' => 'indigo',
                'focus' => 'CRUD workflows, APIs, validation, and full-stack integration.',
            ],
            [
                'id' => 'maintainer',
                'name' => "Maintainer's Path",
                'district' => 'Legacy Harbor',
                'direction' => 'West',
                'accent' => 'amber',
                'focus' => 'Debugging, refactoring, testing/QA, and codebase reliability.',
            ],
        ];

        $paths = [];
        foreach ($pathDefinitions as $def) {
            /** @var UserDisciplineProgress|null $record */
            $record = $disciplineRecords->get($def['id']);
            $xp = $record !== null ? $record->xp : 0;
            $completed = $record !== null ? $record->completed_count : 0;
            $rank = $record !== null ? $record->rank : 'Novice';
            $totalInPath = max(1, (int) ($challengeCountsByDiscipline->get($def['id']) ?? 5));

            $paths[] = [
                'id' => $def['id'],
                'name' => $def['name'],
                'district' => $def['district'],
                'direction' => $def['direction'],
                'rank' => $rank,
                'xp' => $xp,
                'nextRankXp' => $this->nextRankThreshold($xp),
                'completed' => $completed,
                'total' => $totalInPath,
                'accent' => $def['accent'],
                'focus' => $def['focus'],
            ];
        }

        $skillDefinitions = [
            ['id' => 'algorithms-data-structures', 'name' => 'Algorithms & Data Structures', 'pathHint' => 'Algorithm Woods'],
            ['id' => 'backend-apis', 'name' => 'Backend & APIs', 'pathHint' => 'Artisan City'],
            ['id' => 'frontend-integration', 'name' => 'Frontend & Integration', 'pathHint' => 'Artisan City'],
            ['id' => 'debugging-refactoring', 'name' => 'Debugging & Refactoring', 'pathHint' => 'Legacy Harbor'],
            ['id' => 'testing-security', 'name' => 'Testing & Security', 'pathHint' => 'Legacy Harbor'],
        ];

        $skills = [];
        foreach ($skillDefinitions as $def) {
            /** @var UserSkillProficiency|null $skillRecord */
            $skillRecord = $skillRecords->get($def['id']);
            $proficiency = $skillRecord !== null ? $skillRecord->proficiency : 0;

            $skills[] = [
                'id' => $def['id'],
                'name' => $def['name'],
                'proficiency' => $proficiency,
                'levelLabel' => $this->skillLabel($proficiency),
                'pathHint' => $def['pathHint'],
            ];
        }

        $badges = [
            [
                'id' => 'localhost-pioneer',
                'name' => 'Localhost Pioneer',
                'district' => 'Localhost Town',
                'requirement' => 'Arrive in Localhost Town and inspect the Challenge Center.',
                'unlocked' => true,
            ],
            [
                'id' => 'syntax-awakened',
                'name' => 'Syntax Awakened',
                'district' => 'Localhost Academy',
                'requirement' => 'Complete all 8 zero-knowledge Foundations steps at Localhost Academy.',
                'unlocked' => $unlockedBadges->has('syntax-awakened'),
            ],
            [
                'id' => 'logic-badge',
                'name' => 'Logic Crest',
                'district' => 'Algorithm Woods',
                'requirement' => "Complete a verified trial on the Programmer's Path.",
                'unlocked' => $unlockedBadges->has('logic-badge'),
            ],
            [
                'id' => 'artisan-badge',
                'name' => 'Artisan Emblem',
                'district' => 'Artisan City',
                'requirement' => "Ship a verified build on the Developer's Path.",
                'unlocked' => $unlockedBadges->has('artisan-badge'),
            ],
            [
                'id' => 'harbor-badge',
                'name' => 'Harbor Shield',
                'district' => 'Legacy Harbor',
                'requirement' => "Resolve a defect or audit on the Maintainer's Path.",
                'unlocked' => $unlockedBadges->has('harbor-badge'),
            ],
            [
                'id' => 'stack-master',
                'name' => 'Stack Region Champion',
                'district' => 'The Stack Region',
                'requirement' => 'Earn all three discipline badges across the Stack Region.',
                'unlocked' => $unlockedBadges->has('stack-master'),
            ],
        ];

        return [
            'summary' => [
                'playerName' => $user !== null ? $user->name : 'Adventurer',
                'equippedTitle' => $equippedTitleName,
                'level' => $level,
                'totalXp' => $totalXp,
                'nextLevelXp' => $nextLevelXp,
                'completedChallenges' => $completedChallenges,
                'totalChallenges' => $totalChallenges,
                'currentRegion' => 'Localhost Town',
            ],
            'foundations' => [
                'completed' => $foundationsCompleted,
                'total' => $foundationsTotal,
                'xp' => $foundationsXp,
                'badgeUnlocked' => $unlockedBadges->has('syntax-awakened'),
            ],
            'paths' => $paths,
            'skills' => $skills,
            'badges' => $badges,
            'titles' => $titles,
        ];
    }

    private function nextRankThreshold(int $xp): int
    {
        return match (true) {
            $xp >= 600 => 1000,
            $xp >= 350 => 600,
            $xp >= 150 => 350,
            default => 150,
        };
    }

    private function skillLabel(int $proficiency): string
    {
        return match (true) {
            $proficiency >= 75 => 'Proficient',
            $proficiency >= 50 => 'Capable',
            $proficiency >= 25 => 'Practicing',
            default => 'Starter',
        };
    }
}
