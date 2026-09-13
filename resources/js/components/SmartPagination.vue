<script lang="ts" setup>
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem } from '@/components/ui/pagination';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import Icon from './Icon.vue';

// --- Type Definitions ---

interface PaginationLink {
    label: string;
    url: string | null;
    active: boolean;
}

type PaginationItemType = 'page' | 'arrow' | 'ellipsis';

interface PaginationDisplayItem extends Partial<PaginationLink> {
    type: PaginationItemType;
}

// --- Props ---

const props = defineProps<{
    links: PaginationLink[];
    total: number;
    itemsPerPage?: number;
}>();

const itemsPerPage = props.itemsPerPage ?? 2;

// --- Helper Functions ---

const isArrow = (label: string): boolean => label === 'pagination.previous' || label === 'pagination.next';

const arrowIcon = (label?: string): string => {
    return label === 'pagination.previous' ? 'ChevronLeftCircle' : 'ChevronRightCircle';
};

// --- Computed Pagination Display List ---
const filteredLinks = computed<PaginationDisplayItem[]>(() => {
    const links = props.links;
    const arrows = links.filter((l) => isArrow(l.label));
    const pages = links.filter((l) => !isArrow(l.label));
    const currentIndex = pages.findIndex((l) => l.active);

    const currentPageNumber = parseInt(pages[currentIndex]?.label ?? '1');
    const totalPages = pages.length;

    const visiblePages: PaginationDisplayItem[] = [];

    // Ellipsis in front
    if (currentPageNumber > 3) {
        visiblePages.push({ type: 'ellipsis' });
    }

    // Center pages: current -1, current, current +1
    for (let i = currentPageNumber - 1; i <= currentPageNumber + 1; i++) {
        if (i >= 1 && i <= totalPages) {
            const page = pages.find((p) => p.label === i.toString());
            if (page) visiblePages.push({ ...page, type: 'page' });
        }
    }

    // Ellipsis behind
    if (currentPageNumber < totalPages - 2) {
        visiblePages.push({ type: 'ellipsis' });
    }

    return [{ ...arrows[0], type: 'arrow' as const }, ...visiblePages, { ...arrows[1], type: 'arrow' as const }];
});
</script>

<template>
    <Pagination :items-per-page="itemsPerPage" :total="total" :default-page="1">
        <PaginationContent>
            <template v-for="(item, index) in filteredLinks" :key="index">
                <!-- Numbered Page -->
                <PaginationItem v-if="item.type === 'page' && item.url" :value="index" :is-active="item.active">
                    <Link :href="item.url">{{ item.label }}</Link>
                </PaginationItem>

                <!-- Arrows -->
                <PaginationItem v-else-if="item.type === 'arrow'" :value="index">
                    <Link v-if="item.url" :href="item.url">
                        <Icon :name="arrowIcon(item.label)" class="size-5" />
                    </Link>
                    <Icon v-else :name="arrowIcon(item.label)" class="size-5" />
                </PaginationItem>

                <!-- Ellipsis -->
                <PaginationEllipsis v-else-if="item.type === 'ellipsis'" />
            </template>
        </PaginationContent>
    </Pagination>
</template>
