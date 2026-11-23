<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Compass, Gamepad2, Terminal } from '@lucide/vue';
import PhaserGame from '@/components/game/PhaserGame.vue';
import { dashboard } from '@/routes';

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
</script>

<template>
    <Head title="Dashboard" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div
                class="rounded-xl border-2 border-sidebar-border/80 bg-card p-4 shadow-xs transition hover:border-indigo-400/60"
            >
                <div
                    class="flex items-center gap-2 font-mono text-[11px] font-bold tracking-wider text-emerald-600 uppercase dark:text-emerald-400"
                >
                    <Compass class="size-3.5" />
                    Current Region
                </div>
                <p class="mt-1.5 font-mono text-base font-bold text-foreground">
                    Localhost Town
                </p>
                <p class="mt-1 text-xs text-muted-foreground">
                    Central safe hub of the Stack Region • 16×16 GBA tilemap
                </p>
            </div>

            <div
                class="rounded-xl border-2 border-sidebar-border/80 bg-card p-4 shadow-xs transition hover:border-indigo-400/60"
            >
                <div
                    class="flex items-center gap-2 font-mono text-[11px] font-bold tracking-wider text-indigo-600 uppercase dark:text-indigo-400"
                >
                    <Gamepad2 class="size-3.5" />
                    Controls
                </div>
                <p class="mt-1.5 font-mono text-base font-bold text-foreground">
                    WASD / Arrows + E
                </p>
                <p class="mt-1 text-xs text-muted-foreground">
                    Press Space, Enter, or E to talk to NPCs and read signs
                </p>
            </div>

            <div
                class="rounded-xl border-2 border-sidebar-border/80 bg-card p-4 shadow-xs transition hover:border-indigo-400/60"
            >
                <div
                    class="flex items-center gap-2 font-mono text-[11px] font-bold tracking-wider text-amber-600 uppercase dark:text-amber-300"
                >
                    <Terminal class="size-3.5" />
                    Landmark Ahead
                </div>
                <p class="mt-1.5 font-mono text-base font-bold text-foreground">
                    Challenge Center
                </p>
                <p class="mt-1 text-xs text-muted-foreground">
                    Step through the northeast indigo doorway to inspect the
                    terminal
                </p>
            </div>
        </div>

        <div class="flex flex-1 items-center justify-center">
            <PhaserGame />
        </div>
    </div>
</template>
