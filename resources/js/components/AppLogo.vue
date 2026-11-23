<script setup lang="ts">
import { Gamepad2 } from '@lucide/vue';
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md border-2 border-indigo-400 bg-indigo-950 text-indigo-200 shadow-sm"
    >
        <Gamepad2 class="size-4" />
    </div>
    <div class="ml-1 grid flex-1 text-left font-mono text-sm">
        <span class="mb-0.5 truncate leading-tight font-bold">
            ele<span class="text-indigo-500 dark:text-indigo-400">PHP</span>ant
        </span>
    </div>
</template>
