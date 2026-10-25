<script setup lang="ts">
import { InputGroup, InputGroupAddon, InputGroupInput } from '@/components/ui/input-group';
import { Select, SelectContent, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { statusHelper } from '@/data/helpers';
import { Search } from '@lucide/vue';

withDefaults(
    defineProps<{
        searchId?: string;
        searchPlaceholder?: string;
    }>(),
    {
        searchId: 'search',
        searchPlaceholder: 'Cari',
    },
);

const search = defineModel<string>('search', { required: true });
const filter = defineModel<string>('filter', { required: true });
</script>

<template>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
        <InputGroup class="w-full sm:w-62.5">
            <InputGroupInput v-model="search" :id="searchId" aria-label="Cari surat" :placeholder="searchPlaceholder" autocomplete="off" />
            <InputGroupAddon>
                <Search aria-hidden="true" />
            </InputGroupAddon>
        </InputGroup>
        <Select v-model="filter">
            <SelectTrigger aria-label="Status Baca" class="w-full sm:w-35">
                <SelectValue placeholder="Status Baca" />
            </SelectTrigger>
            <SelectContent align="end">
                <SelectLabel>Status Baca</SelectLabel>
                <SelectItem v-for="item in statusHelper" :key="item.value" :value="item.value">
                    {{ item.name }}
                </SelectItem>
            </SelectContent>
        </Select>
    </div>
</template>
