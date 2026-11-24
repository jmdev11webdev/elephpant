<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Award,
    Code2,
    Crown,
    Gamepad2,
    Lock,
    MapPin,
    ShieldCheck,
    Sparkles,
    Terminal,
    Wrench,
} from '@lucide/vue';
import { computed } from 'vue';
import { dashboard, play } from '@/routes';
import { equip as equipTitleRoute } from '@/routes/titles';

export interface PlayerSummary {
    playerName: string;
    equippedTitle: string;
    level: number;
    totalXp: number;
    nextLevelXp: number;
    completedChallenges: number;
    totalChallenges: number;
    currentRegion: string;
}

export interface DisciplinePathProgress {
    id: string;
    name: string;
    district: string;
    direction: string;
    rank: string;
    xp: number;
    nextRankXp: number;
    completed: number;
    total: number;
    accent: 'emerald' | 'indigo' | 'amber';
    focus: string;
}

export interface SkillProficiency {
    id: string;
    name: string;
    proficiency: number;
    levelLabel: string;
    pathHint: string;
}

export interface RankBadge {
    id: string;
    name: string;
    district: string;
    requirement: string;
    unlocked: boolean;
}

export interface DeveloperTitle {
    id: string;
    name: string;
    unlockCondition: string;
    unlocked: boolean;
    equipped: boolean;
}

export interface FoundationsProgress {
    completed: number;
    total: number;
    xp: number;
    badgeUnlocked: boolean;
}

export interface PlayerProgressPayload {
    summary: PlayerSummary;
    foundations: FoundationsProgress;
    paths: DisciplinePathProgress[];
    skills: SkillProficiency[];
    badges: RankBadge[];
    titles: DeveloperTitle[];
}

const props = defineProps<{
    progress: PlayerProgressPayload;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const overallXpPercent = computed(() => {
    const { totalXp, nextLevelXp } = props.progress.summary;

    if (nextLevelXp <= 0) {
        return 0;
    }

    return Math.min(100, Math.round((totalXp / nextLevelXp) * 100));
});

const unlockedBadgeCount = computed(
    () => props.progress.badges.filter((badge) => badge.unlocked).length,
);

const unlockedTitleCount = computed(
    () => props.progress.titles.filter((title) => title.unlocked).length,
);

function equipTitle(titleId: string): void {
    router.post(
        equipTitleRoute(titleId).url,
        {},
        {
            preserveScroll: true,
        },
    );
}

function pathPercent(path: DisciplinePathProgress): number {
    if (path.nextRankXp <= 0) {
        return 0;
    }

    return Math.min(100, Math.round((path.xp / path.nextRankXp) * 100));
}

function pathAccentClasses(accent: DisciplinePathProgress['accent']): {
    iconWrap: string;
    direction: string;
    bar: string;
    borderHover: string;
} {
    switch (accent) {
        case 'emerald':
            return {
                iconWrap:
                    'border-emerald-500/40 bg-emerald-950/70 text-emerald-300',
                direction: 'text-emerald-600 dark:text-emerald-400',
                bar: 'bg-emerald-500',
                borderHover: 'hover:border-emerald-500/60',
            };
        case 'amber':
            return {
                iconWrap: 'border-amber-500/40 bg-amber-950/70 text-amber-300',
                direction: 'text-amber-600 dark:text-amber-300',
                bar: 'bg-amber-400',
                borderHover: 'hover:border-amber-500/60',
            };
        default:
            return {
                iconWrap:
                    'border-indigo-500/40 bg-indigo-950/70 text-indigo-300',
                direction: 'text-indigo-600 dark:text-indigo-400',
                bar: 'bg-indigo-500',
                borderHover: 'hover:border-indigo-500/60',
            };
    }
}
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 sm:p-6">
        <Head title="Dashboard" />

        <!-- 1. Player Summary & World Launch Bar -->
        <section
            data-testid="player-summary-bar"
            class="relative overflow-hidden rounded-xl retro-bezel p-5 text-slate-100 sm:p-6"
        >
            <div
                class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(99,102,241,0.24),transparent_48%),radial-gradient(circle_at_bottom_left,rgba(52,211,153,0.12),transparent_42%)]"
            />

            <div
                class="relative flex flex-col justify-between gap-5 lg:flex-row lg:items-center"
            >
                <div class="space-y-3">
                    <div
                        class="flex flex-wrap items-center gap-2 font-mono text-xs"
                    >
                        <span
                            data-testid="equipped-title"
                            class="inline-flex items-center gap-1.5 rounded-full border border-amber-400/50 bg-amber-950/70 px-3 py-0.5 font-bold text-amber-200"
                        >
                            <Crown class="size-3.5 text-amber-300" />
                            {{ progress.summary.equippedTitle }}
                        </span>
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border border-indigo-400/40 bg-indigo-950/70 px-3 py-0.5 text-indigo-200"
                        >
                            <MapPin class="size-3.5 text-game-emerald" />
                            {{ progress.summary.currentRegion }}
                        </span>
                    </div>

                    <div>
                        <h1
                            class="font-mono text-2xl font-bold tracking-tight text-white sm:text-3xl"
                        >
                            Welcome back, {{ progress.summary.playerName }}
                        </h1>
                        <p class="mt-1 text-sm text-slate-300">
                            Track your Stack Region progression across all three
                            engineering disciplines, or jump straight into
                            Localhost Town.
                        </p>
                    </div>

                    <div
                        class="flex flex-wrap items-center gap-4 pt-1 font-mono text-xs"
                    >
                        <div class="flex items-center gap-2">
                            <span class="text-slate-400">LEVEL</span>
                            <span class="font-bold text-white">
                                Lv. {{ progress.summary.level }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-slate-400">TOTAL XP</span>
                            <span
                                data-testid="total-xp"
                                class="font-bold text-game-emerald"
                            >
                                {{ progress.summary.totalXp }} XP
                            </span>
                            <span class="text-slate-500">
                                / {{ progress.summary.nextLevelXp }} XP
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-slate-400">CHALLENGES</span>
                            <span class="font-bold text-indigo-200">
                                {{ progress.summary.completedChallenges }} /
                                {{ progress.summary.totalChallenges }}
                            </span>
                        </div>
                    </div>

                    <div class="max-w-md">
                        <div
                            class="h-2 w-full overflow-hidden rounded-full bg-slate-800"
                        >
                            <div
                                class="h-full rounded-full bg-indigo-500 transition-all"
                                :style="{ width: `${overallXpPercent}%` }"
                            />
                        </div>
                    </div>
                </div>

                <div
                    class="flex shrink-0 flex-col items-start gap-2 sm:items-end"
                >
                    <Link
                        :href="play()"
                        data-testid="enter-stack-region-cta"
                        class="inline-flex items-center gap-2 rounded-md border-2 border-indigo-400 bg-indigo-600 px-5 py-3 font-mono text-xs font-bold text-white shadow-lg shadow-indigo-950/60 transition hover:bg-indigo-500"
                    >
                        <Gamepad2 class="size-4" />
                        Continue Adventure
                    </Link>
                    <span class="font-mono text-[11px] text-slate-400">
                        Resumes at your latest saved zone (/play)
                    </span>
                </div>
            </div>
        </section>

        <!-- 2. Roads / Path Progress -->
        <section data-testid="roads-progress-section" class="space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-mono text-lg font-bold text-foreground">
                        Roads &amp; Path Progress
                    </h2>
                    <p class="text-xs text-muted-foreground">
                        Non-exclusive Stack Region disciplines—explore and rank
                        up in all three paths freely.
                    </p>
                </div>
            </div>

            <!-- Optional Localhost Academy (Zero-Knowledge Foundations) Track -->
            <div
                data-testid="foundations-progress-banner"
                class="flex flex-col justify-between gap-3 rounded-xl border-2 border-cyan-500/35 bg-card p-4 shadow-xs sm:flex-row sm:items-center"
            >
                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2 font-mono">
                        <span
                            class="rounded border border-cyan-400/40 bg-cyan-950/70 px-2 py-0.5 text-[10px] font-bold text-cyan-200 uppercase"
                        >
                            Optional Onboarding • Localhost Academy
                        </span>
                        <span
                            v-if="progress.foundations.badgeUnlocked"
                            class="rounded bg-emerald-500/20 px-2 py-0.5 text-[10px] font-bold text-emerald-600 uppercase dark:text-emerald-300"
                        >
                            Syntax Awakened Unlocked
                        </span>
                    </div>
                    <h3 class="font-mono text-sm font-bold text-foreground">
                        Foundations (Zero-Knowledge PHP Track)
                    </h3>
                    <p class="text-xs text-muted-foreground">
                        New to PHP? Complete 8 guided micro-challenges with
                        30-second lessons and progressive hints—or skip straight
                        to the three discipline roads below.
                    </p>
                </div>

                <div
                    class="flex shrink-0 flex-col items-start gap-1.5 font-mono text-xs sm:items-end"
                >
                    <span class="font-bold text-foreground">
                        {{ progress.foundations.completed }} /
                        {{ progress.foundations.total }} steps
                    </span>
                    <span class="text-[11px] text-muted-foreground">
                        {{ progress.foundations.xp }} Academy XP
                    </span>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <article
                    v-for="path in progress.paths"
                    :key="path.id"
                    data-testid="path-progress-card"
                    class="flex flex-col justify-between rounded-xl border-2 border-border bg-card p-5 shadow-xs transition"
                    :class="pathAccentClasses(path.accent).borderHover"
                >
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div
                                class="flex size-10 items-center justify-center rounded-lg border"
                                :class="pathAccentClasses(path.accent).iconWrap"
                            >
                                <Code2
                                    v-if="path.id === 'programmer'"
                                    class="size-5"
                                />
                                <Terminal
                                    v-else-if="path.id === 'developer'"
                                    class="size-5"
                                />
                                <Wrench v-else class="size-5" />
                            </div>
                            <span
                                class="rounded border border-border bg-muted px-2 py-0.5 font-mono text-[11px] font-bold text-foreground"
                            >
                                {{ path.rank }}
                            </span>
                        </div>

                        <div>
                            <p
                                class="font-mono text-[11px] font-bold tracking-wider uppercase"
                                :class="
                                    pathAccentClasses(path.accent).direction
                                "
                            >
                                {{ path.direction }} • {{ path.district }}
                            </p>
                            <h3
                                class="mt-0.5 font-mono text-base font-bold text-foreground"
                            >
                                {{ path.name }}
                            </h3>
                            <p
                                class="mt-1.5 text-xs leading-relaxed text-muted-foreground"
                            >
                                {{ path.focus }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 space-y-2 border-t border-border pt-3">
                        <div
                            class="flex items-center justify-between font-mono text-[11px]"
                        >
                            <span class="text-muted-foreground">
                                {{ path.xp }} / {{ path.nextRankXp }} XP
                            </span>
                            <span class="font-semibold text-foreground">
                                {{ path.completed }} / {{ path.total }}
                                challenges
                            </span>
                        </div>
                        <div
                            class="h-2 w-full overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                class="h-full rounded-full transition-all"
                                :class="pathAccentClasses(path.accent).bar"
                                :style="{ width: `${pathPercent(path)}%` }"
                            />
                        </div>
                    </div>
                </article>
            </div>
        </section>

        <!-- 3 & 4. Skills & Proficiency + Rank Badges & Titles Collection -->
        <div class="grid gap-6 lg:grid-cols-12">
            <!-- 3. Skills & Proficiency -->
            <section
                data-testid="skills-proficiency-section"
                class="rounded-xl border-2 border-border bg-card p-5 shadow-xs lg:col-span-5"
            >
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2
                            class="font-mono text-base font-bold text-foreground"
                        >
                            Skills &amp; Proficiency
                        </h2>
                        <p class="text-xs text-muted-foreground">
                            Core software engineering competency meters
                        </p>
                    </div>
                    <Sparkles class="size-4 text-primary" />
                </div>

                <div class="space-y-4">
                    <div
                        v-for="skill in progress.skills"
                        :key="skill.id"
                        data-testid="skill-meter"
                        class="space-y-1.5"
                    >
                        <div
                            class="flex items-center justify-between gap-2 font-mono text-xs"
                        >
                            <span class="font-semibold text-foreground">
                                {{ skill.name }}
                            </span>
                            <span class="text-[11px] text-muted-foreground">
                                {{ skill.proficiency }}% •
                                {{ skill.levelLabel }}
                            </span>
                        </div>
                        <div
                            class="h-2 w-full overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                class="h-full rounded-full bg-primary transition-all"
                                :style="{ width: `${skill.proficiency}%` }"
                            />
                        </div>
                        <p class="font-mono text-[10px] text-muted-foreground">
                            District focus: {{ skill.pathHint }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- 4. Rank Badges & Titles Collection -->
            <section
                data-testid="badges-titles-section"
                class="flex flex-col gap-5 rounded-xl border-2 border-border bg-card p-5 shadow-xs lg:col-span-7"
            >
                <!-- Rank Badges -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2
                                class="font-mono text-base font-bold text-foreground"
                            >
                                Rank Badges
                            </h2>
                            <p class="text-xs text-muted-foreground">
                                District crests awarded for completing challenge
                                milestones
                            </p>
                        </div>
                        <span
                            class="rounded border border-border bg-muted px-2 py-0.5 font-mono text-[11px] font-semibold text-muted-foreground"
                        >
                            {{ unlockedBadgeCount }} /
                            {{ progress.badges.length }} unlocked
                        </span>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div
                            v-for="badge in progress.badges"
                            :key="badge.id"
                            data-testid="badge-slot"
                            class="flex items-start gap-3 rounded-lg border p-3 transition"
                            :class="
                                badge.unlocked
                                    ? 'border-indigo-400/50 bg-accent/50'
                                    : 'border-dashed border-border bg-muted/30 opacity-75'
                            "
                        >
                            <div
                                class="flex size-9 shrink-0 items-center justify-center rounded-md border"
                                :class="
                                    badge.unlocked
                                        ? 'border-indigo-400 bg-indigo-950 text-amber-300'
                                        : 'border-border bg-muted text-muted-foreground'
                                "
                            >
                                <Award v-if="badge.unlocked" class="size-4" />
                                <Lock v-else class="size-4" />
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <p
                                        class="truncate font-mono text-xs font-bold text-foreground"
                                    >
                                        {{ badge.name }}
                                    </p>
                                    <span
                                        v-if="badge.unlocked"
                                        class="rounded bg-emerald-500/20 px-1.5 py-0.5 font-mono text-[9px] font-bold text-emerald-600 uppercase dark:text-emerald-300"
                                    >
                                        Unlocked
                                    </span>
                                </div>
                                <p
                                    class="font-mono text-[10px] text-muted-foreground"
                                >
                                    {{ badge.district }}
                                </p>
                                <p
                                    class="mt-1 text-[11px] leading-snug text-muted-foreground"
                                >
                                    {{ badge.requirement }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Developer Titles -->
                <div class="space-y-3 border-t border-border pt-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2
                                class="font-mono text-base font-bold text-foreground"
                            >
                                Developer Titles
                            </h2>
                            <p class="text-xs text-muted-foreground">
                                Earnable titles displayed on your player profile
                            </p>
                        </div>
                        <span
                            class="rounded border border-border bg-muted px-2 py-0.5 font-mono text-[11px] font-semibold text-muted-foreground"
                        >
                            {{ unlockedTitleCount }} /
                            {{ progress.titles.length }} earned
                        </span>
                    </div>

                    <div class="grid gap-2 sm:grid-cols-2">
                        <div
                            v-for="title in progress.titles"
                            :key="title.id"
                            data-testid="title-slot"
                            class="flex items-start justify-between gap-2 rounded-lg border px-3 py-2.5"
                            :class="
                                title.unlocked
                                    ? 'border-amber-400/50 bg-amber-950/15 dark:bg-amber-950/35'
                                    : 'border-dashed border-border bg-muted/25 opacity-75'
                            "
                        >
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <ShieldCheck
                                        v-if="title.unlocked"
                                        class="size-3.5 shrink-0 text-amber-500 dark:text-amber-300"
                                    />
                                    <Lock
                                        v-else
                                        class="size-3.5 shrink-0 text-muted-foreground"
                                    />
                                    <span
                                        class="truncate font-mono text-xs font-bold text-foreground"
                                    >
                                        {{ title.name }}
                                    </span>
                                </div>
                                <p
                                    class="mt-0.5 text-[11px] leading-snug text-muted-foreground"
                                >
                                    {{ title.unlockCondition }}
                                </p>
                            </div>

                            <span
                                v-if="title.equipped"
                                class="shrink-0 rounded border border-amber-400/50 bg-amber-500/20 px-1.5 py-0.5 font-mono text-[9px] font-bold text-amber-700 uppercase dark:text-amber-200"
                            >
                                Equipped
                            </span>
                            <button
                                v-else-if="title.unlocked"
                                type="button"
                                :data-testid="`equip-title-${title.id}`"
                                @click="equipTitle(title.id)"
                                class="shrink-0 cursor-pointer rounded border border-indigo-400/50 bg-primary px-2 py-0.5 font-mono text-[9px] font-bold text-primary-foreground uppercase transition hover:opacity-90"
                            >
                                Equip
                            </button>
                            <span
                                v-else
                                class="shrink-0 font-mono text-[10px] text-muted-foreground"
                            >
                                Locked
                            </span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
