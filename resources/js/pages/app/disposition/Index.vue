<script setup lang="ts">
import ListFilterBar from '@/components/filters/ListFilterBar.vue';
import Heading from '@/components/Heading.vue';
import LetterFollowUpTable from '@/components/letter/LetterFollowUpTable.vue';
import SmartPagination from '@/components/SmartPagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Disposition, LetterFollowUpItem, PaginatedData } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { shallowRef } from 'vue';

defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    dispo: PaginatedData<Disposition>;
}>();

const page = usePage();
const query = new URLSearchParams(page.url.split('?')[1] ?? '');

const search = shallowRef(query.get('search') ?? '');
const filter = shallowRef(query.get('filter') ?? 'all');

watchDebounced(
    [search, filter],
    () => {
        router.get(
            route('dispo.index'),
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
    router.get(route('dispo.show', { recipient, letter_id }));
}

function goToDetailPatch(disposition: number, letter_id: string) {
    router.patch(route('dispo.update', { disposition, letter_id }));
}

function handleClick(item: LetterFollowUpItem) {
    if (!item.letter) return;

    if (item.read_at) {
        goToDetail(item.id, item.letter.letter_id);
    } else {
        goToDetailPatch(item.id, item.letter.letter_id);
    }
}
</script>

<template>
    <Head title="Disposisi Surat" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6">
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <Heading class="mb-0!" title="Disposisi" description="Daftar disposisi surat" />
                <ListFilterBar v-model:search="search" v-model:filter="filter" />
            </div>

            <div class="space-y-6">
                <LetterFollowUpTable :items="dispo.data" @open="handleClick" />
                <SmartPagination :links="dispo.links" :total="dispo.total" :items-per-page="dispo.per_page" class="justify-end" />
            </div>
        </div>
    </AppLayout>
</template>
