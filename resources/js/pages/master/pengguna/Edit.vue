<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useToast } from '@/components/ui/toast/use-toast';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';
import MasterLayout from '@/layouts/master/Layout.vue';
import { Loader2 } from 'lucide-vue-next';

const { toast } = useToast();

const status = [
    {
        value: 1,
        name: 'Aktif',
    },
    {
        value: 0,
        name: 'Non Aktif',
    },
];

const props = defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    data: any;
}>();

const form = useForm({
    name: props.data.name,
    email: props.data.email,
    password: '',
    password_confirmation: '',
    is_active: props.data.is_active,
});

const submit = () => {
    form.patch(route('ref.user.update', props.data.id), {
        onSuccess: () => {
            toast({
                title: 'Success! Data submitted.',
                description: 'Data berhasil tersimpan',
                variant: 'success',
            });
        },
        onError: (errors: any) => {
            if (errors.confirm) {
                toast({
                    title: 'Uh oh! Something went wrong.',
                    description: errors.confirm,
                    variant: 'destructive',
                });
            } else {
                toast({
                    title: 'Uh oh! Something went wrong.',
                    description: 'Sepertinya data belum lengkap',
                    variant: 'warning',
                });
            }
        },
    });
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
                            class="mt-1 block w-full sm:w-[350px]"
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
                            class="mt-1 block w-full sm:w-[350px]"
                            required
                            :tabindex="2"
                            autocomplete="off"
                            v-model="form.email"
                            placeholder="Enter username"
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">Password</Label>
                        <Input
                            id="password"
                            type="password"
                            class="mt-1 block w-full sm:w-[350px]"
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
                            class="mt-1 block w-full sm:w-[350px]"
                            :tabindex="4"
                            autocomplete="new-password"
                            v-model="form.password_confirmation"
                            placeholder="Confirm password"
                        />
                        <InputError :message="form.errors.password_confirmation" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="status">Status</Label>
                        <Select id="status" v-model="form.is_active">
                            <SelectTrigger class="mt-1 w-full sm:w-[350px]">
                                <SelectValue placeholder="Select a status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup class="w-full sm:w-[350px]">
                                    <SelectItem v-for="item in status" :key="item.value" :value="item.value">{{ item.name }}</SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                        <InputError class="mt-1" :message="form.errors.is_active" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">
                            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            Simpan Pembaruan
                        </Button>

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
        </MasterLayout>
    </AppLayout>
</template>
