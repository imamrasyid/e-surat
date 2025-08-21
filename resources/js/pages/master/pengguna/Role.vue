<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useToast } from '@/components/ui/toast/use-toast';
import AppLayout from '@/layouts/AppLayout.vue';
import MasterLayout from '@/layouts/master/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Loader2 } from 'lucide-vue-next';

const { toast } = useToast();

const props = defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    data: any;
    roles: any[];
}>();

const form = useForm({
    roles: props.data.roles.map((roles: any) => roles.id),
});

const submit = () => {
    form.post(route('ref.user.updateRole', props.data.id), {
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
        <Head title="Referensi Role Pengguna" />

        <MasterLayout class="mb-10">
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Role Pengguna" description="Referensi role pengguna" />

                <Card class="p-0 sm:w-[350px]">
                    <CardHeader class="p-4">
                        <CardTitle>{{ data.name }}</CardTitle>
                        <CardDescription>{{ data.email }}</CardDescription>
                    </CardHeader>
                </Card>

                <form @submit.prevent="submit" class="flex flex-col space-y-6">
                    <Card>
                        <CardContent class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                            <div v-for="(item, index) in roles" :key="index" class="flex space-x-2">
                                <input :id="'role' + index" type="checkbox" class="cursor-pointer" :value="item.id" v-model="form.roles" />
                                <label
                                    :for="'role' + index"
                                    class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                >
                                    {{ item.name }}
                                </label>
                            </div>
                        </CardContent>
                    </Card>

                    <Button type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        Simpan Role
                    </Button>
                </form>
            </div>
        </MasterLayout>
    </AppLayout>
</template>
