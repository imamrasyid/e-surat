<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import SmartPagination from '@/components/SmartPagination.vue';
import { InputGroup, InputGroupAddon, InputGroupInput } from '@/components/ui/input-group';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormattedDate } from '@/composables/useFormattedDate';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Letter, PaginatedData } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Search } from '@lucide/vue';
import { watchDebounced } from '@vueuse/core';
import { ref } from 'vue';

const { formatCreate } = useFormattedDate();

defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    sent: PaginatedData<Letter>;
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
                <Heading class="mb-0!" title="Surat Terkirim" description="Daftar surat yang telah terkirim" />
                 <InputGroup class="sm:max-w-62.5">
                    <InputGroupInput id="search-sent-letter" v-model="search" placeholder="Cari..." />
                    <InputGroupAddon>
                        <Search />
                    </InputGroupAddon>
                </InputGroup>
            </div>

            <div class="space-y-6">
                <div class="rounded-md border overflow-hidden">
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
                            <TableRow v-for="(item, index) in sent.data" :key="item.id ?? index" class="cursor-pointer" @click="goToDetail(item.letter_id)">
                                <TableCell class="align-top">{{ item.agenda }}</TableCell>
                                <TableCell class="align-top">{{ item.number }}</TableCell>
                                <TableCell class="align-top">
                                    {{ item.sender }}
                                    <p class="text-muted-foreground line-clamp-1">
                                        {{ item.subject }}
                                    </p>
                                </TableCell>
                                <TableCell class="align-top">{{ item.status == 'open' ? 'Open' : 'Close' }}</TableCell>
                                <TableCell class="whitespace-nowrap text-right align-top">
                                    {{ formatCreate(item.created_at) }}
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="sent.data.length === 0">
                                <TableCell colspan="5" class="text-muted-foreground text-center">Data tidak ditemukan.</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <SmartPagination :links="sent.links" :total="sent.total" :items-per-page="5" class="justify-end" />
            </div>
        </div>
    </AppLayout>
</template>
