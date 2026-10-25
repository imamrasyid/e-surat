<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Combobox,
    ComboboxAnchor,
    ComboboxEmpty,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxList,
    ComboboxTrigger,
    ComboboxViewport,
} from '@/components/ui/combobox';
import { cn } from '@/lib/utils';
import { CheckIcon, ChevronDownIcon } from '@lucide/vue';

type SelectOption = { id: number; name: string };

withDefaults(
    defineProps<{
        id: string;
        options: SelectOption[];
        placeholder: string;
        tabindex?: number;
        searchPlaceholder?: string;
    }>(),
    {
        tabindex: undefined,
        searchPlaceholder: 'Cari...',
    },
);

const selected = defineModel<SelectOption | null>({ required: true });
</script>

<template>
    <Combobox v-model="selected" by="id">
        <ComboboxAnchor as-child>
            <ComboboxTrigger as-child>
                <Button
                    :id="id"
                    type="button"
                    variant="outline"
                    :tabindex="tabindex"
                    :class="cn('w-full justify-between font-normal', !selected && 'text-muted-foreground')"
                >
                    <span class="line-clamp-1 text-left">{{ selected?.name ?? placeholder }}</span>
                    <ChevronDownIcon class="text-muted-foreground" />
                </Button>
            </ComboboxTrigger>
        </ComboboxAnchor>
        <ComboboxList align="start" class="w-(--reka-combobox-trigger-width) min-w-(--reka-combobox-trigger-width)">
            <ComboboxInput :id="`search-${id}`" :placeholder="searchPlaceholder" />
            <ComboboxViewport class="max-h-65 w-full min-w-(--reka-combobox-trigger-width) overflow-y-auto overscroll-contain p-1">
                <ComboboxEmpty>Tidak ada data.</ComboboxEmpty>
                <ComboboxItem v-for="option in options" :key="option.id" :value="option">
                    {{ option.name }}
                    <ComboboxItemIndicator>
                        <CheckIcon />
                    </ComboboxItemIndicator>
                </ComboboxItem>
            </ComboboxViewport>
        </ComboboxList>
    </Combobox>
</template>
