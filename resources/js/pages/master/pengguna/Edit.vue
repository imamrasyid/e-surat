<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import MasterLayout from '@/layouts/master/Layout.vue';
import type { BreadcrumbItem, User } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Loader2, Send, Undo2 } from '@lucide/vue';
import { onMounted, ref } from 'vue';

const props = defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    data: User;
}>();

const form = useForm({
    name: props.data.name,
    username: props.data.username,
    password: '',
    password_confirmation: '',
    is_active: props.data.is_active,
});

const hasHistory = ref(false);

const goBack = () => {
    window.history.back();
};

onMounted(() => {
    hasHistory.value = window.history.length > 1;
});

const submit = () => {
    form.patch(route('ref.user.update', props.data.id));
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Perbarui Data Pengguna" />

        <MasterLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Perbarui" description="Edit data pengguna" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            type="text"
                            class="w-full"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="off"
                            v-model="form.name"
                            placeholder="Full name"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="username">Username</Label>
                        <Input
                            id="username"
                            type="text"
                            class="w-full"
                            required
                            :tabindex="2"
                            autocomplete="off"
                            v-model="form.username"
                            placeholder="Enter username"
                        />
                        <InputError :message="form.errors.username" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">Password</Label>
                        <Input
                            id="password"
                            type="password"
                            class="w-full"
                            :tabindex="3"
                            autocomplete="new-password"
                            v-model="form.password"
                            placeholder="Password"
                        />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">Confirm password</Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            class="w-full"
                            :tabindex="4"
                            autocomplete="new-password"
                            v-model="form.password_confirmation"
                            placeholder="Confirm password"
                        />
                        <InputError :message="form.errors.password_confirmation" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="status" class="flex items-center space-x-3">
                            <Checkbox id="status" v-model="form.is_active" />
                            <span>Aktifkan</span>
                        </Label>
                        <InputError :message="form.errors.is_active" />
                    </div>

                    <div class="mt-12 flex flex-col gap-4 lg:flex-row lg:justify-end">
                        <Button v-if="hasHistory" type="button" variant="outline" class="w-full cursor-pointer lg:w-40" @click="goBack">
                            <Undo2 class="size-4" />
                            Batal
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="w-full cursor-pointer lg:w-50">
                            <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                            <Send v-else class="size-4" />
                            Simpan Pembaruan
                        </Button>
                    </div>
                </form>
            </div>
        </MasterLayout>
    </AppLayout>
</template>
