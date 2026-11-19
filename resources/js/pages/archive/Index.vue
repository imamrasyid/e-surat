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

const { formatDate } = useFormattedDate();

defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    letters: PaginatedData<Letter>;
}>();

const page = usePage();
const query = new URLSearchParams(page.url.split('?')[1] ?? '');

const search = shallowRef(query.get('search') || '');
watchDebounced(
    search,
    () => {
        router.get(
            route('arsip.index'),
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
    router.get(route('arsip.show', letter_id));
}
</script>

<template>
    <Head title="Arsip Surat" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6">
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <Heading class="mb-0!" title="Arsip Surat" description="Arsip surat masuk yang telah ditutup" />
                <InputGroup class="sm:max-w-62.5">
                    <InputGroupInput
                        id="search-archive-letter"
                        v-model="search"
                        aria-label="Cari arsip surat"
                        autocomplete="off"
                        placeholder="Cari"
                    />
                    <InputGroupAddon>
                        <Search aria-hidden="true" />
                    </InputGroupAddon>
                </InputGroup>
            </div>

            <div class="space-y-6">
                <ScrollArea class="rounded-lg border">
                    <Table>
                        <TableHeader class="bg-muted/30">
                            <TableRow class="hover:bg-transparent">
                                <TableHead class="min-w-32">Agenda</TableHead>
                                <TableHead class="min-w-28">Kategori</TableHead>
                                <TableHead class="min-w-42">Pengirim</TableHead>
                                <TableHead class="min-w-42">Nomor Surat</TableHead>
                                <TableHead class="min-w-46">Perihal</TableHead>
                                <TableHead class="min-w-38">Tanggal Diterima</TableHead>
                                <TableHead class="w-20">Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="item in letters.data"
                                :key="item.id"
                                tabindex="0"
                                class="focus-visible:bg-muted/50 cursor-pointer focus-visible:outline-none"
                                @click="goToDetail(item.letter_id)"
                                @keydown.enter.prevent="goToDetail(item.letter_id)"
                                @keydown.space.prevent="goToDetail(item.letter_id)"
                            >
                                <TableCell class="align-top">{{ item.agenda }}</TableCell>
                                <TableCell class="align-top">{{ getCategoryName(item.category) }}</TableCell>
                                <TableCell class="align-top">{{ item.sender }}</TableCell>
                                <TableCell class="align-top">{{ item.number ?? '-' }}</TableCell>
                                <TableCell class="align-top">{{ item.subject }}</TableCell>
                                <TableCell class="align-top">{{ formatDate(item.receipt_date) }}</TableCell>
                                <TableCell class="align-top">{{ item.status === 'open' ? 'Terbuka' : 'Selesai' }}</TableCell>
                            </TableRow>
                            <TableRow v-if="letters.data.length === 0">
                                <TableCell colspan="7" class="text-muted-foreground text-center">Data tidak ditemukan.</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                    <ScrollBar orientation="horizontal" />
                </ScrollArea>
                <SmartPagination :links="letters.links" :total="letters.total" :items-per-page="letters.per_page" class="justify-end" />
            </div>
        </div>
    </AppLayout>
</template>
