<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card';
import { type ChartConfig, ChartContainer, ChartCrosshair, ChartTooltip } from '@/components/ui/chart';
import type { ChartDataItem } from '@/types/dashboard';
import { Clock } from '@lucide/vue';
import { VisAxis, VisGroupedBar, VisXYContainer } from '@unovis/vue';
import { computed, shallowRef, watch } from 'vue';
import DashboardChartHeader from './DashboardChartHeader.vue';
import { formatDateLabel, formatDateTooltipLabel, formatNumber } from './utils';

interface Props {
    initialData: ChartDataItem[];
    total: number;
}

const props = defineProps<Props>();

const selectedRange = shallowRef('90');
const chartData = shallowRef<ChartDataItem[]>(props.initialData);
const isLoading = shallowRef(false);

const chartConfig = {
    surat_masuk: {
        label: 'Surat Masuk',
        color: 'var(--color-primary)',
    },
} satisfies ChartConfig;

const barColor = 'var(--color-primary)';

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

function tooltipTemplate(d: ChartDataItem): string {
    return `<div class="border-border/50 bg-background min-w-[120px] rounded-lg border px-3 py-2 text-xs shadow-xl">
        <div class="font-medium text-foreground mb-1">${formatDateTooltipLabel(d.date)}</div>
        <div class="flex items-center gap-2">
            <span class="inline-block h-2.5 w-2.5 shrink-0 rounded-[2px]" style="background: var(--color-primary)"></span>
            <span class="text-muted-foreground">Surat Masuk</span>
            <span class="ml-auto font-mono font-medium tabular-nums text-foreground">${formatNumber(d.surat_masuk)}</span>
        </div>
    </div>`;
}

watch(selectedRange, async (days, _prev, onCleanup) => {
    const controller = new AbortController();
    onCleanup(() => controller.abort());

    isLoading.value = true;
    try {
        const response = await fetch(`/dashboard/chart-data?days=${days}`, {
            signal: controller.signal,
        });
        if (!response.ok) return;
        chartData.value = await response.json();
    } catch (error) {
        if (error instanceof DOMException && error.name === 'AbortError') {
            return;
        }
        console.error('Failed to load chart data:', error);
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
            title="Tren Surat Masuk"
            description="Grafik jumlah surat masuk berdasarkan waktu"
            total-label="Seluruh Surat Masuk"
            :total="props.total"
            :range="selectedRange"
            @update:range="setRange"
        />
        <CardContent class="p-4 sm:p-6">
            <div v-if="isLoading" class="text-muted-foreground flex h-62.5 items-center justify-center gap-2" role="status" aria-live="polite">
                <Clock class="h-5 w-5" aria-hidden="true" />
                <span class="text-sm">Memuat data tren surat…</span>
            </div>
            <ChartContainer
                v-else
                :config="chartConfig"
                :cursor="true"
                class="aspect-auto h-62.5 w-full min-w-0"
                role="img"
                aria-label="Diagram batang jumlah surat masuk berdasarkan waktu"
            >
                <VisXYContainer :data="chartData" :y-domain="[0, undefined]">
                    <VisGroupedBar
                        :x="(_: ChartDataItem, i: number) => i"
                        :y="(d: ChartDataItem) => d.surat_masuk"
                        :color="barColor"
                        :bar-padding="0.1"
                        :rounded-corners="false"
                    />
                    <VisAxis
                        type="x"
                        :tick-format="(i: number) => (chartData[i] ? formatDateLabel(chartData[i].date) : '')"
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
        </CardContent>
    </Card>
</template>
