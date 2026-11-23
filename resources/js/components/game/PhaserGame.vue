<script setup lang="ts">
import Phaser from 'phaser';
import { onMounted, onUnmounted, ref } from 'vue';
import DialogueBox from '@/components/game/DialogueBox.vue';
import { createGameConfig } from '@/game/config';
import { gameEvents } from '@/game/events';
import type { ShowDialoguePayload } from '@/game/events';

const containerRef = ref<HTMLDivElement | null>(null);
const activeDialogue = ref<ShowDialoguePayload | null>(null);

let gameInstance: Phaser.Game | null = null;
let unsubscribeDialogue: (() => void) | null = null;

function initializeGame(): void {
    if (gameInstance !== null || containerRef.value === null) {
        return;
    }

    unsubscribeDialogue = gameEvents.on('show-dialogue', (payload) => {
        activeDialogue.value = payload;
    });

    gameInstance = new Phaser.Game(createGameConfig(containerRef.value));
}

function handleDialogueClose(): void {
    activeDialogue.value = null;
    gameEvents.emit('resume-overworld');
}

function destroyGame(): void {
    if (unsubscribeDialogue !== null) {
        unsubscribeDialogue();
        unsubscribeDialogue = null;
    }

    activeDialogue.value = null;

    if (gameInstance === null) {
        return;
    }

    gameInstance.destroy(true);
    gameInstance = null;
}

onMounted(() => {
    initializeGame();
});

onUnmounted(() => {
    destroyGame();
});

if (import.meta.hot) {
    import.meta.hot.dispose(() => {
        destroyGame();
    });
}
</script>

<template>
    <div
        class="flex w-full flex-col items-center justify-center overflow-hidden rounded-xl retro-bezel p-2 sm:p-4"
    >
        <div
            class="mb-2 flex w-full max-w-[960px] items-center justify-between border-b border-game-border px-1 pb-1.5 font-mono text-[11px] text-slate-400"
        >
            <span class="flex items-center gap-2 text-indigo-300">
                <span
                    class="inline-block size-2 rounded-full bg-game-emerald"
                />
                elePHPant • PHASER 3.90.0
            </span>
            <span class="hidden text-slate-500 sm:inline">
                960×540 • 3× GBA ZOOM
            </span>
        </div>

        <div
            class="relative aspect-video w-full max-w-[960px] overflow-hidden rounded-lg bg-game-panel"
        >
            <div
                ref="containerRef"
                data-testid="phaser-game-container"
                class="h-full w-full [image-rendering:pixelated] [&_canvas]:block [&_canvas]:max-h-full [&_canvas]:max-w-full [&_canvas]:[image-rendering:pixelated]"
            />

            <DialogueBox
                v-if="activeDialogue"
                :dialogue="activeDialogue"
                @close="handleDialogueClose"
            />
        </div>
    </div>
</template>
