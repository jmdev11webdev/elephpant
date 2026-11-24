<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Compass, HardDrive } from '@lucide/vue';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import AudioControls from '@/components/game/AudioControls.vue';
import PhaserGame from '@/components/game/PhaserGame.vue';
import SaveLoadModal from '@/components/game/SaveLoadModal.vue';
import type { SaveSlotRecord } from '@/components/game/SaveLoadModal.vue';
import WorldMapModal from '@/components/game/WorldMapModal.vue';
import type { DistrictStatsMap } from '@/components/game/WorldMapModal.vue';
import { audioManager } from '@/game/audio/AudioManager';
import { gameEvents } from '@/game/events';
import type {
    LoadWorldStatePayload,
    PlayerLocationPayload,
    ResumeOverworldPayload,
} from '@/game/events';
import { DISTRICT_SPAWNS } from '@/game/worldState';
import type { WorldStateSnapshot } from '@/game/worldState';
import { dashboard, play } from '@/routes';
import { state as gameStateRoute } from '@/routes/game';
import {
    destroy as deleteGameSlotRoute,
    load as loadGameSlotRoute,
    save as saveGameSlotRoute,
} from '@/routes/game/saves';

interface GameStatePayload {
    resume: {
        zone: string;
        x: number;
        y: number;
        facing: 'up' | 'down' | 'left' | 'right';
        playtimeSeconds: number;
        fromSlot: number | null;
    };
    slots: SaveSlotRecord[];
    districts: DistrictStatsMap;
    solvedSlugs: string[];
    unlockedTerminals: string[];
    summary: {
        equippedTitle: string;
        level: number;
        totalXp: number;
        completedChallenges: number;
        totalChallenges: number;
    };
}

const props = defineProps<{
    worldState: WorldStateSnapshot;
    initialGameState: GameStatePayload;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Play World',
                href: play(),
            },
        ],
    },
});

const phaserRef = ref<{
    loadWorldState: (payload: LoadWorldStatePayload) => void;
} | null>(null);

const gameState = ref<GameStatePayload>(props.initialGameState);
const currentZone = ref<string>(props.initialGameState.resume.zone);
const playerX = ref<number>(props.initialGameState.resume.x);
const playerY = ref<number>(props.initialGameState.resume.y);
const mapWidth = ref<number>(48);
const mapHeight = ref<number>(36);
const playerFacing = ref<'up' | 'down' | 'left' | 'right'>(
    props.initialGameState.resume.facing,
);
const playtimeSeconds = ref<number>(
    props.initialGameState.resume.playtimeSeconds,
);

const showWorldMap = ref(false);
const showSaveLoad = ref(false);
const phaserOverlayOpen = ref(false);
const busySlot = ref<number | null>(null);
const toastMessage = ref<string | null>(null);

const bannerVisible = ref(false);
const bannerTitle = ref('');
const bannerSubtitle = ref('');

let toastTimer: number | null = null;
let bannerTimer: number | null = null;
let playtimeInterval: number | null = null;

const ZONE_META: Record<string, { title: string; subtitle: string }> = {
    'foundations-hub': {
        title: 'Foundations Hub',
        subtitle: 'Localhost Town • Central Crossroads',
    },
    'localhost-town': {
        title: 'Foundations Hub',
        subtitle: 'Localhost Town • Central Crossroads',
    },
    'algorithm-woods': {
        title: 'Algorithm Woods',
        subtitle: "Programmer's Path • Eastern Forest",
    },
    'artisan-city': {
        title: 'Artisan City',
        subtitle: "Developer's Path • Northern Citadel",
    },
    'legacy-harbor': {
        title: 'Legacy Harbor',
        subtitle: "Maintainer's Path • Western Coast",
    },
    'challenge-center': {
        title: 'Challenge Center',
        subtitle: 'Localhost Town • Training Terminal',
    },
    'starter-house': {
        title: 'Localhost Academy',
        subtitle: 'Foundations Zero-Knowledge Desk',
    },
    'logic-pavilion': {
        title: 'Logic Pavilion',
        subtitle: "Algorithm Woods • Programmer's Guild",
    },
    'billing-guild': {
        title: 'Billing Guild',
        subtitle: "Artisan City • Developer's Guild",
    },
    'security-warehouse': {
        title: 'Security Warehouse',
        subtitle: "Legacy Harbor • Maintainer's Guild",
    },
};

const currentZoneLabel = computed(
    () => ZONE_META[currentZone.value]?.title ?? 'Foundations Hub',
);

function getXsrfToken(): string {
    const match = document.cookie
        .split('; ')
        .find((row) => row.startsWith('XSRF-TOKEN='));

    return match ? decodeURIComponent(match.split('=')[1] ?? '') : '';
}

function showToast(message: string): void {
    toastMessage.value = message;

    if (toastTimer !== null) {
        window.clearTimeout(toastTimer);
    }

    toastTimer = window.setTimeout(() => {
        toastMessage.value = null;
    }, 3000);
}

function triggerZoneBanner(zone: string): void {
    const meta = ZONE_META[zone] ?? ZONE_META['foundations-hub'];
    bannerTitle.value = meta.title;
    bannerSubtitle.value = meta.subtitle;
    bannerVisible.value = true;

    if (bannerTimer !== null) {
        window.clearTimeout(bannerTimer);
    }

    bannerTimer = window.setTimeout(() => {
        bannerVisible.value = false;
    }, 2500);
}

function openWorldMap(): void {
    if (phaserOverlayOpen.value) {
        return;
    }

    audioManager.playSfx('uiConfirm');
    showSaveLoad.value = false;
    showWorldMap.value = true;
    gameEvents.emit('pause-overworld');
    void refreshGameState();
}

function closeWorldMap(): void {
    showWorldMap.value = false;
    gameEvents.emit('resume-overworld');
}

function openSaveLoad(): void {
    if (phaserOverlayOpen.value) {
        return;
    }

    audioManager.playSfx('uiConfirm');
    showWorldMap.value = false;
    showSaveLoad.value = true;
    gameEvents.emit('pause-overworld');
    void refreshGameState();
}

function closeSaveLoad(): void {
    audioManager.playSfx('uiCancel');
    showSaveLoad.value = false;
    gameEvents.emit('resume-overworld');
}

async function refreshGameState(): Promise<void> {
    try {
        const response = await fetch(gameStateRoute().url, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (response.ok) {
            gameState.value = (await response.json()) as GameStatePayload;
        }
    } catch {
        // Keep last known state if transient fetch fails
    }
}

async function persistSlot(
    slot: number,
    options?: {
        silent?: boolean;
        zone?: string;
        x?: number;
        y?: number;
        facing?: 'up' | 'down' | 'left' | 'right';
    },
): Promise<void> {
    busySlot.value = slot;

    try {
        const response = await fetch(saveGameSlotRoute(slot).url, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': getXsrfToken(),
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                current_zone: options?.zone ?? currentZone.value,
                player_x: options?.x ?? playerX.value,
                player_y: options?.y ?? playerY.value,
                facing: options?.facing ?? playerFacing.value,
                playtime_seconds: playtimeSeconds.value,
            }),
        });

        if (!response.ok) {
            throw new Error('Unable to save game slot.');
        }

        const data = (await response.json()) as { state: GameStatePayload };
        gameState.value = data.state;

        if (!options?.silent) {
            audioManager.playSfx('saveGame');
            showToast(
                slot === 0 ? 'Auto-Save updated.' : `Saved to Slot ${slot}.`,
            );
        }
    } catch {
        if (!options?.silent) {
            showToast('Failed to save slot. Please retry.');
        }
    } finally {
        busySlot.value = null;
    }
}

async function handleLoadSlot(slot: number): Promise<void> {
    busySlot.value = slot;

    try {
        const response = await fetch(loadGameSlotRoute(slot).url, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-XSRF-TOKEN': getXsrfToken(),
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('Unable to load save slot.');
        }

        const data = (await response.json()) as {
            loaded: {
                zone: string;
                x: number;
                y: number;
                facing: 'up' | 'down' | 'left' | 'right';
                playtimeSeconds: number;
                unlockedTerminals: string[];
            };
            state: GameStatePayload;
        };

        gameState.value = data.state;
        currentZone.value = data.loaded.zone;
        playerX.value = data.loaded.x;
        playerY.value = data.loaded.y;
        playerFacing.value = data.loaded.facing;
        playtimeSeconds.value = data.loaded.playtimeSeconds;

        showSaveLoad.value = false;
        audioManager.playSfx('loadGame');
        audioManager.playZoneBgm(data.loaded.zone);
        triggerZoneBanner(data.loaded.zone);

        phaserRef.value?.loadWorldState({
            zone: data.loaded.zone,
            x: data.loaded.x,
            y: data.loaded.y,
            facing: data.loaded.facing,
            unlockedTerminals: data.loaded.unlockedTerminals,
        });

        showToast(
            slot === 0
                ? 'Loaded Auto-Save.'
                : `Loaded Slot ${slot} (${ZONE_META[data.loaded.zone]?.title ?? data.loaded.zone}).`,
        );
    } catch {
        showToast('Failed to load save slot.');
    } finally {
        busySlot.value = null;
    }
}

async function handleDeleteSlot(slot: number): Promise<void> {
    busySlot.value = slot;

    try {
        const response = await fetch(deleteGameSlotRoute(slot).url, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-XSRF-TOKEN': getXsrfToken(),
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('Unable to delete save slot.');
        }

        const data = (await response.json()) as { state: GameStatePayload };
        gameState.value = data.state;
        audioManager.playSfx('uiCancel');
        showToast(`Deleted Slot ${slot}.`);
    } catch {
        showToast('Failed to delete save slot.');
    } finally {
        busySlot.value = null;
    }
}

async function handleFastTravel(zone: string): Promise<void> {
    const target = DISTRICT_SPAWNS[zone] ?? DISTRICT_SPAWNS['foundations-hub'];
    currentZone.value = zone;
    playerX.value = target.x;
    playerY.value = target.y;
    playerFacing.value = target.facing;

    showWorldMap.value = false;
    audioManager.playZoneBgm(zone);
    triggerZoneBanner(zone);

    phaserRef.value?.loadWorldState({
        zone,
        x: target.x,
        y: target.y,
        facing: target.facing,
        unlockedTerminals: gameState.value.unlockedTerminals,
    });

    await persistSlot(0, {
        silent: true,
        zone,
        x: target.x,
        y: target.y,
        facing: target.facing,
    });
    showToast(
        `Fast traveled to ${ZONE_META[zone]?.title ?? zone} (Auto-Saved).`,
    );
}

function handleLocationChanged(payload: PlayerLocationPayload): void {
    const zoneChanged = payload.zone !== currentZone.value;
    currentZone.value = payload.zone;
    playerX.value = payload.x;
    playerY.value = payload.y;
    mapWidth.value = payload.mapWidth;
    mapHeight.value = payload.mapHeight;
    playerFacing.value = payload.facing;

    if (payload.transitioned || zoneChanged) {
        audioManager.playZoneBgm(payload.zone);
        triggerZoneBanner(payload.zone);
        if (payload.transitioned) {
            void persistSlot(0, { silent: true });
        }
    }
}

function handleChallengeCompleted(payload: ResumeOverworldPayload): void {
    void (async () => {
        await refreshGameState();
        await persistSlot(0, { silent: true });
        if (payload.unlockedTriggerId) {
            showToast('Terminal unlocked & Auto-Saved!');
        }
    })();
}

function handleGlobalKeydown(event: KeyboardEvent): void {
    if (phaserOverlayOpen.value) {
        return;
    }

    const target = event.target as HTMLElement | null;
    if (
        target &&
        (target.tagName === 'INPUT' ||
            target.tagName === 'TEXTAREA' ||
            target.isContentEditable)
    ) {
        return;
    }

    if (event.key === 'm' || event.key === 'M') {
        event.preventDefault();
        if (showWorldMap.value) {
            closeWorldMap();
        } else {
            openWorldMap();
        }
        return;
    }

    if (event.key === 'Escape') {
        event.preventDefault();
        if (showWorldMap.value) {
            closeWorldMap();
        } else if (showSaveLoad.value) {
            closeSaveLoad();
        } else {
            openSaveLoad();
        }
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleGlobalKeydown);
    audioManager.playZoneBgm(currentZone.value);
    triggerZoneBanner(currentZone.value);

    playtimeInterval = window.setInterval(() => {
        playtimeSeconds.value += 1;
    }, 1000);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleGlobalKeydown);
    audioManager.stopBgm();
    if (playtimeInterval !== null) {
        window.clearInterval(playtimeInterval);
    }
    if (toastTimer !== null) {
        window.clearTimeout(toastTimer);
    }
    if (bannerTimer !== null) {
        window.clearTimeout(bannerTimer);
    }
});
</script>

<template>
    <div
        class="flex h-full flex-1 flex-col items-center justify-center gap-4 p-4 sm:p-6"
    >
        <Head title="Play World" />

        <!-- Compact HUD Bar -->
        <div
            data-testid="play-hud-bar"
            class="flex w-full max-w-[1000px] flex-wrap items-center justify-between gap-2 font-mono text-xs text-muted-foreground"
        >
            <div class="flex items-center gap-2.5 text-foreground">
                <AppLogoIcon class="h-8 w-auto shrink-0" />
                <span class="text-sm font-bold">The Stack Region</span>
                <span
                    data-testid="hud-current-zone"
                    class="rounded border border-indigo-400/40 bg-indigo-950/60 px-2 py-0.5 text-[11px] font-semibold text-indigo-200"
                >
                    {{ currentZoneLabel }}
                </span>
            </div>

            <div class="flex flex-wrap items-center gap-2 text-[11px]">
                <AudioControls />

                <button
                    type="button"
                    data-testid="open-world-map-btn"
                    @click="openWorldMap"
                    class="inline-flex cursor-pointer items-center gap-1.5 rounded border border-cyan-500/40 bg-card px-2.5 py-1 font-semibold text-foreground transition hover:border-cyan-400 hover:text-cyan-300"
                >
                    <Compass class="size-3.5 text-game-cyan" />
                    World Map (M)
                </button>

                <button
                    type="button"
                    data-testid="open-save-load-btn"
                    @click="openSaveLoad"
                    class="inline-flex cursor-pointer items-center gap-1.5 rounded border border-amber-500/40 bg-card px-2.5 py-1 font-semibold text-foreground transition hover:border-amber-400 hover:text-amber-300"
                >
                    <HardDrive class="size-3.5 text-game-amber" />
                    Save / Load (Esc)
                </button>

                <Link
                    :href="dashboard()"
                    class="inline-flex items-center gap-1 rounded border border-border bg-card px-2.5 py-1 font-semibold text-foreground transition hover:border-indigo-400 hover:text-primary"
                >
                    <ArrowLeft class="size-3" />
                    Dashboard
                </Link>
            </div>
        </div>

        <!-- Toast Confirmation -->
        <Transition name="zone-banner">
            <div
                v-if="toastMessage"
                data-testid="play-toast"
                class="fixed right-5 bottom-5 z-60 rounded-lg border border-emerald-400/60 bg-emerald-950/95 px-4 py-2.5 font-mono text-xs font-bold text-emerald-200 shadow-lg"
            >
                {{ toastMessage }}
            </div>
        </Transition>

        <!-- Phaser Canvas -->
        <div
            class="flex w-full max-w-[1000px] flex-1 items-center justify-center"
        >
            <PhaserGame
                ref="phaserRef"
                :world-state="worldState"
                :banner-visible="bannerVisible"
                :banner-title="bannerTitle"
                :banner-subtitle="bannerSubtitle"
                @location-changed="handleLocationChanged"
                @challenge-completed="handleChallengeCompleted"
                @overlay-change="phaserOverlayOpen = $event"
            />
        </div>

        <!-- Pokémon-Style Interactive Region Town Map (M) -->
        <Transition name="game-modal">
            <WorldMapModal
                v-if="showWorldMap"
                :current-zone="currentZone"
                :districts="gameState.districts"
                :unlocked-terminals="gameState.unlockedTerminals"
                :solved-slugs="gameState.solvedSlugs"
                :player-x="playerX"
                :player-y="playerY"
                :map-width="mapWidth"
                :map-height="mapHeight"
                @close="closeWorldMap"
                @fast-travel="handleFastTravel"
            />
        </Transition>

        <!-- GBA-Style Save & Load Modal (Esc) -->
        <Transition name="game-modal">
            <SaveLoadModal
                v-if="showSaveLoad"
                :slots="gameState.slots"
                :busy-slot="busySlot"
                :summary="gameState.summary"
                @close="closeSaveLoad"
                @save-slot="persistSlot($event)"
                @load-slot="handleLoadSlot"
                @delete-slot="handleDeleteSlot"
            />
        </Transition>
    </div>
</template>
