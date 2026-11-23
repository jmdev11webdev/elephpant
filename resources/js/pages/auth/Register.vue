<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineProps<{
    passwordRules: string;
}>();

defineOptions({
    layout: {
        title: 'Begin Your Adventure',
        description:
            'Create your developer profile to spawn directly in Localhost Town',
    },
});
</script>

<template>
    <Head title="Register" />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-5"
    >
        <div class="grid gap-4">
            <div class="grid gap-2">
                <Label for="name" class="font-mono text-xs text-slate-200">
                    Developer Name
                </Label>
                <Input
                    id="name"
                    type="text"
                    required
                    v-focus
                    :tabindex="1"
                    autocomplete="name"
                    name="name"
                    placeholder="Ada Lovelace"
                    class="border-slate-700 bg-slate-950 text-white placeholder:text-slate-500 focus-visible:border-indigo-400"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email" class="font-mono text-xs text-slate-200">
                    Email address
                </Label>
                <Input
                    id="email"
                    type="email"
                    required
                    :tabindex="2"
                    autocomplete="email"
                    name="email"
                    placeholder="dev@localhost.test"
                    class="border-slate-700 bg-slate-950 text-white placeholder:text-slate-500 focus-visible:border-indigo-400"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password" class="font-mono text-xs text-slate-200">
                    Password
                </Label>
                <PasswordInput
                    id="password"
                    required
                    :tabindex="3"
                    autocomplete="new-password"
                    name="password"
                    placeholder="Password"
                    :passwordrules="passwordRules"
                    class="border-slate-700 bg-slate-950 text-white placeholder:text-slate-500 focus-visible:border-indigo-400"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label
                    for="password_confirmation"
                    class="font-mono text-xs text-slate-200"
                >
                    Confirm password
                </Label>
                <PasswordInput
                    id="password_confirmation"
                    required
                    :tabindex="4"
                    autocomplete="new-password"
                    name="password_confirmation"
                    placeholder="Confirm password"
                    :passwordrules="passwordRules"
                    class="border-slate-700 bg-slate-950 text-white placeholder:text-slate-500 focus-visible:border-indigo-400"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                class="mt-2 w-full border-2 border-indigo-400 bg-indigo-600 font-mono text-xs font-bold text-white hover:bg-indigo-500"
                :tabindex="5"
                :disabled="processing"
                data-test="register-user-button"
            >
                <Spinner v-if="processing" />
                Create Account & Play
            </Button>
        </div>

        <div class="text-center font-mono text-xs text-slate-400">
            Already have an account?
            <TextLink
                :href="login()"
                class="ml-1 text-indigo-300 underline underline-offset-4 hover:text-indigo-200"
                :tabindex="6"
            >
                Log in
            </TextLink>
        </div>
    </Form>
</template>
