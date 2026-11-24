<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ExternalLink, Library, Search, Sparkles, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import BrandIcon from '@/components/BrandIcon.vue';
import { index as resourcesIndex } from '@/routes/resources';

export interface CuratedResource {
    title: string;
    url: string;
    category: string;
    description: string;
}

const props = defineProps<{
    categories: string[];
    resources: CuratedResource[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Resources',
                href: resourcesIndex(),
            },
        ],
    },
});

const ALL_CATEGORY = 'All';

const selectedCategory = ref<string>(ALL_CATEGORY);
const searchQuery = ref<string>('');

const filterOptions = computed(() => [ALL_CATEGORY, ...props.categories]);

const categoryCounts = computed<Record<string, number>>(() => {
    const counts: Record<string, number> = {
        [ALL_CATEGORY]: props.resources.length,
    };

    for (const category of props.categories) {
        counts[category] = 0;
    }

    for (const item of props.resources) {
        counts[item.category] = (counts[item.category] ?? 0) + 1;
    }

    return counts;
});

const filteredResources = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();

    return props.resources.filter((item) => {
        const matchesCategory =
            selectedCategory.value === ALL_CATEGORY ||
            item.category === selectedCategory.value;

        if (!matchesCategory) {
            return false;
        }

        if (query === '') {
            return true;
        }

        return (
            item.title.toLowerCase().includes(query) ||
            item.description.toLowerCase().includes(query) ||
            item.category.toLowerCase().includes(query)
        );
    });
});

function formatHostname(url: string): string {
    try {
        return new URL(url).hostname.replace(/^www\./, '');
    } catch {
        return url;
    }
}

function resetFilters(): void {
    selectedCategory.value = ALL_CATEGORY;
    searchQuery.value = '';
}
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 sm:p-6">
        <Head title="Resources" />

        <!-- Retro Header Banner -->
        <section
            class="relative overflow-hidden rounded-xl retro-bezel p-5 text-slate-100 sm:p-6"
        >
            <div
                class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(99,102,241,0.22),transparent_48%),radial-gradient(circle_at_bottom_left,rgba(52,211,153,0.12),transparent_42%)]"
            />

            <div
                class="relative flex flex-col justify-between gap-4 lg:flex-row lg:items-end"
            >
                <div class="max-w-2xl space-y-2">
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-indigo-400/40 bg-indigo-950/80 px-3 py-1 font-mono text-[11px] font-semibold text-indigo-200"
                    >
                        <Library class="size-3.5 text-game-emerald" />
                        OUT-OF-GAME DIRECTORY • CURATED ECOSYSTEM
                    </div>
                    <h1
                        class="font-mono text-2xl font-bold tracking-tight text-white sm:text-3xl"
                    >
                        PHP &amp; Laravel Resource Directory
                    </h1>
                    <p class="text-sm leading-relaxed text-slate-300">
                        Explore official documentation, courses, package
                        registries, and frontend stack references outside the
                        overworld canvas. Every link opens safely in a new tab.
                    </p>
                </div>

                <!-- Keyword Search -->
                <div class="w-full lg:max-w-xs">
                    <label for="resource-search" class="sr-only">
                        Search resources
                    </label>
                    <div class="relative">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                        />
                        <input
                            id="resource-search"
                            v-model="searchQuery"
                            type="search"
                            data-testid="resource-search"
                            placeholder="Search titles or topics..."
                            class="w-full rounded-md border border-game-border bg-game-panel py-2 pr-9 pl-9 font-mono text-xs text-white placeholder:text-slate-400 focus:border-game-accent focus:outline-none"
                        />
                        <button
                            v-if="searchQuery"
                            type="button"
                            aria-label="Clear search"
                            @click="searchQuery = ''"
                            class="absolute top-1/2 right-2.5 -translate-y-1/2 cursor-pointer text-slate-400 transition hover:text-white"
                        >
                            <X class="size-3.5" />
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Category Filter Bar -->
        <div
            role="tablist"
            aria-label="Resource categories"
            class="flex flex-wrap items-center gap-2"
        >
            <button
                v-for="category in filterOptions"
                :key="category"
                type="button"
                role="tab"
                :aria-selected="selectedCategory === category"
                :data-testid="`category-filter-${category}`"
                @click="selectedCategory = category"
                class="inline-flex cursor-pointer items-center gap-1.5 rounded-md border px-3 py-1.5 font-mono text-xs transition"
                :class="
                    selectedCategory === category
                        ? 'border-indigo-400 bg-primary font-bold text-primary-foreground shadow-xs'
                        : 'border-border bg-card text-muted-foreground hover:border-indigo-400/60 hover:text-foreground'
                "
            >
                <span>{{ category }}</span>
                <span
                    class="rounded px-1.5 py-0.5 text-[10px]"
                    :class="
                        selectedCategory === category
                            ? 'bg-black/20 text-primary-foreground'
                            : 'bg-muted text-muted-foreground'
                    "
                >
                    {{ categoryCounts[category] ?? 0 }}
                </span>
            </button>
        </div>

        <!-- Results Grid -->
        <div
            v-if="filteredResources.length > 0"
            data-testid="resources-grid"
            class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
        >
            <a
                v-for="item in filteredResources"
                :key="item.url"
                :href="item.url"
                target="_blank"
                rel="noopener noreferrer"
                data-testid="resource-card"
                class="group flex flex-col justify-between rounded-xl border-2 border-border bg-card p-5 shadow-xs transition hover:-translate-y-0.5 hover:border-indigo-400/70 hover:shadow-md"
            >
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <BrandIcon :url="item.url" />
                            <div class="min-w-0">
                                <h2
                                    class="truncate font-mono text-base font-bold text-foreground transition group-hover:text-primary"
                                >
                                    {{ item.title }}
                                </h2>
                                <span
                                    class="mt-0.5 inline-flex items-center rounded border border-indigo-400/30 bg-accent px-2 py-0.5 font-mono text-[10px] font-semibold text-accent-foreground"
                                >
                                    {{ item.category }}
                                </span>
                            </div>
                        </div>
                        <ExternalLink
                            class="mt-1 size-4 shrink-0 text-muted-foreground transition group-hover:text-primary"
                        />
                    </div>

                    <p class="text-sm leading-relaxed text-muted-foreground">
                        {{ item.description }}
                    </p>
                </div>

                <div
                    class="mt-4 flex items-center justify-between border-t border-border pt-3 font-mono text-[11px] text-muted-foreground"
                >
                    <span class="truncate">{{ formatHostname(item.url) }}</span>
                    <span
                        class="shrink-0 font-semibold text-primary group-hover:underline"
                    >
                        Visit →
                    </span>
                </div>
            </a>
        </div>

        <!-- Empty Filter State -->
        <div
            v-else
            class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-border bg-card/60 px-6 py-12 text-center"
        >
            <Sparkles class="size-8 text-muted-foreground" />
            <p class="mt-3 font-mono text-sm font-bold text-foreground">
                No matching resources found
            </p>
            <p class="mt-1 text-xs text-muted-foreground">
                Try clearing your keyword filter or switching back to all
                categories.
            </p>
            <button
                type="button"
                @click="resetFilters"
                class="mt-4 cursor-pointer rounded-md border border-indigo-400 bg-primary px-4 py-1.5 font-mono text-xs font-bold text-primary-foreground transition hover:opacity-90"
            >
                Reset Filters
            </button>
        </div>

        <!-- Compliance & Attribution Footer Notice -->
        <footer
            data-testid="resources-attribution"
            class="mt-auto rounded-xl border border-border bg-muted/40 px-4 py-3 text-center font-mono text-xs text-muted-foreground"
        >
            <strong>elePHPant</strong> is an independent project. All listed
            third-party websites, trademarks, and educational resources belong
            to their respective owners.
        </footer>
    </div>
</template>
