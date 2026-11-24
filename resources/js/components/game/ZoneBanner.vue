<script setup lang="ts">
import { MapPin } from '@lucide/vue';

defineProps<{
    visible: boolean;
    title: string;
    subtitle: string;
}>();
</script>

<template>
    <Transition name="zone-banner">
        <div
            v-if="visible"
            data-testid="zone-banner"
            class="pointer-events-none absolute top-3 left-3 z-30 flex items-center gap-2.5 rounded-lg border-2 border-indigo-400/70 bg-slate-950/92 px-3.5 py-2 font-mono shadow-xl backdrop-blur-xs"
        >
            <span
                class="flex size-7 items-center justify-center rounded border border-cyan-400/50 bg-cyan-950/80 text-game-cyan"
            >
                <MapPin class="size-4" />
            </span>
            <div>
                <p class="text-xs font-bold tracking-wide text-white">
                    {{ title }}
                </p>
                <p class="text-[10px] text-indigo-300">
                    {{ subtitle }}
                </p>
            </div>
        </div>
    </Transition>
</template>
