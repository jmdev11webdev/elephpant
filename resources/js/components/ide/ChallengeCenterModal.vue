<script setup lang="ts">
import {
    AlertTriangle,
    ArrowLeft,
    BookOpen,
    CheckCircle2,
    Code2,
    FileCode2,
    GitCompareArrows,
    Lightbulb,
    Play,
    RotateCcw,
    Send,
    Terminal,
    Trophy,
    Wrench,
    X,
    XCircle,
} from '@lucide/vue';
import { computed, onMounted, ref } from 'vue';
import MonacoCodeEditor from '@/components/ide/MonacoCodeEditor.vue';
import { audioManager } from '@/game/audio/AudioManager';
import type { ResumeOverworldPayload } from '@/game/events';
import {
    index as challengesIndex,
    run as challengeRun,
    show as challengeShow,
    submit as challengeSubmit,
} from '@/routes/challenges';

interface ChallengeListItem {
    id: number;
    slug: string;
    title: string;
    discipline: 'foundations' | 'programmer' | 'developer' | 'maintainer';
    category: string;
    difficulty:
        | 'foundations'
        | 'beginner'
        | 'intermediate'
        | 'advanced'
        | 'expert';
    order: number | null;
    evaluationType: string;
    supportedLanguages: string[];
    xpReward: number;
    estimatedMinutes: number;
    skillKey: string;
    mapTriggerId: string | null;
    completed: boolean;
}

interface DecisionOption {
    id: string;
    label: string;
}

interface ChallengeDetail extends ChallengeListItem {
    promptMarkdown: string;
    description_html: string;
    starterFiles: Record<string, string>;
    hints: string[];
    hints_html: string[];
    decisionOptions: DecisionOption[] | null;
    publicTests: string;
    savedFiles: Record<string, string> | null;
    savedDecisionChoice: string | null;
}

interface ExecutionCase {
    name: string;
    passed: boolean;
    expected: string | null;
    actual: string | null;
    error: string | null;
    scope: string;
}

interface ExecutionRewards {
    xpAwarded: number;
    firstCompletion: boolean;
    disciplineRank: string | null;
    unlockedBadges: string[];
    unlockedTitles: string[];
    unlockedTriggerId: string | null;
}

interface ExecutionResponse {
    mode: 'public' | 'submit';
    status: 'passed' | 'failed' | 'timeout' | 'error';
    passedTests: number;
    totalTests: number;
    executionMs: number;
    cases: ExecutionCase[];
    stderr: string;
    errorMessage: string | null;
    rewards?: ExecutionRewards;
}

const props = defineProps<{
    initialChallengeSlug?: string | null;
    initialDisciplineFilter?: string | null;
}>();

const emit = defineEmits<{
    close: [payload: ResumeOverworldPayload];
}>();

const DISCIPLINES = [
    { id: 'foundations', label: 'Foundations (Zero-Knowledge)' },
    { id: 'programmer', label: "Programmer's Path" },
    { id: 'developer', label: "Developer's Path" },
    { id: 'maintainer', label: "Maintainer's Path" },
] as const;

const DIFFICULTIES = [
    'all',
    'foundations',
    'beginner',
    'intermediate',
    'advanced',
    'expert',
] as const;

const HINT_TIER_LABELS = [
    'Concept Nudge',
    'Syntax Pattern',
    'Near-Complete Example',
] as const;

const challenges = ref<ChallengeListItem[]>([]);
const isLoadingList = ref(false);
const listError = ref<string | null>(null);

const selectedDisciplines = ref<string[]>([
    'foundations',
    'programmer',
    'developer',
    'maintainer',
]);
const selectedDifficulty = ref<string>('all');

const activeChallenge = ref<ChallengeDetail | null>(null);
const isLoadingChallenge = ref(false);
const editorFiles = ref<Record<string, string>>({});
const activeFileName = ref<string>('');
const diffMode = ref(false);
const revealedHintCount = ref(0);
const selectedDecision = ref<string>('');

const isExecuting = ref(false);
const executionResult = ref<ExecutionResponse | null>(null);
const lastCompletionPayload = ref<ResumeOverworldPayload>({});

const filteredChallenges = computed(() =>
    challenges.value.filter((item) => {
        const matchesDiscipline =
            selectedDisciplines.value.length === 0 ||
            selectedDisciplines.value.includes(item.discipline);
        const matchesDifficulty =
            selectedDifficulty.value === 'all' ||
            item.difficulty === selectedDifficulty.value;

        return matchesDiscipline && matchesDifficulty;
    }),
);

const fileNames = computed(() => Object.keys(editorFiles.value));

const activeFileContent = computed({
    get: (): string => editorFiles.value[activeFileName.value] ?? '',
    set: (value: string): void => {
        if (activeFileName.value !== '') {
            editorFiles.value = {
                ...editorFiles.value,
                [activeFileName.value]: value,
            };
        }
    },
});

const activeFileLanguage = computed(() => {
    const name = activeFileName.value.toLowerCase();

    if (name.endsWith('.ts') || name.endsWith('.tsx')) {
        return 'typescript';
    }

    if (name.endsWith('.sql')) {
        return 'sql';
    }

    return 'php';
});

function getXsrfToken(): string {
    const match = document.cookie
        .split('; ')
        .find((row) => row.startsWith('XSRF-TOKEN='));

    return match ? decodeURIComponent(match.split('=')[1] ?? '') : '';
}

function toggleDiscipline(disciplineId: string): void {
    if (selectedDisciplines.value.includes(disciplineId)) {
        selectedDisciplines.value = selectedDisciplines.value.filter(
            (id) => id !== disciplineId,
        );
    } else {
        selectedDisciplines.value = [
            ...selectedDisciplines.value,
            disciplineId,
        ];
    }
}

async function fetchChallenges(): Promise<void> {
    isLoadingList.value = true;
    listError.value = null;

    try {
        const response = await fetch(challengesIndex().url, {
            headers: {
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(`Failed to load challenges (${response.status}).`);
        }

        const data = (await response.json()) as {
            challenges: ChallengeListItem[];
        };
        challenges.value = data.challenges;
    } catch (error) {
        listError.value =
            error instanceof Error
                ? error.message
                : 'Unable to load Challenge Center.';
    } finally {
        isLoadingList.value = false;
    }
}

async function openChallenge(slug: string): Promise<void> {
    isLoadingChallenge.value = true;
    executionResult.value = null;
    diffMode.value = false;
    revealedHintCount.value = 0;

    try {
        const response = await fetch(challengeShow(slug).url, {
            headers: {
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(`Failed to load challenge (${response.status}).`);
        }

        const data = (await response.json()) as { challenge: ChallengeDetail };
        activeChallenge.value = data.challenge;

        const initialFiles =
            data.challenge.savedFiles &&
            Object.keys(data.challenge.savedFiles).length > 0
                ? {
                      ...data.challenge.starterFiles,
                      ...data.challenge.savedFiles,
                  }
                : { ...data.challenge.starterFiles };

        editorFiles.value = initialFiles;
        activeFileName.value = Object.keys(initialFiles)[0] ?? '';
        selectedDecision.value = data.challenge.savedDecisionChoice ?? '';
    } finally {
        isLoadingChallenge.value = false;
    }
}

function resetStarterCode(): void {
    if (!activeChallenge.value) {
        return;
    }

    editorFiles.value = { ...activeChallenge.value.starterFiles };
    activeFileName.value = Object.keys(editorFiles.value)[0] ?? '';
    executionResult.value = null;
}

async function executeCode(mode: 'public' | 'submit'): Promise<void> {
    if (!activeChallenge.value || isExecuting.value) {
        return;
    }

    isExecuting.value = true;
    executionResult.value = null;
    audioManager.playSfx('testRun');

    const endpoint =
        mode === 'submit'
            ? challengeSubmit(activeChallenge.value.slug).url
            : challengeRun(activeChallenge.value.slug).url;

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': getXsrfToken(),
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                language: activeChallenge.value.supportedLanguages[0] ?? 'php',
                files: editorFiles.value,
                decision_choice: selectedDecision.value || null,
            }),
        });

        const data = (await response.json()) as ExecutionResponse;
        executionResult.value = data;

        if (data.status === 'passed') {
            audioManager.playSfx(
                mode === 'submit' ? 'terminalUnlock' : 'testPass',
            );
        } else {
            audioManager.playSfx('testFail');
        }

        if (mode === 'submit' && data.status === 'passed') {
            activeChallenge.value = {
                ...activeChallenge.value,
                completed: true,
            };

            challenges.value = challenges.value.map((item) =>
                item.slug === activeChallenge.value?.slug
                    ? { ...item, completed: true }
                    : item,
            );

            lastCompletionPayload.value = {
                completedChallengeId: activeChallenge.value.slug,
                unlockedTriggerId: data.rewards?.unlockedTriggerId ?? null,
                xpAwarded: data.rewards?.xpAwarded ?? 0,
            };
        }
    } catch (error) {
        executionResult.value = {
            mode,
            status: 'error',
            passedTests: 0,
            totalTests: 0,
            executionMs: 0,
            cases: [],
            stderr: '',
            errorMessage:
                error instanceof Error
                    ? error.message
                    : 'Failed to reach grading service.',
        };
    } finally {
        isExecuting.value = false;
    }
}

function handleClose(): void {
    emit('close', lastCompletionPayload.value);
}

function revealNextHint(): void {
    if (!activeChallenge.value) {
        return;
    }

    if (revealedHintCount.value < activeChallenge.value.hints.length) {
        revealedHintCount.value += 1;
    }
}

function nextHintLabel(): string {
    const idx = revealedHintCount.value;
    const tier = HINT_TIER_LABELS[idx] ?? `Hint ${idx + 1}`;
    const total = activeChallenge.value?.hints.length ?? 0;

    return `Reveal Hint ${idx + 1}/${total} (${tier})`;
}

onMounted(async () => {
    if (props.initialDisciplineFilter) {
        selectedDisciplines.value = [props.initialDisciplineFilter];
    }

    await fetchChallenges();

    if (props.initialChallengeSlug) {
        await openChallenge(props.initialChallengeSlug);
    }
});
</script>

<template>
    <div
        role="dialog"
        aria-modal="true"
        aria-label="Challenge Center and Code Workspace"
        data-testid="challenge-center-modal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/90 p-2 backdrop-blur-xs sm:p-4"
        @keydown.stop
    >
        <div
            class="flex h-[92vh] w-full max-w-7xl flex-col overflow-hidden rounded-xl retro-bezel text-slate-100"
        >
            <!-- Top Bar -->
            <header
                class="flex shrink-0 items-center justify-between border-b border-game-border bg-game-panel px-4 py-2.5 font-mono"
            >
                <div class="flex items-center gap-3">
                    <button
                        v-if="activeChallenge"
                        type="button"
                        data-testid="back-to-catalog-button"
                        @click="
                            activeChallenge = null;
                            executionResult = null;
                        "
                        class="inline-flex cursor-pointer items-center gap-1 rounded border border-game-border bg-game-surface px-2.5 py-1 text-xs text-slate-200 transition hover:border-indigo-400"
                    >
                        <ArrowLeft class="size-3.5" />
                        Catalog
                    </button>

                    <div class="flex items-center gap-2">
                        <Terminal class="size-4 text-game-cyan" />
                        <span class="text-sm font-bold text-white">
                            {{
                                activeChallenge
                                    ? activeChallenge.title
                                    : 'Challenge Center'
                            }}
                        </span>
                        <span
                            v-if="activeChallenge"
                            class="hidden rounded border border-indigo-400/40 bg-indigo-950/80 px-2 py-0.5 text-[10px] text-indigo-200 uppercase sm:inline"
                        >
                            {{
                                activeChallenge.discipline === 'foundations' &&
                                activeChallenge.order
                                    ? `Step ${activeChallenge.order}/8`
                                    : activeChallenge.category
                            }}
                            • +{{ activeChallenge.xpReward }} XP
                        </span>
                    </div>
                </div>

                <button
                    type="button"
                    data-testid="close-challenge-center"
                    @click="handleClose"
                    class="inline-flex cursor-pointer items-center gap-1.5 rounded border border-game-border bg-game-surface px-2.5 py-1 text-xs text-slate-300 transition hover:border-rose-400 hover:text-white"
                >
                    <X class="size-3.5" />
                    Return to World
                </button>
            </header>

            <!-- VIEW 1: CHALLENGE CENTER CATALOG -->
            <div
                v-if="!activeChallenge"
                class="flex flex-1 flex-col gap-4 overflow-y-auto p-4 sm:p-6"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div
                        class="flex flex-wrap items-center gap-2 font-mono text-xs"
                    >
                        <button
                            v-for="disc in DISCIPLINES"
                            :key="disc.id"
                            type="button"
                            :data-testid="`discipline-filter-${disc.id}`"
                            @click="toggleDiscipline(disc.id)"
                            class="inline-flex cursor-pointer items-center gap-1.5 rounded-md border px-3 py-1.5 transition"
                            :class="
                                selectedDisciplines.includes(disc.id)
                                    ? 'border-indigo-400 bg-indigo-600 font-bold text-white'
                                    : 'border-game-border bg-game-panel text-slate-400 hover:text-white'
                            "
                        >
                            <BookOpen
                                v-if="disc.id === 'foundations'"
                                class="size-3.5"
                            />
                            <Code2
                                v-else-if="disc.id === 'programmer'"
                                class="size-3.5"
                            />
                            <Terminal
                                v-else-if="disc.id === 'developer'"
                                class="size-3.5"
                            />
                            <Wrench v-else class="size-3.5" />
                            {{ disc.label }}
                        </button>
                    </div>

                    <div
                        class="flex flex-wrap items-center gap-1.5 font-mono text-xs"
                    >
                        <button
                            v-for="diff in DIFFICULTIES"
                            :key="diff"
                            type="button"
                            :data-testid="`difficulty-filter-${diff}`"
                            @click="selectedDifficulty = diff"
                            class="cursor-pointer rounded border px-2.5 py-1 capitalize transition"
                            :class="
                                selectedDifficulty === diff
                                    ? 'border-amber-400 bg-amber-950/70 font-bold text-amber-200'
                                    : 'border-game-border bg-game-panel text-slate-400 hover:text-white'
                            "
                        >
                            {{ diff }}
                        </button>
                    </div>
                </div>

                <div
                    v-if="listError"
                    class="rounded-lg border border-rose-500/40 bg-rose-950/40 p-4 font-mono text-xs text-rose-200"
                >
                    {{ listError }}
                </div>

                <div
                    v-else-if="isLoadingList"
                    class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="n in 6"
                        :key="n"
                        class="h-36 animate-pulse rounded-xl border border-game-border bg-game-panel"
                    />
                </div>

                <div
                    v-else
                    data-testid="challenge-catalog-grid"
                    class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <button
                        v-for="item in filteredChallenges"
                        :key="item.slug"
                        type="button"
                        :data-testid="`challenge-card-${item.slug}`"
                        @click="openChallenge(item.slug)"
                        class="group flex cursor-pointer flex-col justify-between rounded-xl border-2 border-game-border bg-game-panel p-4 text-left transition hover:border-indigo-400"
                    >
                        <div class="space-y-2">
                            <div
                                class="flex items-center justify-between gap-2"
                            >
                                <span
                                    class="rounded border px-2 py-0.5 font-mono text-[10px] font-bold uppercase"
                                    :class="
                                        item.discipline === 'foundations'
                                            ? 'border-cyan-400/50 bg-cyan-950/70 text-cyan-200'
                                            : 'border-indigo-400/40 bg-indigo-950/70 text-indigo-200'
                                    "
                                >
                                    {{
                                        item.discipline === 'foundations' &&
                                        item.order
                                            ? `Academy Step ${item.order}/8`
                                            : `${item.discipline} • ${item.difficulty}`
                                    }}
                                </span>
                                <span
                                    v-if="item.completed"
                                    class="inline-flex items-center gap-1 font-mono text-[10px] font-bold text-game-emerald"
                                >
                                    <CheckCircle2 class="size-3.5" />
                                    SOLVED
                                </span>
                            </div>

                            <h3
                                class="font-mono text-sm font-bold text-white group-hover:text-indigo-300"
                            >
                                {{ item.title }}
                            </h3>
                            <p class="text-xs text-slate-400">
                                {{ item.category }} •
                                {{ item.evaluationType.replace('_', ' ') }}
                            </p>
                        </div>

                        <div
                            class="mt-4 flex items-center justify-between border-t border-game-border pt-2.5 font-mono text-[11px] text-slate-400"
                        >
                            <span class="text-game-amber">
                                +{{ item.xpReward }} XP
                            </span>
                            <span class="text-indigo-300 group-hover:underline">
                                Open Workspace →
                            </span>
                        </div>
                    </button>
                </div>
            </div>

            <!-- VIEW 2: EMBEDDED MONACO IDE WORKSPACE -->
            <div
                v-else
                class="grid min-h-0 flex-1 grid-cols-1 overflow-hidden lg:grid-cols-12"
            >
                <!-- Left Problem Panel -->
                <aside
                    class="flex min-h-0 flex-col gap-4 overflow-y-auto border-b border-game-border bg-game-panel/80 p-4 lg:col-span-4 lg:border-r lg:border-b-0"
                >
                    <div
                        data-testid="challenge-meta-badges"
                        class="flex flex-wrap items-center gap-2 font-mono text-[11px]"
                    >
                        <span
                            class="rounded border px-2 py-0.5 uppercase"
                            :class="
                                activeChallenge.discipline === 'foundations'
                                    ? 'border-cyan-400/50 bg-cyan-950/70 text-cyan-200'
                                    : 'border-indigo-400/40 bg-indigo-950 text-indigo-200'
                            "
                        >
                            {{
                                activeChallenge.discipline === 'foundations'
                                    ? 'Foundations'
                                    : activeChallenge.discipline
                            }}
                        </span>
                        <span
                            v-if="
                                activeChallenge.difficulty !==
                                activeChallenge.discipline
                            "
                            class="rounded border border-amber-400/40 bg-amber-950/60 px-2 py-0.5 text-amber-200 uppercase"
                        >
                            {{ activeChallenge.difficulty }}
                        </span>
                        <span
                            v-if="activeChallenge.completed"
                            class="inline-flex items-center gap-1 rounded border border-emerald-400/40 bg-emerald-950/60 px-2 py-0.5 text-emerald-200"
                        >
                            <CheckCircle2 class="size-3" />
                            Completed
                        </span>
                    </div>

                    <div
                        data-testid="challenge-description-prose"
                        class="challenge-prose"
                        v-html="activeChallenge.description_html"
                    />

                    <!-- Technical Decision Selector (when applicable) -->
                    <div
                        v-if="
                            activeChallenge.decisionOptions &&
                            activeChallenge.decisionOptions.length > 0
                        "
                        class="space-y-2 rounded-lg border border-indigo-400/40 bg-game-surface p-3"
                    >
                        <p
                            class="font-mono text-xs font-bold text-indigo-300 uppercase"
                        >
                            Technical Decision Required
                        </p>
                        <label
                            v-for="option in activeChallenge.decisionOptions"
                            :key="option.id"
                            class="flex cursor-pointer items-start gap-2 rounded border border-game-border p-2 text-xs transition hover:border-indigo-400"
                        >
                            <input
                                v-model="selectedDecision"
                                type="radio"
                                :value="option.id"
                                class="mt-0.5 accent-indigo-500"
                            />
                            <span>{{ option.label }}</span>
                        </label>
                    </div>

                    <!-- Progressive 3-Step Hints -->
                    <div
                        v-if="activeChallenge.hints.length > 0"
                        data-testid="progressive-hints-panel"
                        class="space-y-2.5 rounded-lg border border-game-border bg-game-surface p-3"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <span
                                class="inline-flex items-center gap-1.5 font-mono text-xs font-bold text-game-amber"
                            >
                                <Lightbulb class="size-3.5" />
                                Progressive Hints ({{ revealedHintCount }}/{{
                                    activeChallenge.hints.length
                                }})
                            </span>
                            <button
                                v-if="revealedHintCount > 0"
                                type="button"
                                @click="revealedHintCount = 0"
                                class="cursor-pointer font-mono text-[10px] text-slate-400 underline hover:text-slate-200"
                            >
                                Hide All
                            </button>
                        </div>

                        <ul
                            v-if="revealedHintCount > 0"
                            class="space-y-2 border-t border-game-border pt-2 text-xs text-slate-200"
                        >
                            <li
                                v-for="(
                                    hint, idx
                                ) in activeChallenge.hints.slice(
                                    0,
                                    revealedHintCount,
                                )"
                                :key="idx"
                                data-testid="revealed-hint-item"
                                class="rounded border border-amber-400/25 bg-amber-950/25 px-2.5 py-2"
                            >
                                <span
                                    class="mb-1 block font-mono text-[10px] font-bold text-game-amber uppercase"
                                >
                                    Hint {{ idx + 1 }} •
                                    {{
                                        HINT_TIER_LABELS[idx] ??
                                        `Step ${idx + 1}`
                                    }}
                                </span>
                                <div
                                    class="challenge-prose challenge-hint-prose"
                                    v-html="
                                        activeChallenge.hints_html?.[idx] ??
                                        hint
                                    "
                                />
                            </li>
                        </ul>

                        <button
                            v-if="
                                revealedHintCount < activeChallenge.hints.length
                            "
                            type="button"
                            data-testid="reveal-next-hint-button"
                            @click="revealNextHint"
                            class="w-full cursor-pointer rounded border border-amber-400/50 bg-amber-950/50 px-2.5 py-1.5 font-mono text-[11px] font-bold text-amber-200 transition hover:bg-amber-900/60"
                        >
                            {{ nextHintLabel() }}
                        </button>
                    </div>
                </aside>

                <!-- Right Editor + Test Console -->
                <div class="flex min-h-0 flex-col lg:col-span-8">
                    <!-- File Tabs & Editor Toolbar -->
                    <div
                        class="flex shrink-0 flex-wrap items-center justify-between gap-2 border-b border-game-border bg-game-panel px-3 py-2 font-mono text-xs"
                    >
                        <div class="flex flex-wrap items-center gap-1">
                            <button
                                v-for="fileName in fileNames"
                                :key="fileName"
                                type="button"
                                @click="activeFileName = fileName"
                                class="inline-flex cursor-pointer items-center gap-1.5 rounded px-2.5 py-1 transition"
                                :class="
                                    activeFileName === fileName
                                        ? 'border border-indigo-400/60 bg-game-surface font-bold text-white'
                                        : 'text-slate-400 hover:text-white'
                                "
                            >
                                <FileCode2 class="size-3.5 text-indigo-400" />
                                {{ fileName }}
                            </button>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <button
                                type="button"
                                data-testid="toggle-diff-view"
                                @click="diffMode = !diffMode"
                                class="inline-flex cursor-pointer items-center gap-1 rounded border px-2 py-1 text-[11px] transition"
                                :class="
                                    diffMode
                                        ? 'border-cyan-400 bg-cyan-950/70 text-cyan-200'
                                        : 'border-game-border bg-game-surface text-slate-300 hover:border-indigo-400'
                                "
                            >
                                <GitCompareArrows class="size-3.5" />
                                {{ diffMode ? 'Exit Diff' : 'Diff View' }}
                            </button>

                            <button
                                type="button"
                                @click="resetStarterCode"
                                class="inline-flex cursor-pointer items-center gap-1 rounded border border-game-border bg-game-surface px-2 py-1 text-[11px] text-slate-300 transition hover:border-amber-400 hover:text-amber-200"
                            >
                                <RotateCcw class="size-3" />
                                Reset
                            </button>
                        </div>
                    </div>

                    <!-- Monaco Editor -->
                    <div class="min-h-0 flex-1">
                        <MonacoCodeEditor
                            v-model="activeFileContent"
                            :original-value="
                                activeChallenge.starterFiles[activeFileName] ??
                                ''
                            "
                            :language="activeFileLanguage"
                            :diff-mode="diffMode"
                        />
                    </div>

                    <!-- Test & Output Console -->
                    <div
                        data-testid="ide-output-console"
                        class="flex max-h-56 shrink-0 flex-col border-t border-game-border bg-game-surface"
                    >
                        <div
                            class="flex flex-wrap items-center justify-between gap-2 border-b border-game-border bg-game-panel px-3 py-2 font-mono text-xs"
                        >
                            <span class="font-bold text-slate-300">
                                Test &amp; Output Console
                            </span>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    data-testid="run-public-tests-button"
                                    :disabled="isExecuting"
                                    @click="executeCode('public')"
                                    class="inline-flex cursor-pointer items-center gap-1.5 rounded border border-game-border bg-game-surface px-3 py-1 font-bold text-slate-200 transition hover:border-indigo-400 disabled:opacity-50"
                                >
                                    <Play class="size-3.5 text-game-emerald" />
                                    {{
                                        isExecuting
                                            ? 'Running...'
                                            : 'Run Public Tests'
                                    }}
                                </button>

                                <button
                                    type="button"
                                    data-testid="submit-solution-button"
                                    :disabled="isExecuting"
                                    @click="executeCode('submit')"
                                    class="inline-flex cursor-pointer items-center gap-1.5 rounded border-2 border-indigo-400 bg-indigo-600 px-3 py-1 font-bold text-white transition hover:bg-indigo-500 disabled:opacity-50"
                                >
                                    <Send class="size-3.5" />
                                    {{
                                        isExecuting
                                            ? 'Grading...'
                                            : 'Submit Solution'
                                    }}
                                </button>
                            </div>
                        </div>

                        <div class="overflow-y-auto p-3 font-mono text-xs">
                            <p v-if="!executionResult" class="text-slate-400">
                                Run public tests to verify sample cases, or
                                submit your solution for full public + hidden
                                server-side grading.
                            </p>

                            <div v-else class="space-y-2.5">
                                <!-- Reward Banner -->
                                <div
                                    v-if="
                                        executionResult.status === 'passed' &&
                                        executionResult.mode === 'submit'
                                    "
                                    data-testid="submission-passed-banner"
                                    class="flex flex-wrap items-center justify-between gap-2 rounded-md border border-emerald-400/50 bg-emerald-950/60 px-3 py-2 text-emerald-200"
                                >
                                    <span
                                        class="inline-flex items-center gap-1.5 font-bold"
                                    >
                                        <Trophy class="size-4 text-amber-300" />
                                        Challenge Solved! ({{
                                            executionResult.passedTests
                                        }}/{{ executionResult.totalTests }}
                                        passed)
                                    </span>
                                    <span
                                        v-if="
                                            executionResult.rewards &&
                                            executionResult.rewards.xpAwarded >
                                                0
                                        "
                                        class="font-bold text-amber-300"
                                    >
                                        +{{ executionResult.rewards.xpAwarded }}
                                        XP Awarded
                                    </span>
                                </div>

                                <!-- Error / Timeout Banner -->
                                <div
                                    v-if="executionResult.errorMessage"
                                    class="flex items-start gap-2 rounded-md border border-rose-500/40 bg-rose-950/50 px-3 py-2 text-rose-200"
                                >
                                    <AlertTriangle
                                        class="mt-0.5 size-4 shrink-0"
                                    />
                                    <span>{{
                                        executionResult.errorMessage
                                    }}</span>
                                </div>

                                <!-- Case List -->
                                <div
                                    v-for="(tc, idx) in executionResult.cases"
                                    :key="idx"
                                    class="rounded border border-game-border bg-game-panel/70 px-3 py-2"
                                >
                                    <div
                                        class="flex items-center justify-between gap-2"
                                    >
                                        <span
                                            class="inline-flex items-center gap-1.5 font-semibold"
                                            :class="
                                                tc.passed
                                                    ? 'text-game-emerald'
                                                    : 'text-rose-300'
                                            "
                                        >
                                            <CheckCircle2
                                                v-if="tc.passed"
                                                class="size-3.5"
                                            />
                                            <XCircle v-else class="size-3.5" />
                                            {{ tc.name }}
                                        </span>
                                        <span
                                            class="rounded bg-slate-800 px-1.5 py-0.5 text-[10px] text-slate-400 uppercase"
                                        >
                                            {{ tc.scope }}
                                        </span>
                                    </div>
                                    <div
                                        v-if="
                                            !tc.passed &&
                                            (tc.expected ||
                                                tc.actual ||
                                                tc.error)
                                        "
                                        class="mt-1.5 space-y-0.5 text-[11px] text-slate-300"
                                    >
                                        <p v-if="tc.expected !== null">
                                            Expected:
                                            <code class="text-emerald-300">{{
                                                tc.expected
                                            }}</code>
                                        </p>
                                        <p v-if="tc.actual !== null">
                                            Actual:
                                            <code class="text-rose-300">{{
                                                tc.actual
                                            }}</code>
                                        </p>
                                        <p
                                            v-if="tc.error"
                                            class="text-rose-300"
                                        >
                                            Error: {{ tc.error }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
