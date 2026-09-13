<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useToast } from '@/components/ui/toast/use-toast';
import AppLayout from '@/layouts/AppLayout.vue';
import MasterLayout from '@/layouts/master/Layout.vue';
import type { BreadcrumbItem, Position } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Loader2 } from 'lucide-vue-next';

const { toast } = useToast();

defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    positions: Position[];
}>();

const form = useForm({
    name: '',
    parent_id: '',
});

const submit = () => {
    form.post(route('ref.position.store'), {
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
        <Head title="Tambah Referensi Jabatan" />

        <MasterLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Tambah Baru" description="Buat referensi jabatan" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="nama">Nama Jabatan</Label>
                        <Input
                            id="nama"
                            type="text"
                            class="mt-1 block w-full sm:w-[350px]"
                            v-model="form.name"
                            autocomplete="off"
                            placeholder="Masukkan nama jabatan"
                        />
                        <InputError class="mt-1" :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="parent">Parent ID</Label>
                        <Select id="parent" v-model="form.parent_id">
                            <SelectTrigger class="mt-1 w-full sm:w-[350px]">
                                <SelectValue placeholder="Select a parent" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup class="w-full sm:w-[350px]">
                                    <SelectItem v-for="item in positions" :key="item.id" :value="item.id">{{ item.name }}</SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                        <InputError class="mt-1" :message="form.errors.parent_id" />
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
