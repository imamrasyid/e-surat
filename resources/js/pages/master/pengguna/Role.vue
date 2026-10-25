<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import MasterLayout from '@/layouts/master/Layout.vue';
import type { BreadcrumbItem, Role, User } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Loader2, Send, Undo2 } from '@lucide/vue';
import { onMounted, ref } from 'vue';

const props = defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    data: User;
    roles: Role[];
}>();

const form = useForm({
    roles: props.data.roles?.map((role) => role.id) ?? [],
});

const hasHistory = ref(false);

const goBack = () => {
    window.history.back();
};

onMounted(() => {
    hasHistory.value = window.history.length > 1;
});

const submit = () => {
    form.post(route('ref.user.updateRole', props.data.id));
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Referensi Role Pengguna" />

        <MasterLayout class="mb-10">
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Role Pengguna" description="Referensi role pengguna" />

                <Card class="p-0">
                    <CardHeader class="p-4">
                        <CardTitle>{{ data.name }}</CardTitle>
                        <CardDescription>{{ data.email }}</CardDescription>
                    </CardHeader>
                </Card>

                <form @submit.prevent="submit" class="flex flex-col space-y-6">
                    <Card>
                        <CardContent class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                            <div v-for="item in roles" :key="item.id" class="flex space-x-2">
                                <input :id="'role' + item.id" type="checkbox" class="cursor-pointer" :value="item.id" v-model="form.roles" />
                                <label
                                    :for="'role' + item.id"
                                    class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                >
                                    {{ item.name }}
                                </label>
                            </div>
                        </CardContent>
                    </Card>

                    <div class="mt-12 flex flex-col gap-4 lg:flex-row lg:justify-end">
                        <Button v-if="hasHistory" type="button" variant="outline" class="w-full cursor-pointer lg:w-40" @click="goBack">
                            <Undo2 class="size-4" />
                            Batal
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="w-full cursor-pointer lg:w-50">
                            <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                            <Send v-else class="size-4" />
                            Simpan Role
                        </Button>
                    </div>
                </form>
            </div>
        </MasterLayout>
    </AppLayout>
</template>
