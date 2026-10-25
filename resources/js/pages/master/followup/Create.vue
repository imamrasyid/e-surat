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
import { Loader2, Save, Undo2 } from '@lucide/vue';
import { onMounted, ref } from 'vue';

defineProps<{
    breadcrumbItems: BreadcrumbItem[];
}>();

const form = useForm({
    name: '',
});

const hasHistory = ref(false);

const goBack = () => {
    window.history.back();
};

onMounted(() => {
    hasHistory.value = window.history.length > 1;
});

const submit = () => {
    form.post(route('ref.followup.store'));
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Tambah Baru Tindak Lanjut" />

        <MasterLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Tambah Baru" description="Buat baru referensi tindak lanjut" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="nama">Nama</Label>
                        <Input
                            id="nama"
                            type="text"
                            class="w-full"
                            v-model="form.name"
                            autocomplete="off"
                            placeholder="Masukkan nama tindak lanjut"
                        />
                        <InputError class="mt-1" :message="form.errors.name" />
                    </div>

                    <div class="flex flex-col gap-4 lg:flex-row">
                        <Button type="submit" :disabled="form.processing">
                            <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                            <Save v-else class="size-4" />
                            Simpan
                        </Button>
                        <Button v-if="hasHistory" type="button" variant="outline" @click="goBack">
                            <Undo2 class="size-4" />
                            Batal
                        </Button>
                    </div>
                </form>
            </div>
        </MasterLayout>
    </AppLayout>
</template>
