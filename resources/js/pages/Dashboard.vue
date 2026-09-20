<script setup lang="ts">
import DashboardStats from '@/components/dashboard/DashboardStats.vue';
import DashboardTrendChart from '@/components/dashboard/DashboardTrendChart.vue';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import type { ChartDataItem } from '@/types/dashboard';
import { Head, usePage } from '@inertiajs/vue3';

interface Props {
    totalLetters: number;
    inProcessLetters: number;
    completedLetters: number;
    chartData: ChartDataItem[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const page = usePage();
const authUser = page.props.auth.user;
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6">
            <Heading :title="`Halo, ${authUser.name}`" description="Selamat datang" />

            <DashboardStats
                :total-letters="props.totalLetters"
                :in-process-letters="props.inProcessLetters"
                :completed-letters="props.completedLetters"
            />

            <DashboardTrendChart :initial-data="props.chartData" />
        </div>
    </AppLayout>
</template>
