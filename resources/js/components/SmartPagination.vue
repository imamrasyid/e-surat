<script lang="ts" setup>
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem } from '@/components/ui/pagination';
import type { PaginationLink } from '@/types';
import { Link } from '@inertiajs/vue3';
import { ChevronLeftCircle, ChevronRightCircle } from '@lucide/vue';
import type { HTMLAttributes } from 'vue';
import { computed } from 'vue';

// --- Type Definitions ---

type PaginationItemType = 'page' | 'arrow' | 'ellipsis';

interface PaginationDisplayItem extends Partial<PaginationLink> {
    type: PaginationItemType;
}

interface Props {
    links: PaginationLink[];
    total: number;
    itemsPerPage?: number;
    class?: HTMLAttributes['class'];
}

// --- Props ---

const props = withDefaults(defineProps<Props>(), {
    itemsPerPage: 2,
});

// --- Helper Functions ---

const isPrev = (label?: string): boolean => label === 'pagination.previous' || label?.toLowerCase().includes('prev') === true;
const isNext = (label?: string): boolean => label === 'pagination.next' || label?.toLowerCase().includes('next') === true;
const isArrow = (label?: string): boolean => isPrev(label) || isNext(label);

const getArrowIcon = (label?: string) => (isPrev(label) ? ChevronLeftCircle : ChevronRightCircle);

// --- Computed Pagination Display List ---

const filteredLinks = computed<PaginationDisplayItem[]>(() => {
    if (!props.links || props.links.length === 0) {
        return [];
    }

    const prevArrow = props.links.find((l) => isPrev(l.label));
    const nextArrow = props.links.find((l) => isNext(l.label));
    const pages = props.links.filter((l) => !isArrow(l.label));

    const currentIndex = pages.findIndex((l) => l.active);
    const currentPageNumber = parseInt(pages[currentIndex]?.label ?? '1', 10);
    const totalPages = pages.length;

    const visiblePages: PaginationDisplayItem[] = [];

    // Ellipsis in front
    if (currentPageNumber > 3) {
        visiblePages.push({ type: 'ellipsis' });
    }

    // Center pages: current - 1, current, current + 1
    for (let i = currentPageNumber - 1; i <= currentPageNumber + 1; i++) {
        if (i >= 1 && i <= totalPages) {
            const page = pages.find((p) => p.label === String(i));
            if (page) {
                visiblePages.push({ ...page, type: 'page' });
            }
        }
    }

    // Ellipsis behind
    if (currentPageNumber < totalPages - 2) {
        visiblePages.push({ type: 'ellipsis' });
    }

    const result: PaginationDisplayItem[] = [];

    if (prevArrow) {
        result.push({ ...prevArrow, type: 'arrow' });
    }

    result.push(...visiblePages);

    if (nextArrow) {
        result.push({ ...nextArrow, type: 'arrow' });
    }

    return result;
});
</script>

<template>
    <Pagination :items-per-page="itemsPerPage" :total="total" :default-page="1" :class="props.class">
        <PaginationContent>
            <template v-for="(item, index) in filteredLinks" :key="`${item.type}-${item.label ?? index}`">
                <!-- Numbered Page -->
                <PaginationItem v-if="item.type === 'page' && item.url" :value="index" :is-active="item.active">
                    <Link :href="item.url">{{ item.label }}</Link>
                </PaginationItem>

                <!-- Arrows (Previous & Next) -->
                <PaginationItem v-else-if="item.type === 'arrow'" :value="index" :disabled="!item.url">
                    <Link v-if="item.url" :href="item.url" :aria-label="item.label">
                        <component :is="getArrowIcon(item.label)" class="size-5" />
                    </Link>
                    <component :is="getArrowIcon(item.label)" v-else class="size-5 opacity-40" />
                </PaginationItem>

                <!-- Ellipsis -->
                <PaginationEllipsis v-else-if="item.type === 'ellipsis'" />
            </template>
        </PaginationContent>
    </Pagination>
</template>
