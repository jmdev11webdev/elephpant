<script setup lang="ts">
import type { UrlMethodPair } from '@inertiajs/core';
import { router } from '@inertiajs/vue3';
import { usePasskeyVerify } from '@laravel/passkeys/vue';
import { KeyRound } from '@lucide/vue';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Spinner } from '@/components/ui/spinner';

type Props = {
    routes?: {
        options: UrlMethodPair;
        submit: UrlMethodPair;
    };
    label?: string;
    loadingLabel?: string;
    separator?: string;
};

const props = defineProps<Props>();

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

const { verify, isLoading, error, isSupported } = usePasskeyVerify({
    ...(props.routes
        ? {
              routes: {
                  options: props.routes.options.url,
                  submit: props.routes.submit.url,
              },
          }
        : {}),
    onSuccess: (response) => {
        router.visit(response.redirect ?? '/dashboard');
    },
});
</script>

<template>
    <div v-if="isSupported">
        <div
            v-if="isLoopbackIp"
            class="mb-4 rounded-md border border-amber-400/40 bg-amber-950/50 px-3 py-2.5 font-mono text-xs text-amber-200"
        >
            Local passkeys require <span class="font-bold">localhost</span>
            instead of an IP address.
            <a
                :href="localhostHref"
                class="ml-1 font-bold text-amber-100 underline underline-offset-4 hover:text-white"
            >
                Switch to localhost →
            </a>
        </div>

        <div class="grid gap-2">
            <Button
                type="button"
                variant="outline"
                class="w-full border-indigo-400/60 bg-indigo-950/70 font-mono text-xs font-semibold text-indigo-100 hover:border-indigo-300 hover:bg-indigo-900/80 hover:text-white"
                @click="verify"
                :disabled="isLoading || isLoopbackIp"
            >
                <Spinner v-if="isLoading" />
                <KeyRound v-else class="size-4 text-amber-300" />
                {{
                    isLoading
                        ? (props.loadingLabel ?? 'Authenticating...')
                        : (props.label ?? 'Sign in with a passkey')
                }}
            </Button>

            <div v-if="error" class="text-center">
                <InputError :message="error" />
            </div>
        </div>

        <div class="relative my-5">
            <div class="absolute inset-0 flex items-center">
                <Separator class="w-full bg-slate-700" />
            </div>
            <div
                class="relative flex justify-center font-mono text-[11px] uppercase"
            >
                <span class="bg-slate-900 px-2.5 text-slate-400">
                    {{ props.separator ?? 'Or continue with email' }}
                </span>
            </div>
        </div>
    </div>
</template>
