<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import MasterLayout from '@/layouts/master/Layout.vue';
import type { BreadcrumbItem, Permission, Role } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Loader2 } from '@lucide/vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    data: Role;
    rolePermissions: Permission[];
    permissions: Permission[];
}>();

const getRowCount = (cols: number) => {
    return Math.ceil(props.permissions.length / cols);
};

const form = useForm({
    name: props.data.name,
});

const submit = () => {
    form.patch(route('ref.role.update', props.data.id), {
        onSuccess: () => {
            toast.success('Success! Data submitted.', {
                description: 'Data berhasil tersimpan',
            });
        },
        onError: (errors: any) => {
            if (errors.confirm) {
                toast.error('Uh oh! Something went wrong.', {
                    description: errors.confirm,
                });
            } else {
                toast.warning('Uh oh! Something went wrong.', {
                    description: 'Sepertinya data belum lengkap',
                });
            }
        },
    });
};

const formPermission = useForm({
    permissions: props.rolePermissions.map((permision) => permision.id),
});

const submitPermission = () => {
    formPermission.patch(route('ref.role.updatePermission', props.data.id));
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Perbarui Referensi Role" />

        <MasterLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Perbarui" description="Edit referensi role" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="nama">Nama</Label>
                        <Input
                            id="nama"
                            type="text"
                            class="mt-1 block w-full sm:w-[350px]"
                            v-model="form.name"
                            autocomplete="off"
                            placeholder="Masukkan nama role"
                        />
                        <InputError class="mt-1" :message="form.errors.name" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button type="submit" :disabled="form.processing">
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

            <Separator class="my-6" />

            <form @submit.prevent="submitPermission" class="flex flex-col space-y-6">
                <HeadingSmall title="Permissions" description="Edit referensi permissions" />
                <Card>
                    <CardContent
                        class="grid [grid-template-rows:repeat(auto-fill,minmax(0,1fr))] gap-4 sm:grid-flow-col sm:[grid-template-rows:repeat(calc(var(--row-sm)),minmax(0,1fr))] md:[grid-template-rows:repeat(calc(var(--row-md)),minmax(0,1fr))]"
                        :style="{
                            '--row-sm': getRowCount(2),
                            '--row-md': getRowCount(3),
                        }"
                    >
                        <div v-for="(item, index) in permissions" :key="index" class="flex space-x-2">
                            <input
                                :id="'permission' + index"
                                type="checkbox"
                                class="cursor-pointer"
                                :value="item.id"
                                v-model="formPermission.permissions"
                            />
                            <label
                                :for="'permission' + index"
                                class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                            >
                                {{ item.name }}
                            </label>
                        </div>
                    </CardContent>
                </Card>

                <Button type="submit" :disabled="formPermission.processing">
                    <Loader2 v-if="formPermission.processing" class="mr-2 h-4 w-4 animate-spin" />
                    Simpan Permission
                </Button>
            </form>
        </MasterLayout>
    </AppLayout>
</template>
