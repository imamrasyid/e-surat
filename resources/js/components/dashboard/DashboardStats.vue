<script setup lang="ts">
import type { DashboardMetric } from '@/types/dashboard';
import { computed } from 'vue';
import DashboardStatCard from './DashboardStatCard.vue';

const props = defineProps<{
    metrics: DashboardMetric[];
}>();

const gridClasses = computed(() =>
    props.metrics.length === 3 ? 'grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3' : 'grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4',
);
</script>

<template>
    <div :class="gridClasses">
        <DashboardStatCard v-for="metric in metrics" :key="metric.key" :metric="metric" />
    </div>
</template>
