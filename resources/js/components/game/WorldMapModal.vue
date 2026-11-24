<script setup lang="ts">
import { Compass, MapPin, Navigation, X } from '@lucide/vue';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { audioManager } from '@/game/audio/AudioManager';

export interface DistrictStat {
    completed: number;
    total: number;
}

export interface DistrictStatsMap {
    foundations: DistrictStat;
    'algorithm-woods': DistrictStat;
    'artisan-city': DistrictStat;
    'legacy-harbor': DistrictStat;
}

interface RegionNode {
    zone:
        | 'foundations-hub'
        | 'artisan-city'
        | 'algorithm-woods'
        | 'legacy-harbor';
    statKey: keyof DistrictStatsMap;
    name: string;
    pathLabel: string;
    lore: string;
    cx: number;
    cy: number;
    box: { x: number; y: number; w: number; h: number };
    accentHex: string;
    terminalDots: Array<{ key: string; x: number; y: number }>;
}

const props = withDefaults(
    defineProps<{
        currentZone: string;
        districts: DistrictStatsMap;
        unlockedTerminals?: string[];
        solvedSlugs?: string[];
        playerX?: number;
        playerY?: number;
        mapWidth?: number;
        mapHeight?: number;
    }>(),
    {
        unlockedTerminals: () => [],
        solvedSlugs: () => [],
        playerX: 24,
        playerY: 18,
        mapWidth: 48,
        mapHeight: 36,
    },
);

const emit = defineEmits<{
    close: [];
    fastTravel: [zone: string];
}>();

const REGIONS: RegionNode[] = [
    {
        zone: 'foundations-hub',
        statKey: 'foundations',
        name: 'Foundations Hub',
        pathLabel: 'Localhost Town • Zero-Knowledge Onboarding',
        lore: 'The central crossroads of The Stack Region—home to Localhost Academy, the Challenge Center, and roads to every discipline.',
        cx: 320,
        cy: 165,
        box: { x: 268, y: 132, w: 104, h: 66 },
        accentHex: '#22d3ee',
        terminalDots: [
            { key: 'hello-elephpant', x: 288, y: 184 },
            { key: 'name-your-developer', x: 297, y: 184 },
            { key: 'potion-math-joining-text', x: 306, y: 184 },
            { key: 'gatekeeper-check', x: 315, y: 184 },
            { key: 'your-first-backpack', x: 324, y: 184 },
            { key: 'count-the-steps', x: 333, y: 184 },
            { key: 'build-a-mini-tool', x: 342, y: 184 },
            { key: 'your-first-bug-fix', x: 351, y: 184 },
        ],
    },
    {
        zone: 'artisan-city',
        statKey: 'artisan-city',
        name: 'Artisan City',
        pathLabel: "North • Developer's Path",
        lore: 'A gleaming citadel of paved boulevards, the Billing Guild, and the API Gateway where full-stack PHP & Laravel engineers forge production services.',
        cx: 320,
        cy: 56,
        box: { x: 256, y: 22, w: 128, h: 66 },
        accentHex: '#818cf8',
        terminalDots: [
            { key: 'localhost-challenge-center', x: 282, y: 74 },
            { key: 'artisan-city-normalizer-terminal', x: 301, y: 74 },
            { key: 'artisan-city-api-gateway', x: 320, y: 74 },
            { key: 'artisan-city-billing-guild', x: 339, y: 74 },
            { key: 'artisan-city-cursor-terminal', x: 358, y: 74 },
        ],
    },
    {
        zone: 'algorithm-woods',
        statKey: 'algorithm-woods',
        name: 'Algorithm Woods',
        pathLabel: "East • Programmer's Path",
        lore: 'Lush forest trails, stepping-stone river crossings, and the Logic Pavilion—testing data structures, graph traversal, and algorithmic precision.',
        cx: 525,
        cy: 165,
        box: { x: 454, y: 120, w: 142, h: 90 },
        accentHex: '#34d399',
        terminalDots: [
            { key: 'algorithm-woods-bridge', x: 476, y: 186 },
            { key: 'algorithm-woods-delimiter-terminal', x: 499, y: 170 },
            { key: 'algorithm-woods-cycle-terminal', x: 522, y: 188 },
            { key: 'algorithm-woods-pavilion', x: 545, y: 164 },
            { key: 'algorithm-woods-lru-terminal', x: 568, y: 184 },
        ],
    },
    {
        zone: 'legacy-harbor',
        statKey: 'legacy-harbor',
        name: 'Legacy Harbor',
        pathLabel: "West • Maintainer's Path",
        lore: 'Weathered timber piers, coastal boardwalks, and the Security Warehouse where seasoned maintainers untangle regressions and harden critical systems.',
        cx: 118,
        cy: 165,
        box: { x: 44, y: 120, w: 146, h: 90 },
        accentHex: '#fbbf24',
        terminalDots: [
            { key: 'legacy-harbor-timezone-terminal', x: 164, y: 152 },
            { key: 'legacy-harbor-dock-crane', x: 144, y: 174 },
            { key: 'legacy-harbor-discount-terminal', x: 124, y: 192 },
            { key: 'legacy-harbor-security-warehouse', x: 102, y: 160 },
            { key: 'legacy-harbor-stock-terminal', x: 78, y: 182 },
        ],
    },
];

function normalizeCurrentDistrict(zone: string): RegionNode['zone'] {
    if (zone === 'algorithm-woods' || zone === 'logic-pavilion') {
        return 'algorithm-woods';
    }
    if (zone === 'artisan-city' || zone === 'billing-guild') {
        return 'artisan-city';
    }
    if (zone === 'legacy-harbor' || zone === 'security-warehouse') {
        return 'legacy-harbor';
    }
    return 'foundations-hub';
}

const currentDistrictZone = computed<RegionNode['zone']>(() =>
    normalizeCurrentDistrict(props.currentZone),
);

const selectedZone = ref<RegionNode['zone']>(currentDistrictZone.value);

const selectedRegion = computed<RegionNode>(
    () => REGIONS.find((r) => r.zone === selectedZone.value) ?? REGIONS[0],
);

const selectedStat = computed<DistrictStat>(
    () => props.districts[selectedRegion.value.statKey],
);

const selectedPercent = computed<number>(() => {
    const { completed, total } = selectedStat.value;
    if (total <= 0) {
        return 0;
    }
    return Math.min(100, Math.round((completed / total) * 100));
});

const isAlreadyHere = computed<boolean>(
    () => selectedRegion.value.zone === currentDistrictZone.value,
);

const solvedLookup = computed<Set<string>>(
    () => new Set([...props.unlockedTerminals, ...props.solvedSlugs]),
);

const playerMarkerPos = computed<{ x: number; y: number }>(() => {
    const region =
        REGIONS.find((r) => r.zone === currentDistrictZone.value) ?? REGIONS[0];
    const rx = Math.max(
        0,
        Math.min(1, props.playerX / Math.max(1, props.mapWidth)),
    );
    const ry = Math.max(
        0,
        Math.min(1, props.playerY / Math.max(1, props.mapHeight)),
    );

    return {
        x: Math.round(region.box.x + 12 + rx * (region.box.w - 24)),
        y: Math.round(region.box.y + 12 + ry * (region.box.h - 24)),
    };
});

function selectRegion(zone: RegionNode['zone']): void {
    if (selectedZone.value !== zone) {
        selectedZone.value = zone;
        audioManager.playSfx('uiHover');
    }
}

function triggerFastTravel(): void {
    if (isAlreadyHere.value) {
        audioManager.playSfx('uiCancel');
        return;
    }
    audioManager.playSfx('fastTravel');
    emit('fastTravel', selectedRegion.value.zone);
}

function handleKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape' || event.key === 'm' || event.key === 'M') {
        event.preventDefault();
        audioManager.playSfx('uiCancel');
        emit('close');
        return;
    }

    if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        triggerFastTravel();
        return;
    }

    const key = event.key.toLowerCase();
    if (key === 'w' || event.key === 'ArrowUp') {
        event.preventDefault();
        selectRegion('artisan-city');
    } else if (key === 'd' || event.key === 'ArrowRight') {
        event.preventDefault();
        selectRegion(
            selectedZone.value === 'legacy-harbor'
                ? 'foundations-hub'
                : 'algorithm-woods',
        );
    } else if (key === 'a' || event.key === 'ArrowLeft') {
        event.preventDefault();
        selectRegion(
            selectedZone.value === 'algorithm-woods'
                ? 'foundations-hub'
                : 'legacy-harbor',
        );
    } else if (key === 's' || event.key === 'ArrowDown') {
        event.preventDefault();
        selectRegion('foundations-hub');
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <div
        role="dialog"
        aria-modal="true"
        aria-label="Stack Region Town Map"
        data-testid="world-map-modal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/85 p-3 backdrop-blur-xs sm:p-5"
        @keydown.stop
    >
        <div
            class="flex w-full max-w-4xl flex-col overflow-hidden rounded-2xl retro-bezel text-slate-100"
        >
            <!-- GBA Device Header -->
            <header
                class="flex items-center justify-between border-b border-game-border bg-game-panel px-4 py-2.5 font-mono"
            >
                <div class="flex items-center gap-2.5">
                    <Compass class="size-4 text-game-cyan" />
                    <span class="text-sm font-bold tracking-wide text-white">
                        STACK REGION • TOWN MAP
                    </span>
                    <span
                        class="hidden rounded border border-cyan-500/40 bg-cyan-950/70 px-2 py-0.5 text-[10px] text-cyan-200 sm:inline"
                    >
                        WASD / Arrows: Select • Enter: Fast Travel
                    </span>
                </div>
                <button
                    type="button"
                    data-testid="close-world-map"
                    @click="
                        audioManager.playSfx('uiCancel');
                        emit('close');
                    "
                    class="inline-flex cursor-pointer items-center gap-1 rounded border border-game-border bg-game-surface px-2.5 py-1 text-xs text-slate-300 transition hover:border-indigo-400 hover:text-white"
                >
                    <X class="size-3.5" />
                    Close (M)
                </button>
            </header>

            <!-- Interactive 16-Bit Region SVG Canvas -->
            <div class="relative bg-[#091224] p-3 sm:p-4">
                <svg
                    viewBox="0 0 640 300"
                    class="h-auto w-full rounded-xl border-2 border-slate-700/80 bg-[#0d1c33] shadow-inner select-none"
                    shape-rendering="crispEdges"
                >
                    <!-- Western Ocean (Legacy Harbor Coastline) -->
                    <rect x="0" y="0" width="142" height="300" fill="#0c365c" />
                    <rect
                        x="142"
                        y="100"
                        width="24"
                        height="145"
                        fill="#0f416e"
                    />
                    <g class="map-ocean-shimmer">
                        <rect
                            x="14"
                            y="48"
                            width="26"
                            height="3"
                            fill="#38bdf8"
                        />
                        <rect
                            x="34"
                            y="106"
                            width="22"
                            height="3"
                            fill="#38bdf8"
                        />
                        <rect
                            x="18"
                            y="188"
                            width="28"
                            height="3"
                            fill="#38bdf8"
                        />
                        <rect
                            x="46"
                            y="238"
                            width="24"
                            height="3"
                            fill="#38bdf8"
                        />
                    </g>

                    <!-- Mainland Grass Base -->
                    <rect
                        x="142"
                        y="14"
                        width="484"
                        height="224"
                        rx="8"
                        fill="#18382c"
                    />
                    <rect
                        x="156"
                        y="22"
                        width="460"
                        height="206"
                        rx="6"
                        fill="#1d4434"
                    />

                    <!-- Algorithm Woods (East Forest Canopy & Stream) -->
                    <rect
                        x="444"
                        y="108"
                        width="164"
                        height="114"
                        rx="6"
                        fill="#143a27"
                    />
                    <rect
                        x="506"
                        y="114"
                        width="12"
                        height="102"
                        fill="#0ea5e9"
                        opacity="0.65"
                    />
                    <rect
                        x="502"
                        y="158"
                        width="20"
                        height="12"
                        fill="#b45309"
                    />
                    <!-- Pixel trees -->
                    <rect
                        x="456"
                        y="124"
                        width="12"
                        height="12"
                        fill="#16a34a"
                    />
                    <rect
                        x="476"
                        y="132"
                        width="12"
                        height="12"
                        fill="#15803d"
                    />
                    <rect
                        x="546"
                        y="122"
                        width="14"
                        height="14"
                        fill="#16a34a"
                    />
                    <rect
                        x="576"
                        y="134"
                        width="12"
                        height="12"
                        fill="#22c55e"
                    />
                    <rect
                        x="564"
                        y="196"
                        width="14"
                        height="12"
                        fill="#15803d"
                    />

                    <!-- Artisan City (North Citadel Skyline) -->
                    <rect
                        x="246"
                        y="18"
                        width="148"
                        height="74"
                        rx="4"
                        fill="#242952"
                    />
                    <rect
                        x="262"
                        y="26"
                        width="22"
                        height="24"
                        fill="#4338ca"
                    />
                    <rect
                        x="292"
                        y="22"
                        width="26"
                        height="28"
                        fill="#4f46e5"
                    />
                    <rect
                        x="326"
                        y="20"
                        width="24"
                        height="30"
                        fill="#6366f1"
                    />
                    <rect
                        x="356"
                        y="28"
                        width="22"
                        height="22"
                        fill="#4338ca"
                    />
                    <!-- Citadel windows -->
                    <rect x="298" y="28" width="4" height="4" fill="#fde68a" />
                    <rect x="308" y="28" width="4" height="4" fill="#fde68a" />
                    <rect x="332" y="26" width="4" height="4" fill="#a5f3fc" />
                    <rect x="340" y="26" width="4" height="4" fill="#a5f3fc" />

                    <!-- Legacy Harbor (West Boardwalk, Piers & Lighthouse) -->
                    <rect
                        x="112"
                        y="114"
                        width="78"
                        height="102"
                        rx="4"
                        fill="#42362a"
                    />
                    <rect
                        x="50"
                        y="136"
                        width="66"
                        height="14"
                        fill="#78350f"
                    />
                    <rect
                        x="44"
                        y="176"
                        width="72"
                        height="14"
                        fill="#78350f"
                    />
                    <!-- Lighthouse -->
                    <rect
                        x="54"
                        y="118"
                        width="12"
                        height="22"
                        fill="#f8fafc"
                    />
                    <rect x="54" y="124" width="12" height="4" fill="#ef4444" />
                    <rect x="56" y="114" width="8" height="4" fill="#fde047" />

                    <!-- Foundations Hub (Center Cobblestone Plaza) -->
                    <rect
                        x="268"
                        y="132"
                        width="104"
                        height="66"
                        rx="4"
                        fill="#334155"
                    />
                    <rect
                        x="276"
                        y="140"
                        width="88"
                        height="50"
                        fill="#475569"
                    />
                    <rect
                        x="312"
                        y="156"
                        width="16"
                        height="16"
                        rx="2"
                        fill="#38bdf8"
                        opacity="0.8"
                    />

                    <!-- Connecting Roads (1-to-1 with Overworld Cardinal Exits) -->
                    <!-- North Road: Foundations Hub <-> Artisan City -->
                    <rect
                        x="312"
                        y="88"
                        width="16"
                        height="44"
                        fill="#cbd5e1"
                        opacity="0.7"
                    />
                    <!-- East Road: Foundations Hub <-> Algorithm Woods -->
                    <rect
                        x="372"
                        y="158"
                        width="82"
                        height="14"
                        fill="#d6b482"
                        opacity="0.85"
                    />
                    <!-- West Road: Foundations Hub <-> Legacy Harbor -->
                    <rect
                        x="190"
                        y="158"
                        width="78"
                        height="14"
                        fill="#cbd5e1"
                        opacity="0.7"
                    />

                    <!-- South Clouded Frontier (Future Milestone 6 Teaser) -->
                    <g opacity="0.85">
                        <rect
                            x="164"
                            y="242"
                            width="312"
                            height="44"
                            rx="10"
                            fill="#1e293b"
                            stroke="#475569"
                            stroke-width="1.5"
                            stroke-dasharray="4 3"
                        />
                        <circle cx="210" cy="262" r="14" fill="#334155" />
                        <circle cx="232" cy="258" r="16" fill="#475569" />
                        <circle cx="408" cy="260" r="16" fill="#334155" />
                        <circle cx="432" cy="264" r="13" fill="#475569" />
                        <text
                            x="320"
                            y="268"
                            text-anchor="middle"
                            fill="#94a3b8"
                            font-family="monospace"
                            font-size="11"
                            font-weight="bold"
                        >
                            ??? — COMING SOON (SOUTHERN FRONTIER)
                        </text>
                    </g>

                    <!-- Clickable District Nodes + Mini Terminal Dots -->
                    <g
                        v-for="region in REGIONS"
                        :key="region.zone"
                        :data-testid="`world-map-node-${region.zone}`"
                        class="cursor-pointer"
                        @mouseenter="selectRegion(region.zone)"
                        @click="selectRegion(region.zone)"
                    >
                        <rect
                            :x="region.box.x"
                            :y="region.box.y"
                            :width="region.box.w"
                            :height="region.box.h"
                            rx="6"
                            fill="transparent"
                            :stroke="
                                selectedZone === region.zone
                                    ? region.accentHex
                                    : '#64748b'
                            "
                            :stroke-width="
                                selectedZone === region.zone ? 3 : 1.5
                            "
                        />

                        <!-- District Name Banner -->
                        <rect
                            :x="region.cx - 56"
                            :y="region.box.y - 11"
                            width="112"
                            height="16"
                            rx="3"
                            fill="#020617"
                            :stroke="region.accentHex"
                            stroke-width="1"
                        />
                        <text
                            :x="region.cx"
                            :y="region.box.y"
                            text-anchor="middle"
                            fill="#f8fafc"
                            font-family="monospace"
                            font-size="9"
                            font-weight="bold"
                        >
                            {{ region.name }}
                        </text>

                        <!-- Terminal Progress Dots -->
                        <circle
                            v-for="dot in region.terminalDots"
                            :key="dot.key"
                            :cx="dot.x"
                            :cy="dot.y"
                            r="3.5"
                            :fill="
                                solvedLookup.has(dot.key)
                                    ? '#34d399'
                                    : '#f59e0b'
                            "
                            stroke="#020617"
                            stroke-width="1"
                        />
                    </g>

                    <!-- Live Blinking elePHPant Player Marker -->
                    <g
                        :transform="`translate(${playerMarkerPos.x}, ${playerMarkerPos.y})`"
                    >
                        <circle
                            r="9"
                            fill="#818cf8"
                            opacity="0.35"
                            class="animate-ping"
                        />
                        <circle
                            r="6"
                            fill="#6366f1"
                            stroke="#ffffff"
                            stroke-width="2"
                        />
                        <circle r="2" fill="#ffffff" />
                    </g>
                </svg>
            </div>

            <!-- GBA Bottom Readout & Fast Travel Bar -->
            <footer
                class="flex flex-col justify-between gap-4 border-t border-game-border bg-game-panel px-5 py-4 font-mono sm:flex-row sm:items-center"
            >
                <div class="min-w-0 flex-1 space-y-1.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-sm font-bold text-white">
                            {{ selectedRegion.name }}
                        </h3>
                        <span
                            class="rounded border border-indigo-400/40 bg-indigo-950/70 px-2 py-0.5 text-[10px] font-semibold text-indigo-200"
                        >
                            {{ selectedRegion.pathLabel }}
                        </span>
                        <span
                            v-if="isAlreadyHere"
                            class="inline-flex items-center gap-1 rounded-full border border-emerald-400/50 bg-emerald-950/80 px-2 py-0.5 text-[10px] font-bold text-emerald-300 uppercase"
                        >
                            <MapPin class="size-3" />
                            You are here
                        </span>
                    </div>

                    <p class="text-xs leading-relaxed text-slate-300">
                        {{ selectedRegion.lore }}
                    </p>

                    <div class="flex items-center gap-3 pt-1">
                        <div
                            class="h-2 w-40 overflow-hidden rounded-full bg-slate-800"
                        >
                            <div
                                class="h-full rounded-full transition-all"
                                :style="{
                                    width: `${selectedPercent}%`,
                                    backgroundColor: selectedRegion.accentHex,
                                }"
                            />
                        </div>
                        <span class="text-[11px] font-bold text-slate-200">
                            {{ selectedStat.completed }} /
                            {{ selectedStat.total }} Terminals Solved
                        </span>
                    </div>
                </div>

                <div class="flex shrink-0 items-center">
                    <button
                        type="button"
                        :disabled="isAlreadyHere"
                        :data-testid="`fast-travel-${selectedRegion.zone}`"
                        @click="triggerFastTravel"
                        class="inline-flex cursor-pointer items-center gap-2 rounded-lg border-2 px-4 py-2.5 text-xs font-bold transition disabled:cursor-not-allowed disabled:opacity-50"
                        :class="
                            isAlreadyHere
                                ? 'border-slate-600 bg-slate-800 text-slate-400'
                                : 'border-indigo-400 bg-indigo-600 text-white shadow-lg shadow-indigo-950/60 hover:bg-indigo-500'
                        "
                    >
                        <Navigation class="size-4" />
                        {{ isAlreadyHere ? 'You Are Here' : 'Fast Travel' }}
                    </button>
                </div>
            </footer>
        </div>
    </div>
</template>
