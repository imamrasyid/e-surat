<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';
import type { AppPageProps } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ArrowRight, LoaderCircle, MailWarning } from '@lucide/vue';
import { computed } from 'vue';

defineProps<{
    status?: string;
}>();

const page = usePage<AppPageProps>();
const user = computed(() => page.props.auth?.user);
const hasEmail = computed(() => Boolean(user.value?.email));

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};
</script>

<template>
    <AuthLayout
        :title="hasEmail ? 'Verify email' : 'Email not registered'"
        :description="
            hasEmail
                ? 'Please verify your email address by clicking on the link we just emailed to you.'
                : 'Please complete your email on the profile page before proceeding with verification.'
        "
    >
        <Head title="Email verification" />

        <div v-if="hasEmail">
            <div v-if="status === 'verification-link-sent'" class="mb-4 text-center text-sm font-medium text-green-600">
                A new verification link has been sent to the email address you provided during registration.
            </div>

            <form @submit.prevent="submit" class="space-y-6 text-center">
                <Button :disabled="form.processing" variant="secondary">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Resend verification email
                </Button>

                <TextLink :href="route('logout')" method="post" as="button" class="mx-auto block text-sm"> Log out </TextLink>
            </form>
        </div>

        <div v-else class="space-y-6 text-center">
            <div
                class="flex items-start rounded-lg border border-amber-200 bg-amber-50 p-3 text-left text-sm text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/50 dark:text-amber-300"
            >
                <MailWarning class="mr-2.5 h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" />
                <div>
                    <p class="font-medium">Email not registered</p>
                    <p class="mt-0.5 text-xs text-amber-700 dark:text-amber-400">Your account does not have an email address registered.</p>
                </div>
            </div>

            <Link :href="route('profile.edit')" :class="buttonVariants()" class="w-full">
                Complete email on profile
                <ArrowRight class="h-4 w-4" />
            </Link>

            <TextLink :href="route('logout')" method="post" as="button" class="mx-auto block text-sm"> Log out </TextLink>
        </div>
    </AuthLayout>
</template>
