<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import SmartPagination from '@/components/SmartPagination.vue';
import { Button } from '@/components/ui/button';
import { InputGroup, InputGroupAddon, InputGroupInput } from '@/components/ui/input-group';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import AppLayout from '@/layouts/AppLayout.vue';
import MasterLayout from '@/layouts/master/Layout.vue';
import type { BreadcrumbItem, PaginatedData, Role } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { CirclePlus, Search, Settings2 } from '@lucide/vue';
import { watchDebounced } from '@vueuse/core';
import { shallowRef } from 'vue';

defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    data: PaginatedData<Role>;
}>();

const page = usePage();
const query = new URLSearchParams(page.url.split('?')[1] ?? '');
const search = shallowRef(query.get('search') ?? '');
watchDebounced(
    search,
    () => {
        router.get(
            route('ref.role.index'),
            { search: search.value },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
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
        <Head title="Master Data Role" />

        <MasterLayout>
            <div class="space-y-6">
                <HeadingSmall title="Role" description="Kelola data referensi role" />

                <div class="space-y-2 sm:flex sm:items-center sm:justify-between sm:space-y-0">
                    <InputGroup class="w-full sm:w-62.5">
                        <InputGroupInput id="search-role" v-model="search" aria-label="Cari data" placeholder="Cari" />
                        <InputGroupAddon>
                            <Search aria-hidden="true" />
                        </InputGroupAddon>
                    </InputGroup>
                    <Button as-child>
                        <Link :href="route('ref.role.create')">
                            <CirclePlus class="size-5" />
                            Tambah Baru
                        </Link>
                    </Button>
                </div>
                <ScrollArea class="rounded-lg border">
                    <TooltipProvider>
                        <Table>
                            <TableHeader class="bg-muted/30">
                                <TableRow class="hover:bg-transparent">
                                    <TableHead class="min-w-64">Nama</TableHead>
                                    <TableHead class="w-25 text-center">Action</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="value in data.data" :key="value.id">
                                    <TableCell>{{ value.name }}</TableCell>
                                    <TableCell class="text-center">
                                        <Tooltip>
                                            <TooltipTrigger>
                                                <Button as-child variant="ghost" size="icon-sm">
                                                    <Link :href="route('ref.role.edit', value.id)">
                                                        <Settings2 />
                                                    </Link>
                                                </Button>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                <p>Perbarui role</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="data.data.length === 0">
                                    <TableCell colspan="2" class="text-muted-foreground text-center">Data tidak ditemukan.</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </TooltipProvider>
                    <ScrollBar orientation="horizontal" />
                </ScrollArea>
                <SmartPagination :links="data.links" :total="data.total" :items-per-page="data.per_page" class="justify-end" />
            </div>
        </MasterLayout>
    </AppLayout>
</template>
