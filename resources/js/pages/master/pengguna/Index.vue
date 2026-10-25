<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import SmartPagination from '@/components/SmartPagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { InputGroup, InputGroupAddon, InputGroupInput } from '@/components/ui/input-group';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import AppLayout from '@/layouts/AppLayout.vue';
import MasterLayout from '@/layouts/master/Layout.vue';
import type { BreadcrumbItem, PaginatedData, User } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Award, CircleEllipsis, CirclePlus, KeyRound, Search, UserRoundPen } from '@lucide/vue';
import { watchDebounced } from '@vueuse/core';
import { shallowRef } from 'vue';

const props = defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    data: PaginatedData<User>;
}>();

const page = usePage();
const query = new URLSearchParams(page.url.split('?')[1] ?? '');
const search = shallowRef(query.get('search') ?? '');
watchDebounced(
    search,
    () => {
        router.get(
            route('ref.user.index'),
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
        <Head title="Master Data Pengguna" />

        <MasterLayout>
            <div class="space-y-6">
                <HeadingSmall title="Pengguna" description="Kelola data referensi pengguna" />

                <div class="space-y-2 sm:flex sm:items-center sm:justify-between sm:space-y-0">
                    <InputGroup class="w-full sm:w-62.5">
                        <InputGroupInput id="search-pengguna" v-model="search" aria-label="Cari data" placeholder="Cari" />
                        <InputGroupAddon>
                            <Search aria-hidden="true" />
                        </InputGroupAddon>
                    </InputGroup>
                    <Button as-child>
                        <Link :href="route('ref.user.create')">
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
                                    <TableHead class="min-w-50">Nama Pengguna</TableHead>
                                    <TableHead class="min-w-30">Username</TableHead>
                                    <TableHead class="min-w-35">Email</TableHead>
                                    <TableHead class="min-w-25">Status</TableHead>
                                    <TableHead class="w-25 text-center">Action</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="value in data.data" :key="value.id">
                                    <TableCell>{{ value.name }}</TableCell>
                                    <TableCell>{{ value.username }}</TableCell>
                                    <TableCell>{{ value.email ?? '-' }}</TableCell>
                                    <TableCell>
                                        <Badge v-if="value.is_active">Aktif</Badge>
                                        <Badge v-else variant="destructive">Non Aktif</Badge>
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <Tooltip v-if="value.id !== 1">
                                            <TooltipTrigger>
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger as-child>
                                                        <Button variant="ghost" size="icon-sm">
                                                            <CircleEllipsis />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end" class="max-w-25 rounded-lg">
                                                        <DropdownMenuLabel class="font-normal">Actions</DropdownMenuLabel>
                                                        <DropdownMenuSeparator />
                                                        <DropdownMenuGroup>
                                                            <DropdownMenuItem :as-child="true">
                                                                <Link class="block w-full" :href="route('ref.user.edit', value.id)">
                                                                    <UserRoundPen class="size-4" />
                                                                    Perbarui
                                                                </Link>
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem :as-child="true">
                                                                <Link class="block w-full" :href="route('ref.user.editPosition', value.id)">
                                                                    <Award class="size-4" />
                                                                    Jabatan
                                                                </Link>
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem :as-child="true">
                                                                <Link class="block w-full" :href="route('ref.user.editRole', value.id)">
                                                                    <KeyRound class="size-4" />
                                                                    Role
                                                                </Link>
                                                            </DropdownMenuItem>
                                                        </DropdownMenuGroup>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                <p>Actions</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="data.data.length === 0">
                                    <TableCell colspan="5" class="text-muted-foreground text-center">Data tidak ditemukan.</TableCell>
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
