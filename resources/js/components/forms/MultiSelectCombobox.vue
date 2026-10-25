<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Combobox,
    ComboboxAnchor,
    ComboboxEmpty,
    ComboboxInput,
    ComboboxItem,
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

const selected = defineModel<SelectOption[]>({ required: true });
</script>

<template>
    <Combobox v-model="selected" multiple by="id">
        <ComboboxAnchor as-child>
            <ComboboxTrigger as-child>
                <Button
                    :id="id"
                    type="button"
                    variant="outline"
                    :tabindex="tabindex"
                    :class="cn('w-full justify-between font-normal whitespace-normal', selected.length === 0 && 'text-muted-foreground')"
                >
                    <span class="line-clamp-1 text-left">
                        {{ selected.length > 0 ? selected.map((item) => item.name).join(', ') : placeholder }}
                    </span>
                    <ChevronDownIcon class="text-muted-foreground" />
                </Button>
            </ComboboxTrigger>
        </ComboboxAnchor>
        <ComboboxList align="start" class="w-(--reka-combobox-trigger-width) min-w-(--reka-combobox-trigger-width)">
            <ComboboxInput :id="`search-${id}`" :placeholder="searchPlaceholder" />
            <ComboboxViewport class="max-h-65 w-full min-w-(--reka-combobox-trigger-width) overflow-y-auto overscroll-contain p-1">
                <ComboboxEmpty>Tidak ada data.</ComboboxEmpty>
                <ComboboxItem v-for="option in options" :key="option.id" :value="option">
                    <div
                        class="border-input data-[selected=true]:border-primary data-[selected=true]:bg-primary data-[selected=true]:text-primary-foreground pointer-events-none size-4 shrink-0 rounded-lg border transition-all select-none *:[svg]:opacity-0 data-[selected=true]:*:[svg]:opacity-100"
                        :data-selected="selected.some((item) => item.id === option.id)"
                    >
                        <CheckIcon class="size-3.5 text-current" />
                    </div>
                    {{ option.name }}
                </ComboboxItem>
            </ComboboxViewport>
        </ComboboxList>
    </Combobox>
</template>
