<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasskeyVerify from '@/components/PasskeyVerify.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Return to Localhost Town',
        description:
            'Sign in with a passkey or your developer credentials to continue your adventure',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <Head title="Log in" />

    <div
        v-if="status"
        class="mb-4 rounded-md border border-emerald-500/40 bg-emerald-950/60 px-3 py-2 text-center font-mono text-xs font-medium text-emerald-200"
    >
        {{ status }}
    </div>

    <PasskeyVerify />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-5"
    >
        <div class="grid gap-5">
            <div class="grid gap-2">
                <Label for="email" class="font-mono text-xs text-slate-200">
                    Email address
                </Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    v-focus
                    :tabindex="1"
                    autocomplete="email"
                    placeholder="dev@localhost.test"
                    class="border-slate-700 bg-slate-950 text-white placeholder:text-slate-500 focus-visible:border-indigo-400"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label
                        for="password"
                        class="font-mono text-xs text-slate-200"
                    >
                        Password
                    </Label>
                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="font-mono text-xs text-indigo-300 hover:text-indigo-200"
                        :tabindex="5"
                    >
                        Forgot password?
                    </TextLink>
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="Password"
                    class="border-slate-700 bg-slate-950 text-white placeholder:text-slate-500 focus-visible:border-indigo-400"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <Label
                    for="remember"
                    class="flex items-center space-x-3 text-slate-300"
                >
                    <Checkbox
                        id="remember"
                        name="remember"
                        :tabindex="3"
                        class="border-slate-600 data-[state=checked]:border-indigo-400 data-[state=checked]:bg-indigo-600"
                    />
                    <span class="text-xs">Remember me on this device</span>
                </Label>
            </div>

            <Button
                type="submit"
                class="mt-2 w-full border-2 border-indigo-400 bg-indigo-600 font-mono text-xs font-bold text-white hover:bg-indigo-500"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                Log in to Game
            </Button>
        </div>

        <div class="text-center font-mono text-xs text-slate-400">
            New to the Stack Region?
            <TextLink
                :href="register()"
                :tabindex="5"
                class="ml-1 text-indigo-300 hover:text-indigo-200"
            >
                Create account
            </TextLink>
        </div>
    </Form>
</template>
