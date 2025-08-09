<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Loader2, Send } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Input Surat Masuk',
        href: '/app/compose',
    },
];

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});
</script>

<template>
    <Head title="Input Surat Masuk" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6">
            <form>
                <div class="mb-6 flex items-center justify-between">
                    <Heading class="!mb-0" title="Input Baru" description="Data surat masuk" />
                    <Transition
                        enter-active-class="transition duration-200 ease-in-out"
                        enter-from-class="opacity-40 translate-x-4"
                        enter-to-class="opacity-100 translate-x-0"
                        leave-active-class="transition duration-200 ease-in-out"
                        leave-from-class="opacity-100 translate-x-0"
                        leave-to-class="opacity-40 translate-x-4"
                    >
                        <Button v-show="form.isDirty" :disabled="form.processing">
                            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            <Send v-else class="h-4 w-4" />
                            Kirim Surat
                        </Button>
                    </Transition>
                </div>
                <div class="flex flex-col space-y-6 lg:flex-row lg:space-x-6">
                    <div class="w-full max-w-2xl lg:w-1/2">
                        <Card class="w-full">
                            <CardContent>
                                <div class="space-y-6">
                                    <div class="grid gap-2">
                                        <Label for="nomor">Nomor Surat</Label>
                                        <Input
                                            id="nomor"
                                            type="text"
                                            required
                                            autofocus
                                            :tabindex="1"
                                            autocomplete="off"
                                            v-model="form.name"
                                            placeholder="Full name"
                                        />
                                        <InputError :message="form.errors.name" />
                                    </div>
                                    <div class="space-y-6 sm:flex sm:space-y-0 sm:space-x-6">
                                        <div class="grid gap-2">
                                            <Label for="nomor">Tanggal Surat</Label>
                                            <Input
                                                id="nomor"
                                                type="text"
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
                                            <Label for="nomor">Tanggal Diterima</Label>
                                            <Input
                                                id="nomor"
                                                type="text"
                                                required
                                                autofocus
                                                :tabindex="1"
                                                autocomplete="off"
                                                v-model="form.name"
                                                placeholder="Full name"
                                            />
                                            <InputError :message="form.errors.name" />
                                        </div>
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="nomor">Pengirim</Label>
                                        <Input
                                            id="nomor"
                                            type="text"
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
                                        <Label for="nomor">Perihal</Label>
                                        <Input
                                            id="nomor"
                                            type="text"
                                            required
                                            autofocus
                                            :tabindex="1"
                                            autocomplete="off"
                                            v-model="form.name"
                                            placeholder="Full name"
                                        />
                                        <InputError :message="form.errors.name" />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                    <div class="lg:max-w-2xl: flex flex-1 flex-col space-y-6">
                        <Card class="w-full">
                            <CardContent></CardContent>
                        </Card>
                        <Card class="w-full">
                            <CardHeader>
                                <CardTitle>Lampiran</CardTitle>
                            </CardHeader>
                            <CardContent></CardContent>
                        </Card>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
