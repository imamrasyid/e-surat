<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import SmartPagination from '@/components/SmartPagination.vue';
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
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import AppLayout from '@/layouts/AppLayout.vue';
import MasterLayout from '@/layouts/master/Layout.vue';
import type { BreadcrumbItem, PaginatedData, User } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { Award, CircleEllipsis, CirclePlus, KeyRound, Search, UserRoundPen } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    data: PaginatedData<User>;
}>();

const search = ref<string>('');
watchDebounced(
    search,
    () => {
        router.get(
            route('ref.user.index'),
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
        <Head title="Referensi Data Pengguna" />

        <MasterLayout>
            <div class="space-y-6">
                <HeadingSmall title="Data Pengguna" description="Daftar referensi pengguna" />

                <div class="space-y-2 sm:flex sm:items-center sm:justify-between sm:space-y-0">
                    <div class="relative w-full max-w-sm items-center sm:max-w-[250px]">
                        <Input id="search" type="text" v-model="search" placeholder="Search..." class="pl-8" autocomplete="off" />
                        <span class="absolute inset-y-0 start-0 flex items-center justify-center px-2">
                            <Search class="size-5 text-muted-foreground" />
                        </span>
                    </div>
                    <Button as-child>
                        <Link :href="route('ref.user.create')">
                            <CirclePlus class="size-5" />
                            Tambah Baru
                        </Link>
                    </Button>
                </div>
                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nama Pengguna</TableHead>
                                <TableHead>Username</TableHead>
                                <TableHead class="w-[125px]">Status</TableHead>
                                <TableHead class="w-[50px]"></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="value in data.data" :key="value.id">
                                <TableCell>{{ value.name }}</TableCell>
                                <TableCell>{{ value.email }}</TableCell>
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
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger as-child>
                                                        <Button variant="outline" :size="null" class="rounded-full p-1">
                                                            <CircleEllipsis class="size-4" />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent class="max-w-[100px] rounded-lg">
                                                        <DropdownMenuLabel class="font-normal">Actions</DropdownMenuLabel>
                                                        <DropdownMenuSeparator />
                                                        <DropdownMenuGroup>
                                                            <DropdownMenuItem :as-child="true">
                                                                <Link class="block w-full" :href="route('ref.user.edit', value.id)">
                                                                    <UserRoundPen class="mr-2 h-4 w-4" />
                                                                    Perbarui
                                                                </Link>
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem :as-child="true">
                                                                <Link class="block w-full" :href="route('ref.user.editPosition', value.id)">
                                                                    <Award class="mr-2 h-4 w-4" />
                                                                    Jabatan
                                                                </Link>
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem :as-child="true">
                                                                <Link class="block w-full" :href="route('ref.user.editRole', value.id)">
                                                                    <KeyRound class="mr-2 h-4 w-4" />
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
                                    </TooltipProvider>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="data.data.length === 0">
                                <TableCell colspan="4" class="text-center text-gray-500">Data tidak ditemukan.</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <SmartPagination :links="data.links" :total="data.total" :items-per-page="5" class="justify-end" />
            </div>
        </MasterLayout>
    </AppLayout>
</template>
