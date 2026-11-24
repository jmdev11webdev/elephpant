<script setup lang="ts">
import { SlidersHorizontal, Volume2, VolumeX } from '@lucide/vue';
import { onMounted, onUnmounted, ref } from 'vue';
import { audioManager } from '@/game/audio/AudioManager';
import type { AudioPrefs } from '@/game/audio/AudioManager';

const prefs = ref<AudioPrefs>(audioManager.getPrefs());
const showPopover = ref(false);
const containerRef = ref<HTMLDivElement | null>(null);

let unsubscribe: (() => void) | null = null;

function handleOutsideClick(event: MouseEvent): void {
    if (
        showPopover.value &&
        containerRef.value &&
        !containerRef.value.contains(event.target as Node)
    ) {
        showPopover.value = false;
    }
}

function handleBgmInput(event: Event): void {
    const val = Number((event.target as HTMLInputElement).value);
    audioManager.setBgmVolume(val);
}

function handleSfxInput(event: Event): void {
    const val = Number((event.target as HTMLInputElement).value);
    audioManager.setSfxVolume(val);
}

onMounted(() => {
    unsubscribe = audioManager.subscribe((next) => {
        prefs.value = next;
    });
    window.addEventListener('pointerdown', handleOutsideClick);
});

onUnmounted(() => {
    unsubscribe?.();
    window.removeEventListener('pointerdown', handleOutsideClick);
});
</script>

<template>
    <div ref="containerRef" class="relative inline-flex items-center gap-1">
        <button
            type="button"
            data-testid="audio-mute-toggle"
            :aria-label="prefs.muted ? 'Unmute audio' : 'Mute audio'"
            @click="audioManager.toggleMute()"
            class="inline-flex cursor-pointer items-center gap-1 rounded border border-border bg-card px-2 py-1 font-mono text-[11px] font-semibold text-foreground transition hover:border-indigo-400"
        >
            <VolumeX v-if="prefs.muted" class="size-3.5 text-rose-400" />
            <Volume2 v-else class="size-3.5 text-game-emerald" />
            <span class="hidden sm:inline">
                {{ prefs.muted ? 'Muted' : 'Audio' }}
            </span>
        </button>

        <button
            type="button"
            data-testid="audio-mixer-toggle"
            aria-label="Audio volume settings"
            @click="showPopover = !showPopover"
            class="inline-flex cursor-pointer items-center rounded border border-border bg-card p-1 text-muted-foreground transition hover:border-indigo-400 hover:text-foreground"
        >
            <SlidersHorizontal class="size-3.5" />
        </button>

        <Transition name="game-modal">
            <div
                v-if="showPopover"
                data-testid="audio-mixer-popover"
                class="absolute top-full right-0 z-40 mt-2 w-56 rounded-xl border-2 border-game-border bg-game-panel p-3.5 font-mono text-xs text-slate-100 shadow-xl"
            >
                <div class="mb-2.5 flex items-center justify-between">
                    <span class="font-bold text-white">16-Bit Audio</span>
                    <button
                        type="button"
                        @click="audioManager.toggleMute()"
                        class="cursor-pointer rounded border border-game-border bg-game-surface px-2 py-0.5 text-[10px] text-slate-300 hover:border-indigo-400"
                    >
                        {{ prefs.muted ? 'Unmute' : 'Mute All' }}
                    </button>
                </div>

                <div class="space-y-3">
                    <label class="block space-y-1">
                        <div class="flex justify-between text-[11px]">
                            <span class="text-slate-300">Music (BGM)</span>
                            <span class="text-game-cyan">
                                {{ Math.round(prefs.bgmVolume * 100) }}%
                            </span>
                        </div>
                        <input
                            type="range"
                            min="0"
                            max="1"
                            step="0.05"
                            :value="prefs.bgmVolume"
                            @input="handleBgmInput"
                            class="w-full accent-indigo-500"
                        />
                    </label>

                    <label class="block space-y-1">
                        <div class="flex justify-between text-[11px]">
                            <span class="text-slate-300">Sound FX</span>
                            <span class="text-game-emerald">
                                {{ Math.round(prefs.sfxVolume * 100) }}%
                            </span>
                        </div>
                        <input
                            type="range"
                            min="0"
                            max="1"
                            step="0.05"
                            :value="prefs.sfxVolume"
                            @input="handleSfxInput"
                            class="w-full accent-emerald-400"
                        />
                    </label>
                </div>
            </div>
        </Transition>
    </div>
</template>
