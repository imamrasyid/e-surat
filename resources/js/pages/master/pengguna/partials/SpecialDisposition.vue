<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import MultiSelectCombobox from '@/components/forms/MultiSelectCombobox.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import type { Position } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { Loader2, Save } from '@lucide/vue';
import { ref, watch } from 'vue';

const props = defineProps<{
    user_id: number;
    specialDispositions: Position[];
    positions: Position[];
}>();

const form = useForm<{ position_ids: number[] }>({
    position_ids: [],
});

const selectedPositions = ref<{ id: number; name: string }[]>(props.specialDispositions.map(({ id, name }) => ({ id, name })));

watch(
    selectedPositions,
    (items) => {
        form.position_ids = items.map(({ id }) => id);
    },
    { deep: true, immediate: true },
);

const submit = () => {
    form.post(route('ref.user.updateDisposition', props.user_id));
};
</script>

<template>
    <form @submit.prevent="submit" class="flex flex-col space-y-6">
        <HeadingSmall title="Disposisi Khusus" description="Referensi jabatan untuk disposisi khusus" />

        <div class="grid gap-2">
            <Label for="special-disposition">Jabatan Disposisi Khusus</Label>
            <MultiSelectCombobox
                id="special-disposition"
                v-model="selectedPositions"
                :options="positions"
                placeholder="Pilih jabatan disposisi khusus"
                search-placeholder="Cari jabatan..."
            />
            <InputError :message="form.errors.position_ids" />
        </div>

        <Button type="submit" :disabled="form.processing" class="w-full cursor-pointer lg:w-50">
            <Loader2 v-if="form.processing" class="size-4 animate-spin" />
            <Save v-else class="size-4" />
            Simpan
        </Button>
    </form>
</template>
