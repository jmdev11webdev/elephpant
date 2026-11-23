<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import type { ShowDialoguePayload } from '@/game/events';

const props = defineProps<{
    dialogue: ShowDialoguePayload;
}>();

const emit = defineEmits<{
    close: [];
}>();

const currentIndex = ref(0);
let openedAt = 0;

const totalLines = computed(() => Math.max(props.dialogue.lines.length, 1));
const currentLine = computed(
    () => props.dialogue.lines[currentIndex.value] ?? '',
);
const isLastLine = computed(() => currentIndex.value >= totalLines.value - 1);

const badgeLabel = computed(() => {
    if (props.dialogue.speaker) {
        return props.dialogue.speaker;
    }

    if (props.dialogue.kind === 'terminal') {
        return 'Terminal';
    }

    if (props.dialogue.kind === 'sign') {
        return 'Sign';
    }

    return 'Notice';
});

const badgeAccentClass = computed(() => {
    switch (props.dialogue.kind) {
        case 'terminal':
            return 'border-cyan-400 bg-cyan-950 text-cyan-200';
        case 'sign':
            return 'border-amber-400 bg-amber-950 text-amber-200';
        default:
            return 'border-indigo-400 bg-indigo-950 text-indigo-100';
    }
});

watch(
    () => props.dialogue,
    () => {
        currentIndex.value = 0;
        openedAt = performance.now();
    },
);

function advance(): void {
    if (isLastLine.value) {
        emit('close');
        return;
    }

    currentIndex.value += 1;
}

function dismiss(): void {
    emit('close');
}

function handleKeydown(event: KeyboardEvent): void {
    if (event.repeat) {
        return;
    }

    if (event.key === 'Escape') {
        event.preventDefault();
        dismiss();
        return;
    }

    if (
        event.key === ' ' ||
        event.key === 'Enter' ||
        event.key === 'e' ||
        event.key === 'E'
    ) {
        event.preventDefault();

        if (performance.now() - openedAt < 120) {
            return;
        }

        advance();
    }
}

onMounted(() => {
    openedAt = performance.now();
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
        aria-label="Game dialogue"
        data-testid="dialogue-box"
        class="pointer-events-auto absolute inset-x-3 bottom-3 z-20 sm:inset-x-5 sm:bottom-4"
        @click="advance"
    >
        <div
            class="relative cursor-pointer rounded-md retro-dialogue-frame px-4 py-3 font-mono text-slate-100 ring-2 ring-game-accent/80 select-none sm:px-5 sm:py-4"
        >
            <div
                class="absolute -top-3.5 left-3 rounded border-2 px-2.5 py-0.5 text-[11px] font-bold tracking-wider uppercase shadow sm:text-xs"
                :class="badgeAccentClass"
            >
                {{ badgeLabel }}
            </div>

            <p
                class="mt-1 min-h-[2.75rem] text-xs leading-relaxed text-slate-100 sm:min-h-[3.25rem] sm:text-sm"
            >
                {{ currentLine }}
            </p>

            <div
                class="mt-2 flex items-center justify-between border-t border-slate-800 pt-1.5 text-[10px] text-slate-400 sm:text-xs"
            >
                <span>{{ currentIndex + 1 }} / {{ totalLines }}</span>
                <span class="flex items-center gap-2 text-indigo-300">
                    <span>
                        {{
                            isLastLine
                                ? 'Space / Enter / Click to close'
                                : 'Space / Enter / Click to continue'
                        }}
                    </span>
                    <span class="animate-pulse font-bold text-amber-300">
                        {{ isLastLine ? '■' : '▼' }}
                    </span>
                </span>
            </div>
        </div>
    </div>
</template>
