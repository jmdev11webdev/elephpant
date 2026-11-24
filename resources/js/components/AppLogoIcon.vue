<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <img
        src="/images/elephpant-logo.png"
        alt="elePHPant logo"
        :class="['object-contain [image-rendering:pixelated]', className]"
        v-bind="$attrs"
    />
</template>
