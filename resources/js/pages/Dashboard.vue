<script setup lang="ts">
import DashboardRoleOverview from '@/components/dashboard/DashboardRoleOverview.vue';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { AppPageProps, BreadcrumbItem } from '@/types';
import type { ChartDataItem, DashboardPayload, UserChartPayload } from '@/types/dashboard';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type Props = {
    dashboard: DashboardPayload;
    chartData: ChartDataItem[];
    userChartData: UserChartPayload | null;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const page = usePage<AppPageProps<Props>>();
const userName = computed(() => page.props.auth.user.name);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-7xl px-4 py-6 lg:px-6 lg:py-8">
            <Heading :title="`Halo, ${userName}`" description="Ringkasan aktivitas dan persuratan" />

            <DashboardRoleOverview
                :dashboard="props.dashboard"
                :chart-data="props.chartData"
                :user-chart-data="props.userChartData"
                :inbox="page.props.inbox"
            />
        </div>
    </AppLayout>
</template>
