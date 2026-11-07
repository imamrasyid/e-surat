<script setup lang="ts">
import { Card, CardAction, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import type { DashboardMetric } from '@/types/dashboard';
import { computed } from 'vue';
import { formatNumber } from './utils';

const props = defineProps<{
    metric: DashboardMetric;
}>();

const formattedValue = computed(() => formatNumber(props.metric.value));
</script>

<template>
    <Card class="hover:border-primary/40 dark:bg-input/30 gap-4 shadow-none transition-colors duration-200">
        <CardHeader>
            <CardTitle>{{ props.metric.title }}</CardTitle>
            <CardDescription>{{ props.metric.description }}</CardDescription>
            <CardAction>
                <div class="bg-primary/10 flex size-9 items-center justify-center gap-2 rounded-md border">
                    <component :is="props.metric.icon" class="text-primary size-4 shrink-0" aria-hidden="true" />
                </div>
            </CardAction>
        </CardHeader>
        <CardContent class="mt-auto">
            <div class="text-3xl font-bold tabular-nums">
                {{ formattedValue
                }}<span v-if="props.metric.unit" class="text-muted-foreground ml-1 text-sm font-medium">{{ props.metric.unit }}</span>
            </div>
        </CardContent>
    </Card>
</template>
