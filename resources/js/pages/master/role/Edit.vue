<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import MasterLayout from '@/layouts/master/Layout.vue';
import type { BreadcrumbItem, Permission, Role } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Loader2, Save, Undo2 } from '@lucide/vue';
import { onMounted, ref } from 'vue';
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

const hasHistory = ref(false);
const goBack = () => {
    window.history.back();
};

onMounted(() => {
    hasHistory.value = window.history.length > 1;
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

const togglePermission = (id: number, checked: boolean | 'indeterminate') => {
    const index = formPermission.permissions.indexOf(id);

    if (checked === true) {
        if (index === -1) {
            formPermission.permissions.push(id);
        }

        return;
    }

    if (index !== -1) {
        formPermission.permissions.splice(index, 1);
    }
};

const submitPermission = () => {
    formPermission.patch(route('ref.role.updatePermission', props.data.id));
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Perbarui Role" />

        <MasterLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Perbarui" description="Edit referensi role" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="nama">Nama</Label>
                        <Input id="nama" type="text" class="w-full" v-model="form.name" autocomplete="off" placeholder="Masukkan nama role" />
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

            <Separator class="my-6" />

            <form @submit.prevent="submitPermission" class="flex flex-col space-y-6">
                <HeadingSmall title="Permissions" description="Edit referensi permissions" />
                <Card class="dark:bg-input/30 rounded-lg shadow-none">
                    <CardContent
                        class="grid grid-rows-[repeat(auto-fill,minmax(0,1fr))] gap-4 sm:grid-flow-col sm:grid-rows-[repeat(calc(var(--row-sm)),minmax(0,1fr))] md:grid-rows-[repeat(calc(var(--row-md)),minmax(0,1fr))]"
                        :style="{
                            '--row-sm': getRowCount(2),
                            '--row-md': getRowCount(3),
                        }"
                    >
                        <div v-for="item in permissions" :key="item.id" class="flex items-center gap-2">
                            <Checkbox
                                :id="`permission-${item.id}`"
                                :model-value="formPermission.permissions.includes(item.id)"
                                @update:model-value="(checked) => togglePermission(item.id, checked)"
                            />
                            <Label :for="`permission-${item.id}`" class="cursor-pointer font-normal">
                                <span class="break-all">{{ item.name }}</span>
                            </Label>
                        </div>
                    </CardContent>
                </Card>

                <Button type="submit" :disabled="formPermission.processing">
                    <Loader2 v-if="formPermission.processing" class="size-4 animate-spin" />
                    <Save v-else class="size-4" />
                    Simpan Permission
                </Button>
            </form>
        </MasterLayout>
    </AppLayout>
</template>
