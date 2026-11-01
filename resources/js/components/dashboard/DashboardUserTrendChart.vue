<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card';
import { type ChartConfig, ChartContainer, ChartCrosshair, ChartTooltip } from '@/components/ui/chart';
import type { UserChartSeriesItem } from '@/types/dashboard';
import { Clock } from '@lucide/vue';
import { VisAxis, VisLine, VisXYContainer } from '@unovis/vue';
import { computed, shallowRef, watch } from 'vue';
import DashboardChartHeader from './DashboardChartHeader.vue';
import { userChartSeries } from './userChartSeries';
import { formatDateLabel, formatDateTooltipLabel, formatNumber } from './utils';

interface Props {
    initialData: UserChartSeriesItem[];
    total: number;
}

const props = defineProps<Props>();

const selectedRange = shallowRef('90');
const series = shallowRef<UserChartSeriesItem[]>(props.initialData);
const isLoading = shallowRef(false);

const chartConfig = Object.fromEntries(userChartSeries.map((item) => [item.key, { label: item.label, color: item.color }])) satisfies ChartConfig;

const numTicks = computed(() => {
    switch (selectedRange.value) {
        case '7':
            return 7;
        case '30':
            return 10;
        default:
            return 12;
    }
});

function setRange(value: string): void {
    selectedRange.value = value;
}

function tooltipTemplate(d: UserChartSeriesItem): string {
    const rows = userChartSeries
        .map(
            (item) => `<div class="flex items-center gap-2">
                <span class="inline-block h-2.5 w-2.5 shrink-0 rounded-[2px]" style="background: ${item.color}"></span>
                <span class="text-muted-foreground">${item.label}</span>
                <span class="ml-auto font-mono font-medium tabular-nums text-foreground">${formatNumber(d[item.key])}</span>
            </div>`,
        )
        .join('');

    return `<div class="border-border/50 bg-background min-w-[160px] rounded-lg border px-3 py-2 text-xs shadow-xl">
        <div class="font-medium text-foreground mb-1">${formatDateTooltipLabel(d.date)}</div>
        <div class="space-y-1">${rows}</div>
    </div>`;
}

watch(selectedRange, async (days, _prev, onCleanup) => {
    const controller = new AbortController();
    onCleanup(() => controller.abort());

    isLoading.value = true;
    try {
        const response = await fetch(`/dashboard/user-chart-data?days=${days}`, {
            signal: controller.signal,
        });
        if (!response.ok) return;
        const payload: { series: UserChartSeriesItem[] } = await response.json();
        series.value = payload.series;
    } catch (error) {
        if (error instanceof DOMException && error.name === 'AbortError') {
            return;
        }
        console.error('Failed to load user chart data:', error);
    } finally {
        if (!controller.signal.aborted) {
            isLoading.value = false;
        }
    }
});
</script>

<template>
    <Card class="dark:bg-input/30 min-w-0 gap-0 py-0 shadow-none">
        <DashboardChartHeader
            title="Surat Diterima"
            description="Perbandingan utama, undangan, disposisi, dan nota dinas"
            total-label="Total Surat Diterima"
            :total="props.total"
            :range="selectedRange"
            @update:range="setRange"
        />
        <CardContent class="p-4 sm:p-6">
            <div v-if="isLoading" class="text-muted-foreground flex h-62.5 items-center justify-center gap-2" role="status" aria-live="polite">
                <Clock class="h-5 w-5" aria-hidden="true" />
                <span class="text-sm">Memuat data surat…</span>
            </div>
            <div v-else class="space-y-4">
                <ul class="flex flex-wrap gap-x-4 gap-y-2">
                    <li v-for="item in userChartSeries" :key="item.key" class="text-muted-foreground flex items-center gap-1.5 text-xs">
                        <span class="h-2.5 w-2.5 shrink-0 rounded-xs" :style="{ background: item.color }" aria-hidden="true"></span>
                        {{ item.label }}
                    </li>
                </ul>
                <ChartContainer
                    :config="chartConfig"
                    :cursor="true"
                    class="aspect-auto h-62.5 w-full min-w-0"
                    role="img"
                    aria-label="Diagram garis perbandingan utama, undangan, disposisi, dan nota dinas"
                >
                    <VisXYContainer :data="series" :y-domain="[0, undefined]">
                        <VisLine
                            v-for="item in userChartSeries"
                            :key="item.key"
                            :x="(_: UserChartSeriesItem, i: number) => i"
                            :y="(d: UserChartSeriesItem) => d[item.key]"
                            :color="item.color"
                            :line-width="2"
                        />
                        <VisAxis
                            type="x"
                            :tick-format="(i: number) => (series[i] ? formatDateLabel(series[i].date) : '')"
                            :num-ticks="numTicks"
                            :tick-line="false"
                            :domain-line="false"
                            :grid-line="false"
                        />
                        <VisAxis type="y" :num-ticks="3" :tick-line="false" :domain-line="false" />
                        <ChartCrosshair :template="tooltipTemplate" color="transparent" />
                        <ChartTooltip />
                    </VisXYContainer>
                </ChartContainer>
            </div>
        </CardContent>
    </Card>
</template>
