<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import MasterLayout from '@/layouts/master/Layout.vue';
import type { BreadcrumbItem, Followup } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Loader2, Save, Undo2 } from '@lucide/vue';
import { onMounted, ref } from 'vue';

const props = defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    data: Followup;
}>();

const form = useForm({
    name: props.data.name,
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
    form.patch(route('ref.followup.update', props.data.id));
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Perbarui Tindak Lanjut" />

        <MasterLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Perbarui" description="Edit referensi tindak lanjut" />

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

                    <div class="grid gap-2">
                        <div class="flex items-start gap-3">
                            <Checkbox id="status" v-model="form.is_active" />
                            <div class="grid gap-0.5">
                                <Label for="status">Status Aktif Tindak Lanjut</Label>
                                <p class="text-muted-foreground text-sm">Centang untuk mengaktifkan tindak lanjut.</p>
                            </div>
                        </div>
                        <InputError class="mt-1" :message="form.errors.is_active" />
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
