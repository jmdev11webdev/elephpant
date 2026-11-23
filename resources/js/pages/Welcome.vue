<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Code2,
    Compass,
    Gamepad2,
    Mail,
    MapPin,
    Send,
    ShieldCheck,
    Sparkles,
    Terminal,
    Wrench,
} from '@lucide/vue';
import { ref } from 'vue';
import PixelHeroCanvas from '@/components/landing/PixelHeroCanvas.vue';
import { dashboard, login, register } from '@/routes';

type PreviewTab = 'overworld' | 'interior' | 'dialogue' | 'controls';

const activePreview = ref<PreviewTab>('overworld');

const previewDetails: Record<
    PreviewTab,
    {
        badge: string;
        title: string;
        speaker: string;
        lines: string[];
        highlights: string[];
    }
> = {
    overworld: {
        badge: 'OVERWORLD HUB',
        title: 'Explore Localhost Town at 3x GBA Zoom',
        speaker: 'Mentor Ada',
        lines: [
            'Welcome to Localhost Town, the heart of the Stack Region!',
            'Walk behind tree canopies and roof eaves with true 5-layer depth sorting.',
        ],
        highlights: [
            '16×16 pixel-art Tiled JSON maps with crisp nearest-neighbor rendering',
            'Smooth 4-directional grid-aligned movement (150ms tweened steps)',
            'Hub-and-spoke roads leading toward Algorithm Woods, Artisan City & Legacy Harbor',
        ],
    },
    interior: {
        badge: 'DOOR WARPS',
        title: 'Enter the Challenge Center Seamlessly',
        speaker: 'Lab Coordinator',
        lines: [
            'Step onto any doorway trigger for a crisp 200ms camera fade transition.',
            'Inspect the glowing Challenge Terminal on the east wing of the lab.',
        ],
        highlights: [
            'Spawn-ID warp routing between OverworldScene and InteriorScene',
            'Dedicated Challenge Terminal preparing for the embedded Monaco IDE',
            'Zero page reload—powered by Phaser 3.90.0 inside Vue 3 + Inertia v3',
        ],
    },
    dialogue: {
        badge: 'EVENT BRIDGE',
        title: 'Retro GBA Dialogue Overlay in Vue 3',
        speaker: 'Challenge Terminal',
        lines: [
            '[TERMINAL ONLINE] Typed Phaser–Vue event bridge active.',
            'Overworld movement pauses cleanly while dialogue or IDE overlays are open.',
        ],
        highlights: [
            'Strict separation: Phaser owns 2D gameplay, Vue owns retro overlays',
            'Multi-line pagination with Space, Enter, Click, and Escape shortcuts',
            'Clean lifecycle teardown via game.destroy(true) on Inertia navigation',
        ],
    },
    controls: {
        badge: 'CONTROLS',
        title: 'Classic Keyboard Controls Built In',
        speaker: 'Town Sign',
        lines: [
            'Move with WASD or Arrow Keys. Interact with E, Space, or Enter.',
            'Press Escape anytime to dismiss active dialogue overlays.',
        ],
        highlights: [
            'WASD / Arrow Keys — 4-directional grid movement (page scroll captured)',
            'E / Space / Enter — Talk to NPCs, read signs, and inspect terminals',
            'Escape — Close overlays and resume overworld exploration immediately',
        ],
    },
};

const contactName = ref('');
const contactEmail = ref('');
const contactMessage = ref('');
const contactSubmitted = ref(false);

function submitContact(): void {
    if (
        !contactName.value.trim() ||
        !contactEmail.value.trim() ||
        !contactMessage.value.trim()
    ) {
        return;
    }

    contactSubmitted.value = true;
    contactName.value = '';
    contactEmail.value = '';
    contactMessage.value = '';
}
</script>

<template>
    <Head title="elePHPant — 16-Bit Developer Adventure RPG" />

    <div
        class="min-h-screen scroll-smooth bg-game-surface text-slate-100 selection:bg-game-accent selection:text-white"
    >
        <!-- Sticky Retro Navbar -->
        <header
            class="sticky top-0 z-40 border-b border-game-border/90 bg-game-surface/90 backdrop-blur"
        >
            <div
                class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3.5 sm:px-6"
            >
                <a
                    href="#home"
                    class="flex items-center gap-2.5 font-mono text-base font-bold tracking-tight text-white"
                >
                    <span
                        class="flex size-8 items-center justify-center rounded border-2 border-indigo-400 bg-indigo-950 text-indigo-200 shadow-sm"
                    >
                        <Gamepad2 class="size-4" />
                    </span>
                    <span>ele<span class="text-indigo-400">PHP</span>ant</span>
                </a>

                <nav
                    aria-label="Primary"
                    class="hidden items-center gap-6 font-mono text-xs tracking-wide text-slate-300 uppercase md:flex"
                >
                    <a href="#home" class="transition hover:text-indigo-300">
                        Home
                    </a>
                    <a href="#about" class="transition hover:text-indigo-300">
                        About
                    </a>
                    <a
                        href="#gameplay"
                        class="transition hover:text-indigo-300"
                    >
                        Gameplay Preview
                    </a>
                    <a href="#contact" class="transition hover:text-indigo-300">
                        Contact
                    </a>
                </nav>

                <div class="flex items-center gap-2.5 font-mono text-xs">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="inline-flex items-center gap-1.5 rounded-md border-2 border-indigo-400 bg-indigo-600 px-3.5 py-1.5 font-semibold text-white transition hover:bg-indigo-500"
                    >
                        <Gamepad2 class="size-3.5" />
                        Play Now
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="rounded-md border border-slate-700 px-3 py-1.5 text-slate-200 transition hover:border-indigo-400 hover:text-white"
                        >
                            Log in
                        </Link>
                        <Link
                            :href="register()"
                            class="rounded-md border-2 border-indigo-400 bg-indigo-600 px-3.5 py-1.5 font-semibold text-white transition hover:bg-indigo-500"
                        >
                            Start Free
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <main>
            <!-- HOME / HERO SECTION -->
            <section
                id="home"
                class="relative overflow-hidden border-b border-slate-800/80 py-14 sm:py-20"
            >
                <div
                    class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(99,102,241,0.18),transparent_45%),radial-gradient(circle_at_bottom_left,rgba(34,197,94,0.10),transparent_40%)]"
                />

                <div
                    class="relative mx-auto grid max-w-6xl items-center gap-10 px-4 sm:px-6 lg:grid-cols-12"
                >
                    <div class="space-y-6 lg:col-span-5">
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-indigo-400/40 bg-indigo-950/80 px-3 py-1 font-mono text-xs text-indigo-200"
                        >
                            <Sparkles class="size-3.5 text-amber-300" />
                            16-Bit PHP Developer RPG
                        </div>

                        <h1
                            class="font-mono text-3xl leading-tight font-bold tracking-tight text-white sm:text-4xl lg:text-5xl"
                        >
                            Explore the
                            <span class="text-indigo-400">Stack Region</span>.
                            Code Your Adventure.
                        </h1>

                        <p
                            class="text-sm leading-relaxed text-slate-300 sm:text-base"
                        >
                            <strong>elePHPant</strong> is an original 2D, 16-bit
                            pixel-art adventure inspired by classic GBA RPGs and
                            PHP developer culture. Walk through Localhost Town,
                            talk to mentors, and unlock real engineering
                            challenges.
                        </p>

                        <div
                            class="flex flex-wrap items-center gap-3 pt-1 font-mono text-xs"
                        >
                            <Link
                                :href="
                                    $page.props.auth.user
                                        ? dashboard()
                                        : register()
                                "
                                class="inline-flex items-center gap-2 rounded-md border-2 border-indigo-400 bg-indigo-600 px-5 py-2.5 font-bold text-white shadow-lg shadow-indigo-950/60 transition hover:bg-indigo-500"
                            >
                                <Gamepad2 class="size-4" />
                                {{
                                    $page.props.auth.user
                                        ? 'Enter Localhost Town'
                                        : 'Begin Your Adventure'
                                }}
                            </Link>

                            <a
                                href="#gameplay"
                                class="inline-flex items-center gap-2 rounded-md border border-slate-700 bg-slate-900 px-4 py-2.5 font-semibold text-slate-200 transition hover:border-indigo-400 hover:text-white"
                            >
                                <Compass class="size-4 text-emerald-400" />
                                Preview Gameplay
                            </a>
                        </div>

                        <div
                            class="grid grid-cols-3 gap-3 border-t border-slate-800/80 pt-5 font-mono text-xs"
                        >
                            <div>
                                <div class="font-bold text-white">16×16 px</div>
                                <div class="text-slate-400">GBA Tilemaps</div>
                            </div>
                            <div>
                                <div class="font-bold text-emerald-400">
                                    3 Paths
                                </div>
                                <div class="text-slate-400">
                                    Code Disciplines
                                </div>
                            </div>
                            <div>
                                <div class="font-bold text-amber-300">
                                    Open Source
                                </div>
                                <div class="text-slate-400">
                                    Laravel + Phaser
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-7">
                        <PixelHeroCanvas />
                    </div>
                </div>
            </section>

            <!-- ABOUT SECTION -->
            <section
                id="about"
                class="border-b border-slate-800/80 bg-slate-900/40 py-16 sm:py-20"
            >
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <div class="max-w-2xl space-y-3">
                        <p
                            class="font-mono text-xs font-bold tracking-widest text-indigo-400 uppercase"
                        >
                            About elePHPant
                        </p>
                        <h2
                            class="font-mono text-2xl font-bold text-white sm:text-3xl"
                        >
                            A Real Playable 2D World—Not Just Another Quiz App
                        </h2>
                        <p
                            class="text-sm leading-relaxed text-slate-300 sm:text-base"
                        >
                            Built with Laravel, Vue 3, Inertia.js, and Phaser
                            3.90.0, elePHPant blends nostalgic 16-bit
                            exploration with practical software engineering
                            across three non-exclusive disciplines.
                        </p>
                    </div>

                    <div class="mt-10 grid gap-5 md:grid-cols-3">
                        <article
                            class="rounded-xl border-2 border-slate-800 bg-slate-950/90 p-5 transition hover:border-emerald-500/60"
                        >
                            <div
                                class="mb-4 flex size-10 items-center justify-center rounded-lg border border-emerald-500/40 bg-emerald-950/70 text-emerald-300"
                            >
                                <Code2 class="size-5" />
                            </div>
                            <p
                                class="font-mono text-[11px] font-semibold tracking-wider text-emerald-400 uppercase"
                            >
                                East • Algorithm Woods
                            </p>
                            <h3
                                class="mt-1 font-mono text-lg font-bold text-white"
                            >
                                Programmer’s Path
                            </h3>
                            <p
                                class="mt-2 text-sm leading-relaxed text-slate-400"
                            >
                                Master fundamentals, data structures,
                                computational thinking, and algorithmic puzzles
                                hidden along forest trails and stepping-stone
                                clearings.
                            </p>
                        </article>

                        <article
                            class="rounded-xl border-2 border-slate-800 bg-slate-950/90 p-5 transition hover:border-indigo-500/60"
                        >
                            <div
                                class="mb-4 flex size-10 items-center justify-center rounded-lg border border-indigo-500/40 bg-indigo-950/70 text-indigo-300"
                            >
                                <Terminal class="size-5" />
                            </div>
                            <p
                                class="font-mono text-[11px] font-semibold tracking-wider text-indigo-400 uppercase"
                            >
                                North • Artisan City
                            </p>
                            <h3
                                class="mt-1 font-mono text-lg font-bold text-white"
                            >
                                Developer’s Path
                            </h3>
                            <p
                                class="mt-2 text-sm leading-relaxed text-slate-400"
                            >
                                Build real CRUD workflows, APIs, validation
                                guards, and full-stack integrations inside
                                Artisan City’s developer guilds.
                            </p>
                        </article>

                        <article
                            class="rounded-xl border-2 border-slate-800 bg-slate-950/90 p-5 transition hover:border-amber-500/60"
                        >
                            <div
                                class="mb-4 flex size-10 items-center justify-center rounded-lg border border-amber-500/40 bg-amber-950/70 text-amber-300"
                            >
                                <Wrench class="size-5" />
                            </div>
                            <p
                                class="font-mono text-[11px] font-semibold tracking-wider text-amber-400 uppercase"
                            >
                                West • Legacy Harbor
                            </p>
                            <h3
                                class="mt-1 font-mono text-lg font-bold text-white"
                            >
                                Maintainer’s Path
                            </h3>
                            <p
                                class="mt-2 text-sm leading-relaxed text-slate-400"
                            >
                                Debug broken production code, refactor legacy
                                docks, close security gaps, and harden test
                                suites with confidence.
                            </p>
                        </article>
                    </div>
                </div>
            </section>

            <!-- GAMEPLAY PREVIEW SECTION -->
            <section
                id="gameplay"
                class="border-b border-slate-800/80 py-16 sm:py-20"
            >
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <div
                        class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"
                    >
                        <div class="max-w-2xl space-y-2">
                            <p
                                class="font-mono text-xs font-bold tracking-widest text-emerald-400 uppercase"
                            >
                                Gameplay Preview
                            </p>
                            <h2
                                class="font-mono text-2xl font-bold text-white sm:text-3xl"
                            >
                                Inside the Localhost Town Vertical Slice
                            </h2>
                        </div>

                        <div class="flex flex-wrap gap-2 font-mono text-xs">
                            <button
                                v-for="(tab, key) in previewDetails"
                                :key="key"
                                type="button"
                                @click="activePreview = key"
                                class="cursor-pointer rounded-md border px-3 py-1.5 transition"
                                :class="
                                    activePreview === key
                                        ? 'border-indigo-400 bg-indigo-600 font-bold text-white'
                                        : 'border-slate-800 bg-slate-900 text-slate-300 hover:border-slate-600'
                                "
                            >
                                {{ tab.badge }}
                            </button>
                        </div>
                    </div>

                    <div
                        class="mt-8 grid gap-6 rounded-2xl border-2 border-slate-800 bg-slate-900/70 p-5 sm:p-8 lg:grid-cols-12"
                    >
                        <div class="space-y-4 lg:col-span-6">
                            <span
                                class="inline-block rounded border border-indigo-400/50 bg-indigo-950 px-2.5 py-0.5 font-mono text-[11px] font-bold text-indigo-200"
                            >
                                {{ previewDetails[activePreview].badge }}
                            </span>
                            <h3 class="font-mono text-xl font-bold text-white">
                                {{ previewDetails[activePreview].title }}
                            </h3>
                            <ul class="space-y-2.5 text-sm text-slate-300">
                                <li
                                    v-for="item in previewDetails[activePreview]
                                        .highlights"
                                    :key="item"
                                    class="flex items-start gap-2.5"
                                >
                                    <span
                                        class="mt-1.5 inline-block size-2 shrink-0 rounded-xs bg-emerald-400"
                                    />
                                    <span>{{ item }}</span>
                                </li>
                            </ul>

                            <div class="pt-2">
                                <Link
                                    :href="
                                        $page.props.auth.user
                                            ? dashboard()
                                            : login()
                                    "
                                    class="inline-flex items-center gap-2 font-mono text-xs font-bold text-indigo-300 underline-offset-4 hover:text-indigo-200 hover:underline"
                                >
                                    Launch playable dashboard →
                                </Link>
                            </div>
                        </div>

                        <!-- Interactive GBA Dialogue Box Mockup -->
                        <div
                            class="flex flex-col justify-between rounded-xl border-2 border-slate-700 bg-slate-950 p-5 lg:col-span-6"
                        >
                            <div
                                class="mb-4 flex items-center justify-between font-mono text-[11px] text-slate-400"
                            >
                                <span>IN-GAME DIALOGUE OVERLAY</span>
                                <span class="text-emerald-400">
                                    PHASER ↔ VUE BRIDGE
                                </span>
                            </div>

                            <div
                                class="relative rounded-md retro-dialogue-frame px-4 py-4 font-mono text-slate-100 ring-2 ring-game-accent/80"
                            >
                                <div
                                    class="absolute -top-3 left-3 rounded border-2 border-indigo-400 bg-indigo-950 px-2 py-0.5 text-[11px] font-bold tracking-wider text-indigo-100 uppercase"
                                >
                                    {{ previewDetails[activePreview].speaker }}
                                </div>

                                <div
                                    class="mt-1 space-y-2 text-xs leading-relaxed sm:text-sm"
                                >
                                    <p
                                        v-for="line in previewDetails[
                                            activePreview
                                        ].lines"
                                        :key="line"
                                    >
                                        {{ line }}
                                    </p>
                                </div>

                                <div
                                    class="mt-3 flex items-center justify-between border-t border-slate-800 pt-2 text-[11px] text-slate-400"
                                >
                                    <span>LOCALHOST NODE</span>
                                    <span class="animate-pulse text-amber-300">
                                        ▼ READY
                                    </span>
                                </div>
                            </div>

                            <div
                                class="mt-4 flex flex-wrap gap-2 font-mono text-[11px] text-slate-300"
                            >
                                <span
                                    class="rounded border border-slate-700 bg-slate-900 px-2 py-1"
                                >
                                    WASD / Arrows: Move
                                </span>
                                <span
                                    class="rounded border border-slate-700 bg-slate-900 px-2 py-1"
                                >
                                    E / Space / Enter: Interact
                                </span>
                                <span
                                    class="rounded border border-slate-700 bg-slate-900 px-2 py-1"
                                >
                                    Passkeys: Local Ready
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CONTACT SECTION -->
            <section id="contact" class="py-16 sm:py-20">
                <div
                    class="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 lg:grid-cols-12"
                >
                    <div class="space-y-4 lg:col-span-5">
                        <p
                            class="font-mono text-xs font-bold tracking-widest text-amber-300 uppercase"
                        >
                            Contact & Community
                        </p>
                        <h2
                            class="font-mono text-2xl font-bold text-white sm:text-3xl"
                        >
                            Send a Message to Localhost Town HQ
                        </h2>
                        <p class="text-sm leading-relaxed text-slate-300">
                            Have feedback on the pixel-art vertical slice, want
                            to propose a new programming challenge, or
                            interested in contributing tilesets and quests? Drop
                            us a note.
                        </p>

                        <div
                            class="space-y-3 pt-2 font-mono text-xs text-slate-300"
                        >
                            <div class="flex items-center gap-3">
                                <MapPin class="size-4 text-indigo-400" />
                                <span
                                    >Localhost Town, Stack Region
                                    (127.0.0.1)</span
                                >
                            </div>
                            <div class="flex items-center gap-3">
                                <Mail class="size-4 text-emerald-400" />
                                <span>hello@elephpant.dev</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <ShieldCheck class="size-4 text-amber-300" />
                                <span>
                                    Local-friendly accounts & WebAuthn Passkeys
                                    enabled
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-7">
                        <form
                            @submit.prevent="submitContact"
                            class="space-y-4 rounded-xl border-2 border-slate-800 bg-slate-900/70 p-6"
                        >
                            <div
                                v-if="contactSubmitted"
                                class="rounded-md border border-emerald-500/50 bg-emerald-950/60 px-4 py-3 font-mono text-xs text-emerald-200"
                            >
                                Message received at Localhost Town HQ! Thanks
                                for reaching out.
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-1.5">
                                    <label
                                        for="contact-name"
                                        class="block font-mono text-xs text-slate-300"
                                    >
                                        Developer Name
                                    </label>
                                    <input
                                        id="contact-name"
                                        v-model="contactName"
                                        type="text"
                                        required
                                        placeholder="Ada Lovelace"
                                        class="w-full rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white placeholder:text-slate-500 focus:border-indigo-400 focus:outline-none"
                                    />
                                </div>
                                <div class="space-y-1.5">
                                    <label
                                        for="contact-email"
                                        class="block font-mono text-xs text-slate-300"
                                    >
                                        Email
                                    </label>
                                    <input
                                        id="contact-email"
                                        v-model="contactEmail"
                                        type="email"
                                        required
                                        placeholder="ada@localhost.dev"
                                        class="w-full rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white placeholder:text-slate-500 focus:border-indigo-400 focus:outline-none"
                                    />
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label
                                    for="contact-message"
                                    class="block font-mono text-xs text-slate-300"
                                >
                                    Message
                                </label>
                                <textarea
                                    id="contact-message"
                                    v-model="contactMessage"
                                    rows="4"
                                    required
                                    placeholder="Share feedback, quest ideas, or bug reports..."
                                    class="w-full rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white placeholder:text-slate-500 focus:border-indigo-400 focus:outline-none"
                                />
                            </div>

                            <button
                                type="submit"
                                class="inline-flex cursor-pointer items-center gap-2 rounded-md border-2 border-indigo-400 bg-indigo-600 px-5 py-2 font-mono text-xs font-bold text-white transition hover:bg-indigo-500"
                            >
                                <Send class="size-3.5" />
                                Send Transmission
                            </button>
                        </form>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer
            class="border-t border-slate-800/80 bg-slate-950 py-8 font-mono text-xs text-slate-400"
        >
            <div
                class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-3 px-4 sm:flex-row sm:px-6"
            >
                <p>
                    <span class="font-bold text-white">elePHPant</span> — 16-bit
                    developer adventure built with Laravel, Vue 3 & Phaser
                    3.90.0.
                </p>
                <div class="flex items-center gap-4">
                    <a href="#home" class="hover:text-indigo-300">Home</a>
                    <a href="#about" class="hover:text-indigo-300">About</a>
                    <a href="#gameplay" class="hover:text-indigo-300">
                        Gameplay
                    </a>
                    <a href="#contact" class="hover:text-indigo-300">Contact</a>
                </div>
            </div>
        </footer>
    </div>
</template>
