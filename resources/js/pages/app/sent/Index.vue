<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import SmartPagination from '@/components/SmartPagination.vue';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormattedDate } from '@/composables/useFormattedDate';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { Search } from 'lucide-vue-next';
import { ref } from 'vue';

const { formatCreate } = useFormattedDate();

defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    sent: any;
}>();

const search = ref<string>('');
watchDebounced(
    search,
    () => {
        router.get(
            route('sent.index'),
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

function goToDetail(letter_id: string) {
    router.get(
        route('sent.show', letter_id),
        {},
        {
            preserveState: true,
        },
    );
}
</script>

<template>
    <Head title="Surat Terkirim" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6">
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <Heading class="!mb-0" title="Surat Terkirim" description="Daftar surat yang telah terkirim" />
                <div class="relative w-full max-w-sm items-center sm:max-w-[250px]">
                    <Input id="search" type="text" v-model="search" placeholder="Cari..." class="h-10 pl-8" autocomplete="off" />
                    <span class="absolute inset-y-0 start-0 flex items-center justify-center px-2">
                        <Search class="size-5 text-muted-foreground" />
                    </span>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-md border">
                    <Table>
                        <TableHeader class="bg-muted/30">
                            <TableRow class="hover:bg-transparent">
                                <TableHead class="w-[125px] xl:w-[175px]">Agenda</TableHead>
                                <TableHead class="w-[165px] xl:w-[260px]">Nomor Surat</TableHead>
                                <TableHead class="min-w-[180px]">Perihal</TableHead>
                                <TableHead class="w-[100px]">Status</TableHead>
                                <TableHead class="w-[100px] text-right"></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="(item, index) in sent.data" :key="index" class="cursor-pointer" @click="goToDetail(item.letter_id)">
                                <TableCell class="align-top">{{ item.agenda }}</TableCell>
                                <TableCell class="align-top">{{ item.number }}</TableCell>
                                <TableCell class="align-top">
                                    <div>
                                        <p>
                                            {{ item.sender }}
                                        </p>
                                        <p class="line-clamp-1 text-muted-foreground">
                                            {{ item.subject }}
                                        </p>
                                    </div>
                                </TableCell>
                                <TableCell class="align-top">{{ item.status == 'open' ? 'Open' : 'Close' }}</TableCell>
                                <TableCell class="text-right align-top">
                                    {{ formatCreate(item.created_at) }}
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="sent.data.length === 0">
                                <TableCell colspan="4" class="text-center text-muted-foreground">Data tidak ditemukan.</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <SmartPagination :links="sent.links" :total="sent.total" :items-per-page="5" class="justify-end" />
            </div>
        </div>
    </AppLayout>
</template>
