<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { useToast } from '@/components/ui/toast/use-toast';
import AppLayout from '@/layouts/AppLayout.vue';
import MasterLayout from '@/layouts/master/Layout.vue';
import type { BreadcrumbItem, Position, User } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Loader2 } from '@lucide/vue';
import SpecialDisposition from './partials/SpecialDisposition.vue';

const { toast } = useToast();

const props = defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    data: User;
    currentPosition: Position;
    positions: Position[];
    specialDispositions: Position[];
}>();

const form = useForm({
    positionId: '',
});

const submit = () => {
    form.post(route('ref.user.updatePosition', props.data.id), {
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
        <Head title="Referensi Jabatan Pengguna" />

        <MasterLayout class="mb-10">
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Jabatan Pengguna" description="Referensi jabatan pengguna" />

                <Card class="sm:w-[350px]">
                    <CardHeader>
                        <CardTitle>{{ data.name }}</CardTitle>
                        <CardDescription>
                            Jabatan saat ini: <strong>{{ currentPosition?.name ?? '-' }}</strong>
                        </CardDescription>
                    </CardHeader>
                </Card>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="position">Atur Jabatan Baru</Label>
                        <Select id="position" v-model="form.positionId">
                            <SelectTrigger class="mt-1 w-full sm:w-[350px]">
                                <SelectValue placeholder="Select a position" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup class="w-full sm:w-[350px]">
                                    <SelectItem v-for="item in positions" :key="item.id" :value="item.id">{{ item.name }}</SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                        <InputError class="mt-1" :message="form.errors.positionId" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">
                            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            Simpan Jabatan
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

            <Separator class="my-6" />

            <SpecialDisposition :user_id="data.id" :special-dispositions="specialDispositions" :positions="positions" />
        </MasterLayout>
    </AppLayout>
</template>
