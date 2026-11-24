<script setup lang="ts">
import {
    Clock,
    Download,
    HardDrive,
    MapPin,
    Save,
    Trash2,
    Trophy,
    X,
} from '@lucide/vue';

export interface SaveSlotRecord {
    slot: number;
    isAutoSave: boolean;
    exists: boolean;
    label: string;
    currentZone: string | null;
    zoneName: string | null;
    playerX: number | null;
    playerY: number | null;
    facing: string | null;
    playtimeSeconds: number;
    savedAt: string | null;
    savedAtHuman: string | null;
}

defineProps<{
    slots: SaveSlotRecord[];
    busySlot: number | null;
    summary: {
        equippedTitle: string;
        level: number;
        totalXp: number;
        completedChallenges: number;
        totalChallenges: number;
    };
}>();

const emit = defineEmits<{
    close: [];
    saveSlot: [slot: number];
    loadSlot: [slot: number];
    deleteSlot: [slot: number];
}>();

function formatPlaytime(totalSeconds: number): string {
    const hrs = Math.floor(totalSeconds / 3600);
    const mins = Math.floor((totalSeconds % 3600) / 60);
    const secs = totalSeconds % 60;

    return [hrs, mins, secs]
        .map((unit) => String(unit).padStart(2, '0'))
        .join(':');
}
</script>

<template>
    <div
        role="dialog"
        aria-modal="true"
        aria-label="Save and Load Game Slots"
        data-testid="save-load-modal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/85 p-4 backdrop-blur-xs"
        @keydown.stop
    >
        <div
            class="flex w-full max-w-2xl flex-col overflow-hidden rounded-xl retro-bezel text-slate-100"
        >
            <header
                class="flex items-center justify-between border-b border-game-border bg-game-panel px-5 py-3 font-mono"
            >
                <div class="flex items-center gap-2.5">
                    <HardDrive class="size-4 text-game-amber" />
                    <span class="text-sm font-bold text-white">
                        Save &amp; Load Adventure
                    </span>
                </div>
                <button
                    type="button"
                    data-testid="close-save-load"
                    @click="emit('close')"
                    class="inline-flex cursor-pointer items-center gap-1 rounded border border-game-border bg-game-surface px-2.5 py-1 text-xs text-slate-300 transition hover:border-indigo-400 hover:text-white"
                >
                    <X class="size-3.5" />
                    Close (Esc)
                </button>
            </header>

            <div class="space-y-3 p-5 font-mono">
                <div
                    class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-indigo-400/35 bg-indigo-950/40 px-3.5 py-2 text-xs text-indigo-200"
                >
                    <span class="inline-flex items-center gap-1.5 font-bold">
                        <Trophy class="size-3.5 text-game-amber" />
                        Lv. {{ summary.level }} • {{ summary.totalXp }} XP ({{
                            summary.completedChallenges
                        }}/{{ summary.totalChallenges }} solved)
                    </span>
                    <span class="text-[11px] text-slate-300">
                        Coding progress &amp; unlocks never roll back on load.
                    </span>
                </div>

                <div class="space-y-2.5">
                    <div
                        v-for="item in slots"
                        :key="item.slot"
                        :data-testid="`save-slot-${item.slot}`"
                        class="flex flex-col justify-between gap-3 rounded-xl border-2 p-3.5 sm:flex-row sm:items-center"
                        :class="
                            item.isAutoSave
                                ? 'border-cyan-500/40 bg-cyan-950/20'
                                : 'border-game-border bg-game-panel'
                        "
                    >
                        <div class="min-w-0 space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    class="rounded px-2 py-0.5 text-[10px] font-bold uppercase"
                                    :class="
                                        item.isAutoSave
                                            ? 'border border-cyan-400/50 bg-cyan-950 text-cyan-200'
                                            : 'border border-indigo-400/40 bg-indigo-950 text-indigo-200'
                                    "
                                >
                                    {{
                                        item.isAutoSave
                                            ? 'Slot 0 • Auto-Save'
                                            : `Slot ${item.slot}`
                                    }}
                                </span>
                                <span
                                    v-if="item.exists"
                                    class="text-xs font-bold text-white"
                                >
                                    {{ item.zoneName }}
                                </span>
                                <span
                                    v-else
                                    class="text-xs text-slate-400 italic"
                                >
                                    Empty Slot
                                </span>
                            </div>

                            <div
                                v-if="item.exists"
                                class="flex flex-wrap items-center gap-3 text-[11px] text-slate-400"
                            >
                                <span class="inline-flex items-center gap-1">
                                    <MapPin class="size-3 text-game-emerald" />
                                    ({{ item.playerX }}, {{ item.playerY }})
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <Clock class="size-3 text-game-amber" />
                                    {{ formatPlaytime(item.playtimeSeconds) }}
                                </span>
                                <span v-if="item.savedAtHuman">
                                    Saved {{ item.savedAtHuman }}
                                </span>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-2">
                            <button
                                v-if="!item.isAutoSave"
                                type="button"
                                :disabled="busySlot !== null"
                                :data-testid="`save-slot-btn-${item.slot}`"
                                @click="emit('saveSlot', item.slot)"
                                class="inline-flex cursor-pointer items-center gap-1 rounded border border-emerald-400/60 bg-emerald-600/80 px-2.5 py-1 text-[11px] font-bold text-white transition hover:bg-emerald-500 disabled:opacity-50"
                            >
                                <Save class="size-3" />
                                Save
                            </button>

                            <button
                                v-if="item.exists"
                                type="button"
                                :disabled="busySlot !== null"
                                :data-testid="`load-slot-btn-${item.slot}`"
                                @click="emit('loadSlot', item.slot)"
                                class="inline-flex cursor-pointer items-center gap-1 rounded border border-indigo-400/60 bg-indigo-600 px-2.5 py-1 text-[11px] font-bold text-white transition hover:bg-indigo-500 disabled:opacity-50"
                            >
                                <Download class="size-3" />
                                Load
                            </button>

                            <button
                                v-if="!item.isAutoSave && item.exists"
                                type="button"
                                :disabled="busySlot !== null"
                                :data-testid="`delete-slot-btn-${item.slot}`"
                                @click="emit('deleteSlot', item.slot)"
                                class="inline-flex cursor-pointer items-center gap-1 rounded border border-rose-500/50 bg-rose-950/70 px-2 py-1 text-[11px] font-bold text-rose-200 transition hover:bg-rose-900 disabled:opacity-50"
                            >
                                <Trash2 class="size-3" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
