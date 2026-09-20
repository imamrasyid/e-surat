<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { useToast } from '@/components/ui/toast';
import type { Position } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { Loader2 } from '@lucide/vue';

const { toast } = useToast();

const props = defineProps<{
    user_id: number;
    specialDispositions: Position[];
    positions: Position[];
}>();

// form inertia
const form = useForm({
    position_ids: [] as number[],
});

// inisialisasi: tandai yang sudah punya hak khusus
form.position_ids = props.specialDispositions.map((d) => d.id);

const submit = () => {
    form.post(route('ref.user.updateDisposition', props.user_id), {
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
    <form @submit.prevent="submit" class="flex flex-col space-y-6">
        <HeadingSmall title="Disposisi Khusus" description="Referensi jabatan untuk disposisi khusus" />

        <Card>
            <CardContent class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                <div v-for="(item, index) in positions" :key="index" class="flex space-x-2">
                    <input :id="'position' + index" type="checkbox" class="cursor-pointer" :value="item.id" v-model="form.position_ids" />
                    <label
                        :for="'position' + index"
                        class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                    >
                        {{ item.name }}
                    </label>
                </div>
            </CardContent>
        </Card>

        <Button type="submit" :disabled="form.processing">
            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
            Simpan
        </Button>
    </form>
</template>
