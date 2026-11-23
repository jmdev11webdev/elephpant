<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <div
            class="hidden items-center gap-2 rounded-full border border-indigo-400/40 bg-indigo-950/10 px-3 py-1 font-mono text-[11px] font-semibold text-indigo-700 sm:inline-flex dark:bg-indigo-950/70 dark:text-indigo-200"
        >
            <span class="inline-block size-2 rounded-full bg-emerald-400" />
            STACK REGION • 16-BIT
        </div>
    </header>
</template>
