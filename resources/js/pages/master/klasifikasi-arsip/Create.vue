<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import MasterLayout from '@/layouts/master/Layout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Loader2 } from '@lucide/vue';

defineProps<{
    breadcrumbItems: BreadcrumbItem[];
}>();

const form = useForm({
    kode: '',
    nama: '',
});

const submit = () => {
    form.post(route('ref.klasifikasi.store'));
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Tambah Kode Klasifikasi Arsip" />

        <MasterLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Tambah Baru" description="Buat kode klasifikasi arsip" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="kode">Kode</Label>
                        <Input
                            id="kode"
                            type="number"
                            class="mt-1 block w-full sm:w-[350px]"
                            v-model="form.kode"
                            autocomplete="off"
                            placeholder="3 digit angka"
                        />
                        <InputError class="mt-1" :message="form.errors.kode" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="nama">Nama</Label>
                        <Input
                            id="nama"
                            type="text"
                            class="mt-1 block w-full sm:w-[350px]"
                            v-model="form.nama"
                            autocomplete="off"
                            placeholder="Masukkan nama kode klasifikasi"
                        />
                        <InputError class="mt-1" :message="form.errors.nama" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">
                            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            Simpan
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
