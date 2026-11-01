<script setup lang="ts">
import { CardDescription, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { formatNumber } from './utils';

const props = defineProps<{
    title: string;
    description: string;
    totalLabel: string;
    total: number;
    range: string;
}>();

const emit = defineEmits<{
    'update:range': [value: string];
}>();
</script>

<template>
    <div class="flex flex-col items-stretch border-b sm:flex-row">
        <div class="flex min-w-0 flex-1 flex-col justify-center gap-1 px-6 py-5 sm:py-6">
            <CardTitle>{{ props.title }}</CardTitle>
            <CardDescription>{{ props.description }}</CardDescription>
        </div>
        <div class="flex flex-col sm:flex-row">
            <div class="flex min-w-0 flex-1 flex-col justify-center gap-1 border-t px-6 py-4 sm:border-t-0 sm:border-l sm:px-8 sm:py-6">
                <span class="text-muted-foreground text-xs">{{ props.totalLabel }}</span>
                <span class="text-lg leading-none font-bold sm:text-3xl">{{ formatNumber(props.total) }}</span>
            </div>
            <div class="flex flex-col gap-1 border-t px-6 py-4 sm:border-t-0 sm:border-l sm:px-8 sm:py-6">
                <span class="text-muted-foreground text-xs">Filter</span>
                <Select :model-value="props.range" @update:model-value="(value) => emit('update:range', String(value))">
                    <SelectTrigger class="min-w-40" :aria-label="`Pilih periode ${props.title.toLowerCase()}`">
                        <SelectValue placeholder="Pilih periode" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="90">3 Bulan Terakhir</SelectItem>
                        <SelectItem value="30">30 Hari Terakhir</SelectItem>
                        <SelectItem value="7">7 Hari Terakhir</SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>
    </div>
</template>
