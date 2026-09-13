<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import SmartPagination from '@/components/SmartPagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import AppLayout from '@/layouts/AppLayout.vue';
import MasterLayout from '@/layouts/master/Layout.vue';
import type { BreadcrumbItem, PaginatedData, Role } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { CirclePlus, FilePenLine, Search } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    data: PaginatedData<Role>;
}>();

const search = ref<string>('');
watchDebounced(
    search,
    () => {
        router.get(
            route('ref.role.index'),
            { search: search.value },
            {
                preserveState: true,
            },
        );
    },
    {
        debounce: 500,
        maxWait: 1000,
    },
);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Data Referensi Role" />

        <MasterLayout>
            <div class="space-y-6">
                <HeadingSmall title="Data Role" description="Daftar data referensi role" />

                <div class="space-y-2 sm:flex sm:items-center sm:justify-between sm:space-y-0">
                    <div class="relative w-full max-w-sm items-center sm:max-w-[250px]">
                        <Input id="search" type="text" v-model="search" placeholder="Search..." class="pl-8" autocomplete="off" />
                        <span class="absolute inset-y-0 start-0 flex items-center justify-center px-2">
                            <Search class="size-5 text-muted-foreground" />
                        </span>
                    </div>
                    <Button as-child>
                        <Link :href="route('ref.role.create')">
                            <CirclePlus class="size-5" />
                            Tambah Baru
                        </Link>
                    </Button>
                </div>
                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nama</TableHead>
                                <TableHead class="min-w-[125px] text-right">Action</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="value in data.data" :key="value.id">
                                <TableCell>{{ value.name }}</TableCell>
                                <TableCell class="text-right">
                                    <TooltipProvider>
                                        <Tooltip>
                                            <TooltipTrigger>
                                                <Button as-child variant="outline" :size="null" class="rounded-full p-1">
                                                    <Link :href="route('ref.role.edit', value.id)">
                                                        <FilePenLine class="size-4" />
                                                    </Link>
                                                </Button>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                <p>Perbarui role</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="data.data.length === 0">
                                <TableCell colspan="3" class="text-center text-gray-500">Data tidak ditemukan.</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <SmartPagination :links="data.links" :total="data.total" :items-per-page="5" class="justify-end" />
            </div>
        </MasterLayout>
    </AppLayout>
</template>
