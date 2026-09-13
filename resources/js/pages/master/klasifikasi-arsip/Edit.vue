<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useToast } from '@/components/ui/toast/use-toast';
import AppLayout from '@/layouts/AppLayout.vue';
import MasterLayout from '@/layouts/master/Layout.vue';
import type { BreadcrumbItem, RefKdKlasifikasi } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Loader2 } from 'lucide-vue-next';

const { toast } = useToast();

const props = defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    data: RefKdKlasifikasi;
}>();

const form = useForm({
    kode: props.data.kode,
    nama: props.data.nama,
    is_active: props.data.is_active,
});

const submit = () => {
    form.patch(route('ref.klasifikasi.update', props.data.id), {
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
        <Head title="Perbarui Klasifikasi Arsip" />

        <MasterLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Perbarui" description="Edit kode klasifikasi arsip" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="kode">Kode</Label>
                        <Input id="kode" type="number" class="mt-1 block w-full sm:w-[350px]" v-model="form.kode" readonly />
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

                    <div class="grid gap-2">
                        <Label for="status" class="flex items-center space-x-3">
                            <Checkbox id="status" v-model="form.is_active" />
                            <span>Aktifkan</span>
                        </Label>
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
