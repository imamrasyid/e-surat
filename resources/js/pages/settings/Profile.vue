<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import TelegramLinkModal from '@/components/TelegramLinkModal.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type User } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
    telegramConnected: boolean;
    telegramEnabled: boolean;
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const page = usePage();
const user = page.props.auth.user as User;

const form = useForm({
    name: user.name,
    username: user.username,
    email: user.email,
});

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};

const telegramUnlinkForm = useForm({});
const showTelegramModal = ref(false);

const disconnectTelegram = () => {
    telegramUnlinkForm.delete(route('telegram.unlink'), { preserveScroll: true });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Profile information" description="Your name, username, and setup email" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" class="mt-1 block w-full" v-model="form.name" autocomplete="off" placeholder="Enter your name" />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="username">Username</Label>
                        <Input
                            id="username"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.username"
                            autocomplete="off"
                            placeholder="Enter your username"
                        />
                        <InputError class="mt-2" :message="form.errors.username" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            autocomplete="off"
                            placeholder="Enter your email"
                            :readonly="mustVerifyEmail && user.email && !user.email_verified_at"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && user.email && !user.email_verified_at">
                        <p class="text-muted-foreground -mt-4 text-sm">
                            Your email address is unverified.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                            A new verification link has been sent to your email address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Save Profile</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                        </Transition>
                    </div>
                </form>
            </div>

            <div v-if="telegramEnabled" class="flex flex-col space-y-6">
                <HeadingSmall title="Notifikasi Telegram" description="Hubungkan akun Telegram" />

                <div class="space-y-3">
                    <p class="text-muted-foreground text-sm" data-testid="telegram-status">
                        <template v-if="telegramConnected">
                            Akun Telegram sudah tersambung. Pemberitahuan dikirim otomatis ke chat Telegram.
                        </template>
                        <template v-else>
                            Akun Telegram belum tersambung untuk menerima pemberitahuan surat masuk, disposisi, dan nota dinas.
                        </template>
                    </p>

                    <div class="flex flex-wrap items-center gap-3">
                        <Button v-if="!telegramConnected" type="button" data-testid="telegram-link" @click="showTelegramModal = true">
                            Hubungkan Telegram
                        </Button>

                        <Button
                            v-else
                            type="button"
                            variant="destructive"
                            :disabled="telegramUnlinkForm.processing"
                            data-testid="telegram-unlink"
                            @click="disconnectTelegram"
                        >
                            Putuskan Telegram
                        </Button>
                    </div>
                </div>

                <TelegramLinkModal v-model:isOpen="showTelegramModal" />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
