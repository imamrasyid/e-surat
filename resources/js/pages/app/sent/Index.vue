<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import SmartPagination from '@/components/SmartPagination.vue';
import { InputGroup, InputGroupAddon, InputGroupInput } from '@/components/ui/input-group';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormattedDate } from '@/composables/useFormattedDate';
import { categoryInbox } from '@/data/helpers';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Letter, PaginatedData } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Search } from '@lucide/vue';
import { watchDebounced } from '@vueuse/core';
import { shallowRef } from 'vue';

const { formatCreate } = useFormattedDate();

defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    sent: PaginatedData<Letter>;
}>();

const page = usePage();
const query = new URLSearchParams(page.url.split('?')[1] ?? '');

const search = shallowRef(query.get('search') || '');
watchDebounced(
    search,
    () => {
        router.get(
            route('sent.index'),
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

function getCategoryName(value: string) {
    const found = categoryInbox.find((cat) => cat.value === value);
    return found ? found.name : '-';
}

function goToDetail(letter_id: string) {
    router.get(route('sent.show', letter_id));
}
</script>

<template>
    <Head title="Surat Terkirim" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6">
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <Heading class="mb-0!" title="Surat Terkirim" description="Daftar surat yang telah terkirim" />
                <InputGroup class="sm:max-w-62.5">
                    <InputGroupInput id="search-sent-letter" v-model="search" placeholder="Cari" />
                    <InputGroupAddon>
                        <Search />
                    </InputGroupAddon>
                </InputGroup>
            </div>

            <div class="space-y-6">
                <ScrollArea class="rounded-lg border">
                    <Table>
                        <TableHeader class="bg-muted/30">
                            <TableRow class="hover:bg-transparent">
                                <TableHead class="min-w-32">Agenda</TableHead>
                                <TableHead class="min-w-42">Nomor Surat</TableHead>
                                <TableHead class="min-w-46">Perihal</TableHead>
                                <TableHead class="w-20">Status</TableHead>
                                <TableHead class="w-20 text-right"></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="item in sent.data" :key="item.id" class="cursor-pointer" @click="goToDetail(item.letter_id)">
                                <TableCell class="align-top">{{ item.agenda }}</TableCell>
                                <TableCell class="align-top">
                                    {{ item.number ?? '-' }}
                                    <p class="text-muted-foreground">Kategori: {{ getCategoryName(item.category) }}</p>
                                </TableCell>
                                <TableCell class="align-top">
                                    {{ item.sender }}
                                    <p class="text-muted-foreground line-clamp-1">
                                        {{ item.subject }}
                                    </p>
                                </TableCell>
                                <TableCell class="align-top">{{ item.status == 'open' ? 'Terbuka' : 'Selesai' }}</TableCell>
                                <TableCell class="text-right align-top whitespace-nowrap">
                                    {{ formatCreate(item.created_at) }}
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="sent.data.length === 0">
                                <TableCell colspan="5" class="text-muted-foreground text-center">Data tidak ditemukan.</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                    <ScrollBar orientation="horizontal" />
                </ScrollArea>
                <SmartPagination :links="sent.links" :total="sent.total" :items-per-page="sent.per_page" class="justify-end" />
            </div>
        </div>
    </AppLayout>
</template>
