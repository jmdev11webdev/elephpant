<script setup lang="ts">
import { Globe } from '@lucide/vue';
import {
    siAlpinedotjs,
    siComposer,
    siGithub,
    siInertia,
    siLaravel,
    siLivewire,
    siPackagist,
    siPhp,
    siReact,
    siSvelte,
    siTailwindcss,
    siVuedotjs,
} from 'simple-icons';
import type { SimpleIcon } from 'simple-icons';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        brand?: string;
        url?: string;
        sizeClass?: string;
    }>(),
    {
        sizeClass: 'size-5',
    },
);

const BRAND_SLUG_MAP: Record<string, SimpleIcon> = {
    php: siPhp,
    composer: siComposer,
    packagist: siPackagist,
    laravel: siLaravel,
    livewire: siLivewire,
    alpine: siAlpinedotjs,
    alpinejs: siAlpinedotjs,
    tailwind: siTailwindcss,
    tailwindcss: siTailwindcss,
    inertia: siInertia,
    inertiajs: siInertia,
    vue: siVuedotjs,
    vuejs: siVuedotjs,
    react: siReact,
    svelte: siSvelte,
    github: siGithub,
};

const HOSTNAME_MAP: Record<string, SimpleIcon> = {
    'php.net': siPhp,
    'phpnews.net': siPhp,
    'phptherightway.com': siPhp,
    'php.watch': siPhp,
    'getcomposer.org': siComposer,
    'packagist.org': siPackagist,
    'laravel.com': siLaravel,
    'laravel-news.com': siLaravel,
    'laraveldaily.com': siLaravel,
    'livewire.laravel.com': siLivewire,
    'alpinejs.dev': siAlpinedotjs,
    'tailwindcss.com': siTailwindcss,
    'inertiajs.com': siInertia,
    'vuejs.org': siVuedotjs,
    'react.dev': siReact,
    'svelte.dev': siSvelte,
    'github.com': siGithub,
};

function extractHostname(rawUrl: string): string {
    try {
        return new URL(rawUrl).hostname.replace(/^www\./, '').toLowerCase();
    } catch {
        return '';
    }
}

function relativeLuminance(hex: string): number {
    const clean = hex.replace('#', '');
    const r = Number.parseInt(clean.slice(0, 2), 16) / 255;
    const g = Number.parseInt(clean.slice(2, 4), 16) / 255;
    const b = Number.parseInt(clean.slice(4, 6), 16) / 255;

    const toLinear = (channel: number): number =>
        channel <= 0.03928
            ? channel / 12.92
            : Math.pow((channel + 0.055) / 1.055, 2.4);

    return 0.2126 * toLinear(r) + 0.7152 * toLinear(g) + 0.0722 * toLinear(b);
}

const icon = computed<SimpleIcon | null>(() => {
    if (props.brand) {
        const byBrand = BRAND_SLUG_MAP[props.brand.toLowerCase()];
        if (byBrand) {
            return byBrand;
        }
    }

    if (props.url) {
        const host = extractHostname(props.url);
        if (host && HOSTNAME_MAP[host]) {
            return HOSTNAME_MAP[host];
        }
    }

    return null;
});

const needsLightBadge = computed<boolean>(() => {
    if (!icon.value) {
        return false;
    }

    return relativeLuminance(icon.value.hex) < 0.16;
});
</script>

<template>
    <span
        data-testid="brand-icon-badge"
        class="inline-flex size-9 shrink-0 items-center justify-center rounded-lg border transition"
        :class="
            needsLightBadge
                ? 'border-slate-300/80 bg-slate-100 shadow-xs'
                : 'border-border bg-muted/70 dark:bg-slate-900/90'
        "
    >
        <svg
            v-if="icon"
            viewBox="0 0 24 24"
            role="img"
            :aria-label="icon.title"
            :class="sizeClass"
            :fill="`#${icon.hex}`"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path :d="icon.path" />
        </svg>
        <Globe v-else :class="[sizeClass, 'text-indigo-400']" />
    </span>
</template>
