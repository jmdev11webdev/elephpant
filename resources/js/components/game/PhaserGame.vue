<script setup lang="ts">
import Phaser from 'phaser';
import { onMounted, onUnmounted, ref } from 'vue';
import DialogueBox from '@/components/game/DialogueBox.vue';
import ZoneBanner from '@/components/game/ZoneBanner.vue';
import ChallengeCenterModal from '@/components/ide/ChallengeCenterModal.vue';
import { audioManager } from '@/game/audio/AudioManager';
import { createGameConfig } from '@/game/config';
import { gameEvents } from '@/game/events';
import type {
    LoadWorldStatePayload,
    PlayerLocationPayload,
    ResumeOverworldPayload,
    ShowDialoguePayload,
} from '@/game/events';
import { DEFAULT_WORLD_STATE } from '@/game/worldState';
import type { WorldStateSnapshot } from '@/game/worldState';

const props = withDefaults(
    defineProps<{
        worldState?: WorldStateSnapshot;
        bannerVisible?: boolean;
        bannerTitle?: string;
        bannerSubtitle?: string;
    }>(),
    {
        worldState: () => DEFAULT_WORLD_STATE,
        bannerVisible: false,
        bannerTitle: '',
        bannerSubtitle: '',
    },
);

const emit = defineEmits<{
    locationChanged: [payload: PlayerLocationPayload];
    challengeCompleted: [payload: ResumeOverworldPayload];
    overlayChange: [open: boolean];
}>();

const containerRef = ref<HTMLDivElement | null>(null);
const activeDialogue = ref<ShowDialoguePayload | null>(null);
const showChallengeCenter = ref(false);
const activeChallengeSlug = ref<string | null>(null);
const activeDisciplineFilter = ref<string | null>(null);

let gameInstance: Phaser.Game | null = null;
let unsubscribers: Array<() => void> = [];

function initializeGame(): void {
    if (gameInstance !== null || containerRef.value === null) {
        return;
    }

    unsubscribers = [
        gameEvents.on('show-dialogue', (payload) => {
            activeDialogue.value = payload;
            emit('overlayChange', true);
        }),
        gameEvents.on('open-challenge-center', (payload) => {
            activeDialogue.value = null;
            activeChallengeSlug.value = null;
            activeDisciplineFilter.value = payload?.discipline ?? null;
            showChallengeCenter.value = true;
            emit('overlayChange', true);
            audioManager.playSfx('terminalOpen');
            audioManager.enterChallengeMode();
        }),
        gameEvents.on('open-challenge', (payload) => {
            activeDialogue.value = null;
            activeDisciplineFilter.value = null;
            activeChallengeSlug.value = payload.challengeId;
            showChallengeCenter.value = true;
            emit('overlayChange', true);
            audioManager.playSfx('terminalOpen');
            audioManager.enterChallengeMode();
        }),
        gameEvents.on('player-location-changed', (payload) => {
            emit('locationChanged', payload);
        }),
    ];

    gameInstance = new Phaser.Game(createGameConfig(containerRef.value));
    gameInstance.registry.set('worldState', {
        ...props.worldState,
        unlockedTriggers: [...props.worldState.unlockedTriggers],
    });
}

function handleDialogueClose(): void {
    activeDialogue.value = null;
    emit('overlayChange', false);
    gameEvents.emit('resume-overworld');
}

function handleChallengeCenterClose(payload: ResumeOverworldPayload): void {
    showChallengeCenter.value = false;
    activeChallengeSlug.value = null;
    activeDisciplineFilter.value = null;
    emit('overlayChange', false);
    audioManager.exitChallengeMode();
    gameEvents.emit('resume-overworld', payload);

    if (payload.completedChallengeId) {
        emit('challengeCompleted', payload);
    }
}

function loadWorldState(payload: LoadWorldStatePayload): void {
    activeDialogue.value = null;
    showChallengeCenter.value = false;
    activeChallengeSlug.value = null;
    activeDisciplineFilter.value = null;
    emit('overlayChange', false);
    gameEvents.emit('load-world-state', payload);
}

defineExpose({
    loadWorldState,
});

function destroyGame(): void {
    for (const unsubscribe of unsubscribers) {
        unsubscribe();
    }

    unsubscribers = [];
    activeDialogue.value = null;
    showChallengeCenter.value = false;
    activeChallengeSlug.value = null;

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

            <ZoneBanner
                :visible="bannerVisible"
                :title="bannerTitle"
                :subtitle="bannerSubtitle"
            />

            <Transition name="game-modal">
                <DialogueBox
                    v-if="activeDialogue"
                    :dialogue="activeDialogue"
                    @close="handleDialogueClose"
                />
            </Transition>
        </div>

        <Transition name="game-modal">
            <ChallengeCenterModal
                v-if="showChallengeCenter"
                :initial-challenge-slug="activeChallengeSlug"
                :initial-discipline-filter="activeDisciplineFilter"
                @close="handleChallengeCenterClose"
            />
        </Transition>
    </div>
</template>
