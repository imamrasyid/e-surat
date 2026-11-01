<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { type ChartConfig, ChartContainer, ChartTooltip } from '@/components/ui/chart';
import type { UserChartTotals } from '@/types/dashboard';
import { Donut } from '@unovis/ts';
import { VisDonut, VisSingleContainer } from '@unovis/vue';
import { computed } from 'vue';
import { userChartSeries } from './userChartSeries';
import { formatNumber } from './utils';

interface Props {
    totals: UserChartTotals;
}

const props = defineProps<Props>();

interface DistributionDatum {
    key: string;
    label: string;
    value: number;
    color: string;
}

const chartData = computed<DistributionDatum[]>(() =>
    userChartSeries.map((item) => ({
        key: item.key,
        label: item.label,
        value: props.totals[item.key],
        color: item.color,
    })),
);

const total = computed(() => chartData.value.reduce((accumulator, current) => accumulator + current.value, 0));

const chartConfig = Object.fromEntries(userChartSeries.map((item) => [item.key, { label: item.label, color: item.color }])) satisfies ChartConfig;

function tooltipTemplate(d: { data: DistributionDatum }): string {
    const datum = d.data;

    return `<div class="border-border/50 bg-background min-w-[120px] rounded-lg border px-3 py-2 text-xs shadow-xl">
        <div class="font-medium text-foreground mb-1">${datum.label}</div>
        <div class="flex items-center gap-2">
            <span class="inline-block h-2.5 w-2.5 shrink-0 rounded-[2px]" style="background: ${datum.color}"></span>
            <span class="text-muted-foreground">${formatNumber(datum.value)} Surat</span>
        </div>
    </div>`;
}
</script>

<template>
    <Card class="dark:bg-input/30 flex h-full min-w-0 flex-col sm:col-span-1">
        <CardHeader class="items-center pb-0">
            <CardTitle>Distribusi Surat</CardTitle>
            <CardDescription>Seluruh surat yang diterima</CardDescription>
        </CardHeader>
        <CardContent class="flex flex-1 flex-col justify-center gap-6 p-4 sm:p-6">
            <ChartContainer
                :config="chartConfig"
                class="mx-auto aspect-square max-h-50 min-w-0"
                role="img"
                aria-label="Diagram donat distribusi utama, undangan, disposisi, dan nota dinas"
                :style="{
                    '--vis-donut-central-label-font-size': 'var(--text-lg)',
                    '--vis-donut-central-label-font-weight': 'var(--font-bold)',
                    '--vis-donut-central-label-text-color': 'var(--foreground)',
                    '--vis-donut-central-sub-label-text-color': 'var(--muted-foreground)',
                }"
            >
                <VisSingleContainer :data="chartData" :margin="{ top: 10, bottom: 10 }">
                    <VisDonut
                        :value="(d: DistributionDatum) => d.value"
                        :color="(d: DistributionDatum) => d.color"
                        :arc-width="30"
                        :central-label-offset-y="10"
                        :central-label="formatNumber(total)"
                        central-sub-label="Surat"
                    />
                    <ChartTooltip
                        :triggers="{
                            [Donut.selectors.segment]: tooltipTemplate,
                        }"
                    />
                </VisSingleContainer>
            </ChartContainer>
            <ul class="grid gap-2">
                <li v-for="item in chartData" :key="item.key" class="flex items-center gap-2 text-xs">
                    <span class="h-2.5 w-2.5 shrink-0 rounded-xs" :style="{ background: item.color }" aria-hidden="true"></span>
                    <span class="text-muted-foreground flex-1">{{ item.label }}</span>
                    <span class="text-foreground font-medium tabular-nums">{{ formatNumber(item.value) }}</span>
                </li>
            </ul>
        </CardContent>
    </Card>
</template>
