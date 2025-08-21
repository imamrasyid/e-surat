<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

defineProps<{
    breadcrumbItems: BreadcrumbItem[];
}>();
</script>

<template>
    <Head title="Arsip Surat" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6">
            <Heading title="Arsip Surat" description="Data arsip surat masuk" />

            <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>
