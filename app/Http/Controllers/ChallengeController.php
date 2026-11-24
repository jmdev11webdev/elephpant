<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChallengeRunRequest;
use App\Models\Challenge;
use App\Models\ChallengeSubmission;
use App\Models\User;
use App\Services\ChallengeGradingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChallengeController extends Controller
{
    public function __construct(
        private readonly ChallengeGradingService $grading,
    ) {}

    /**
     * List all synced challenges with the authenticated player's completion state.
     * Hidden tests and solution code are never returned.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $completedIds = ChallengeSubmission::query()
            ->where('user_id', $user->id)
            ->where('mode', 'submit')
            ->where('status', 'passed')
            ->distinct()
            ->pluck('challenge_id')
            ->flip();

        $challenges = Challenge::query()
            ->orderByRaw("CASE difficulty WHEN 'foundations' THEN 0 WHEN 'beginner' THEN 1 WHEN 'intermediate' THEN 2 WHEN 'advanced' THEN 3 WHEN 'expert' THEN 4 ELSE 5 END")
            ->orderByRaw('COALESCE(sort_order, 9999)')
            ->orderBy('title')
            ->get()
            ->map(static fn (Challenge $challenge): array => [
                'id' => $challenge->id,
                'slug' => $challenge->slug,
                'title' => $challenge->title,
                'discipline' => $challenge->discipline,
                'district' => $challenge->district,
                'category' => $challenge->category,
                'difficulty' => $challenge->difficulty,
                'order' => $challenge->sort_order,
                'evaluationType' => $challenge->evaluation_type,
                'supportedLanguages' => $challenge->supported_languages,
                'xpReward' => $challenge->xp_reward,
                'estimatedMinutes' => $challenge->estimated_minutes,
                'skillKey' => $challenge->skill_key,
                'mapTriggerId' => $challenge->map_trigger_id,
                'terminalKey' => $challenge->terminal_key,
                'completed' => $completedIds->has($challenge->id),
            ])
            ->values();

        return response()->json([
            'challenges' => $challenges,
        ]);
    }

    /**
     * Return a single challenge's workspace payload (prompt, starter files, public tests, hints).
     * Strictly excludes server-only hidden_tests and correct_decision_id.
     */
    public function show(Request $request, Challenge $challenge): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $latestSubmission = ChallengeSubmission::query()
            ->where('user_id', $user->id)
            ->where('challenge_id', $challenge->id)
            ->latest('id')
            ->first();

        $completed = ChallengeSubmission::query()
            ->where('user_id', $user->id)
            ->where('challenge_id', $challenge->id)
            ->where('mode', 'submit')
            ->where('status', 'passed')
            ->exists();

        $markdownOptions = [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ];

        $descriptionHtml = Str::markdown($challenge->prompt_markdown, $markdownOptions);
        $hintsHtml = array_map(
            static fn (string $hint): string => Str::markdown($hint, $markdownOptions),
            $challenge->hints,
        );

        return response()->json([
            'challenge' => [
                'id' => $challenge->id,
                'slug' => $challenge->slug,
                'title' => $challenge->title,
                'discipline' => $challenge->discipline,
                'district' => $challenge->district,
                'category' => $challenge->category,
                'difficulty' => $challenge->difficulty,
                'order' => $challenge->sort_order,
                'evaluationType' => $challenge->evaluation_type,
                'supportedLanguages' => $challenge->supported_languages,
                'xpReward' => $challenge->xp_reward,
                'estimatedMinutes' => $challenge->estimated_minutes,
                'skillKey' => $challenge->skill_key,
                'mapTriggerId' => $challenge->map_trigger_id,
                'terminalKey' => $challenge->terminal_key,
                'promptMarkdown' => $challenge->prompt_markdown,
                'description_html' => $descriptionHtml,
                'starterFiles' => $challenge->starter_files,
                'hints' => $challenge->hints,
                'hints_html' => $hintsHtml,
                'decisionOptions' => $challenge->decision_options,
                'publicTests' => $challenge->public_tests,
                'completed' => $completed,
                'savedFiles' => $latestSubmission?->files_json,
                'savedDecisionChoice' => $latestSubmission?->decision_choice,
            ],
        ]);
    }

    /**
     * Execute player code against public tests only via the isolated sandbox.
     */
    public function run(ChallengeRunRequest $request, Challenge $challenge): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        /** @var array<string, string> $files */
        $files = $request->validated('files');
        $language = (string) ($request->validated('language') ?? 'php');

        $result = $this->grading->runPublic($user, $challenge, $files, $language);

        return response()->json($result);
    }

    /**
     * Grade player code against public + server-only hidden tests and update progression.
     */
    public function submit(ChallengeRunRequest $request, Challenge $challenge): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        /** @var array<string, string> $files */
        $files = $request->validated('files');
        $language = (string) ($request->validated('language') ?? 'php');
        $decisionChoice = $request->validated('decision_choice');

        $result = $this->grading->submit(
            $user,
            $challenge,
            $files,
            $language,
            is_string($decisionChoice) ? $decisionChoice : null,
        );

        return response()->json($result);
    }
}
