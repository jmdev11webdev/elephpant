<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative flex min-h-svh flex-col items-center justify-center gap-6 overflow-hidden bg-game-surface p-6 text-slate-100 md:p-10"
    >
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(99,102,241,0.20),transparent_48%),radial-gradient(circle_at_bottom_right,rgba(34,197,94,0.10),transparent_40%)]"
        />

        <div class="relative z-10 w-full max-w-md">
            <div
                class="flex flex-col gap-7 rounded-xl retro-bezel bg-game-panel/95 p-6 sm:p-8"
            >
                <div class="flex flex-col items-center gap-4">
                    <Link
                        :href="home()"
                        class="flex flex-col items-center gap-2 font-mono font-bold text-white transition hover:text-indigo-300"
                    >
                        <div class="flex size-14 items-center justify-center">
                            <AppLogoIcon class="h-16 w-auto" />
                        </div>
                        <span class="text-sm tracking-wide">
                            ele<span class="text-indigo-400">PHP</span>ant
                        </span>
                    </Link>

                    <div class="space-y-1.5 text-center">
                        <h1 class="font-mono text-xl font-bold text-white">
                            {{ title }}
                        </h1>
                        <p class="text-center text-sm text-slate-400">
                            {{ description }}
                        </p>
                    </div>
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>
