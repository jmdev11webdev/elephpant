<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex shrink-0 items-center justify-center group-data-[collapsible=icon]:size-8"
    >
        <AppLogoIcon
            class="h-9 w-auto max-w-none group-data-[collapsible=icon]:h-6"
        />
    </div>
    <div
        class="ml-1.5 grid flex-1 text-left font-mono text-lg tracking-tight group-data-[collapsible=icon]:hidden"
    >
        <span class="truncate leading-none font-extrabold">
            ele<span class="text-indigo-500 dark:text-indigo-400">PHP</span>ant
        </span>
    </div>
</template>
