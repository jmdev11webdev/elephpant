<script setup lang="ts">
import { usePasskeyRegister } from '@laravel/passkeys/vue';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const emit = defineEmits<{
    success: [];
}>();

const isLoopbackIp = computed(
    () =>
        typeof window !== 'undefined' &&
        (window.location.hostname === '127.0.0.1' ||
            window.location.hostname === '[::1]'),
);

const localhostHref = computed(() => {
    if (typeof window === 'undefined') {
        return '#';
    }

    const url = new URL(window.location.href);
    url.hostname = 'localhost';

    return url.toString();
});

const getDefaultPasskeyName = (): string => {
    const ua = navigator.userAgent;

    const browser = [
        { pattern: /Edg|Edge/, name: 'Edge' },
        { pattern: /OPR|Opera|OPiOS/, name: 'Opera' },
        { pattern: /Firefox|FxiOS/, name: 'Firefox' },
        { pattern: /Chrome|CriOS/, name: 'Chrome' },
        { pattern: /Safari/, name: 'Safari' },
    ].find(({ pattern }) => pattern.test(ua))?.name;

    const os = [
        { pattern: /iPhone/, name: 'iPhone' },
        { pattern: /iPad|Macintosh(?=.*Mobile)/, name: 'iPad' },
        { pattern: /Android/, name: 'Android' },
        { pattern: /Mac/, name: 'Mac' },
        { pattern: /Windows/, name: 'Windows' },
        { pattern: /Linux/, name: 'Linux' },
    ].find(({ pattern }) => pattern.test(ua))?.name;

    return [browser, os].filter(Boolean).join(' on ') || 'Local Device';
};

const name = ref(getDefaultPasskeyName());
const showForm = ref(false);

const { register, isLoading, error, isSupported } = usePasskeyRegister({
    onSuccess: () => {
        name.value = getDefaultPasskeyName();
        showForm.value = false;
        emit('success');
    },
});

const handleSubmit = async (event: Event): Promise<void> => {
    event.preventDefault();

    if (!name.value.trim()) {
        return;
    }

    await register(name.value);
};

const handleCancel = (): void => {
    showForm.value = false;
    name.value = getDefaultPasskeyName();
};
</script>

<template>
    <div v-if="!isSupported" class="text-sm text-muted-foreground">
        Passkeys are not supported in this browser.
    </div>

    <div
        v-else-if="isLoopbackIp"
        class="rounded-lg border border-amber-400/50 bg-amber-950/30 p-3 text-xs text-amber-200"
    >
        WebAuthn passkey registration on local development requires
        <strong>localhost</strong> instead of <code>127.0.0.1</code>.
        <a
            :href="localhostHref"
            class="ml-1 font-semibold underline underline-offset-4 hover:text-white"
        >
            Open on localhost →
        </a>
    </div>

    <Button v-else-if="!showForm" variant="outline" @click="showForm = true">
        Add passkey
    </Button>

    <form
        v-else
        @submit="handleSubmit"
        class="space-y-4 rounded-lg border border-border bg-muted/50 p-4"
    >
        <div class="grid gap-2">
            <Label for="passkey-name">Passkey name</Label>
            <Input
                id="passkey-name"
                type="text"
                v-model="name"
                placeholder="e.g., Chrome on Linux, MacBook Pro"
                class="mt-1 block w-full border-foreground/20"
                v-focus
            />
            <p class="text-xs text-muted-foreground">
                A name helps you identify this passkey later.
            </p>
        </div>

        <InputError v-if="error" :message="error" />

        <div class="flex gap-2">
            <Button type="submit" :disabled="isLoading || !name.trim()">
                {{ isLoading ? 'Registering...' : 'Register passkey' }}
            </Button>
            <Button type="button" variant="ghost" @click="handleCancel">
                Cancel
            </Button>
        </div>
    </form>
</template>
