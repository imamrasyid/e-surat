<script setup lang="ts">
import SelectCombobox from '@/components/forms/SelectCombobox.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import MasterLayout from '@/layouts/master/Layout.vue';
import type { BreadcrumbItem, Position, User } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Loader2, Save, Undo2 } from '@lucide/vue';
import { onMounted, ref, watch } from 'vue';
import SpecialDisposition from './partials/SpecialDisposition.vue';

const props = defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    data: User;
    currentPosition: Position;
    positions: Position[];
    specialDispositions: Position[];
}>();

const form = useForm<{ positionId: number | null }>({
    positionId: null,
});

const selectedPosition = ref<{ id: number; name: string } | null>(null);

watch(selectedPosition, (position) => {
    form.positionId = position?.id ?? null;
});

const hasHistory = ref(false);

const goBack = () => {
    window.history.back();
};

onMounted(() => {
    hasHistory.value = window.history.length > 1;
});

const submit = () => {
    form.post(route('ref.user.updatePosition', props.data.id));
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Referensi Jabatan Pengguna" />

        <MasterLayout class="mb-10">
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Jabatan Pengguna" description="Referensi jabatan pengguna" />

                <Card class="dark:bg-input/30 shadow-none">
                    <CardHeader class="gap-0.5">
                        <CardTitle class="text-sm font-medium">{{ data.name }}</CardTitle>
                        <CardDescription class="text-xs">
                            Jabatan saat ini: <span class="font-medium">{{ currentPosition?.name ?? '-' }}</span>
                        </CardDescription>
                    </CardHeader>
                </Card>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="position">Atur Jabatan Baru</Label>
                        <SelectCombobox
                            id="position"
                            v-model="selectedPosition"
                            :options="positions"
                            placeholder="Pilih jabatan"
                            search-placeholder="Cari jabatan..."
                        />
                        <InputError :message="form.errors.positionId" />
                    </div>

                    <div class="flex flex-col gap-4 lg:flex-row">
                        <Button type="submit" class="w-full cursor-pointer lg:w-50" :disabled="!selectedPosition || form.processing">
                            <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                            <Save v-else class="size-4" />
                            Simpan Jabatan
                        </Button>
                        <Button v-if="hasHistory" type="button" variant="outline" @click="goBack">
                            <Undo2 class="size-4" />
                            Batal
                        </Button>
                    </div>
                </form>
            </div>

            <Separator class="my-6" />

            <SpecialDisposition :user_id="data.id" :special-dispositions="specialDispositions" :positions="positions" />
        </MasterLayout>
    </AppLayout>
</template>
