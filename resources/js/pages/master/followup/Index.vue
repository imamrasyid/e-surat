<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import SmartPagination from '@/components/SmartPagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import AppLayout from '@/layouts/AppLayout.vue';
import MasterLayout from '@/layouts/master/Layout.vue';
import type { BreadcrumbItem, Followup, PaginatedData } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { CirclePlus, FilePenLine, Search } from '@lucide/vue';
import { watchDebounced } from '@vueuse/core';
import { ref } from 'vue';

defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    data: PaginatedData<Followup>;
}>();

const search = ref<string>('');
watchDebounced(
    search,
    () => {
        router.get(
            route('ref.followup.index'),
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
        <Head title="Data Referensi Followup" />

        <MasterLayout>
            <div class="space-y-6">
                <HeadingSmall title="Data Followup" description="Daftar data referensi followup" />

                <div class="space-y-2 sm:flex sm:items-center sm:justify-between sm:space-y-0">
                    <div class="relative w-full max-w-sm items-center sm:max-w-[250px]">
                        <Input id="search" type="text" v-model="search" placeholder="Search..." class="pl-8" autocomplete="off" />
                        <span class="absolute inset-y-0 start-0 flex items-center justify-center px-2">
                            <Search class="text-muted-foreground size-5" />
                        </span>
                    </div>
                    <Button as-child>
                        <Link :href="route('ref.followup.create')">
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
                                <TableHead class="w-[125px]">Status</TableHead>
                                <TableHead class="text-right">Action</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="value in data.data" :key="value.id">
                                <TableCell>{{ value.name }}</TableCell>
                                <TableCell>
                                    <div v-if="value.is_active" class="inline rounded-full bg-sky-50 px-3 py-1 text-xs text-sky-500 dark:bg-gray-800">
                                        Aktif
                                    </div>
                                    <div v-else class="inline rounded-full bg-red-50 px-3 py-1 text-xs text-red-500 dark:bg-gray-800">Non Aktif</div>
                                </TableCell>
                                <TableCell class="text-right">
                                    <TooltipProvider>
                                        <Tooltip>
                                            <TooltipTrigger>
                                                <Button as-child variant="outline" :size="null" class="rounded-full p-1">
                                                    <Link :href="route('ref.followup.edit', value.id)">
                                                        <FilePenLine class="size-4" />
                                                    </Link>
                                                </Button>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                <p>Perbarui followup</p>
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
