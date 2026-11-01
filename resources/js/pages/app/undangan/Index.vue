<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import ListFilterBar from '@/components/filters/ListFilterBar.vue';
import InboxLetterTable from '@/components/inbox/InboxLetterTable.vue';
import SmartPagination from '@/components/SmartPagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, LetterRecipient, PaginatedData } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { shallowRef } from 'vue';

defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    undangan: PaginatedData<LetterRecipient>;
}>();

const page = usePage();
const query = new URLSearchParams(page.url.split('?')[1] ?? '');

const search = shallowRef(query.get('search') ?? '');
const filter = shallowRef(query.get('filter') ?? 'all');

watchDebounced(
    [search, filter],
    () => {
        router.get(
            route('inbox.undangan.index'),
            { search: search.value, filter: filter.value },
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

function goToDetail(recipient: number, letter_id: string) {
    router.get(route('inbox.undangan.show', { recipient, letter_id }));
}

function goToDetailPatch(recipient: number, letter_id: string) {
    router.patch(route('inbox.undangan.show', { recipient, letter_id }));
}

function handleClick(item: LetterRecipient) {
    if (!item.letter) return;

    if (item.read_at) {
        goToDetail(item.id, item.letter.letter_id);
    } else {
        goToDetailPatch(item.id, item.letter.letter_id);
    }
}
</script>

<template>
    <Head title="Surat Masuk Undangan" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6">
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <Heading class="mb-0!" title="Undangan" description="Daftar surat masuk undangan" />
                <ListFilterBar v-model:search="search" v-model:filter="filter" />
            </div>

            <div class="space-y-6">
                <InboxLetterTable :items="undangan.data" @open="handleClick" />
                <SmartPagination :links="undangan.links" :total="undangan.total" :items-per-page="undangan.per_page" class="justify-end" />
            </div>
        </div>
    </AppLayout>
</template>
