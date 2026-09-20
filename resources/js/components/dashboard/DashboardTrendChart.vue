<script setup lang="ts">
import { Card, CardAction, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { type ChartConfig, ChartContainer, ChartCrosshair, ChartTooltip } from '@/components/ui/chart';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { ChartDataItem } from '@/types/dashboard';
import { Clock, TrendingUp } from '@lucide/vue';
import { CurveType } from '@unovis/ts';
import { VisArea, VisAxis, VisLine, VisXYContainer } from '@unovis/vue';
import { computed, shallowRef, watch } from 'vue';
import { formatDateLabel, formatDateTooltipLabel, formatNumber } from './utils';

interface Props {
    initialData: ChartDataItem[];
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
    <Card class="mt-4 shadow-none lg:mt-8">
        <CardHeader>
            <CardTitle class="flex items-center gap-2">
                <TrendingUp class="h-5 w-5 text-primary" />
                Tren Surat Masuk
            </CardTitle>
            <CardDescription>Grafik jumlah surat masuk berdasarkan waktu</CardDescription>
            <CardAction>
                <Select v-model="selectedRange">
                    <SelectTrigger class="w-[160px]">
                        <SelectValue placeholder="Pilih periode" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="90">3 Bulan Terakhir</SelectItem>
                        <SelectItem value="30">30 Hari Terakhir</SelectItem>
                        <SelectItem value="7">7 Hari Terakhir</SelectItem>
                    </SelectContent>
                </Select>
            </CardAction>
        </CardHeader>
        <CardContent>
            <div v-if="isLoading" class="flex h-[300px] items-center justify-center">
                <Clock class="h-8 w-8 animate-spin text-muted-foreground" />
            </div>
            <ChartContainer v-else :config="chartConfig" :cursor="true" class="h-[300px] w-full">
                <VisXYContainer :data="chartData" :padding="{ top: 10 }">
                    <VisArea
                        :x="(_: ChartDataItem, i: number) => i"
                        :y="(d: ChartDataItem) => d.surat_masuk"
                        color="var(--color-primary)"
                        :opacity="0.15"
                        :curve-type="CurveType.MonotoneX"
                    />
                    <VisLine
                        :x="(_: ChartDataItem, i: number) => i"
                        :y="(d: ChartDataItem) => d.surat_masuk"
                        color="var(--color-primary)"
                        :line-width="2"
                        :curve-type="CurveType.MonotoneX"
                    />
                    <VisAxis
                        type="x"
                        :tick-format="(i: number) => (chartData[i] ? formatDateLabel(chartData[i].date) : '')"
                        :num-ticks="numTicks"
                        :grid-line="false"
                    />
                    <VisAxis type="y" :grid-line="true" :num-ticks="5" />
                    <ChartCrosshair :template="tooltipTemplate" color="var(--color-primary)" />
                    <ChartTooltip />
                </VisXYContainer>
            </ChartContainer>
        </CardContent>
    </Card>
</template>
