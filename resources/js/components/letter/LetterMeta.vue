<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { useFormattedDate } from '@/composables/useFormattedDate';
import type { Letter } from '@/types';
import { BadgeCheck } from '@lucide/vue';
import { computed } from 'vue';

const props = defineProps<{
    letter: Letter;
}>();

const { formatDate } = useFormattedDate();

const fields = computed(() => [
    { label: 'Nomor Agenda', value: props.letter.agenda },
    { label: 'Pengirim', value: props.letter.sender },
    { label: 'Nomor Surat', value: props.letter.number ?? '-' },
    { label: 'Perihal', value: props.letter.subject },
    { label: 'Tanggal Surat', value: formatDate(props.letter.date) },
    { label: 'Tanggal Diterima', value: formatDate(props.letter.receipt_date) },
]);
</script>

<template>
    <dl class="mt-6 space-y-2">
        <div v-for="field in fields" :key="field.label" class="flex items-start space-x-4 text-sm lg:space-x-12">
            <dt class="w-37 shrink-0 font-medium">{{ field.label }}</dt>
            <dd class="max-w-md wrap-break-word">{{ field.value }}</dd>
        </div>
        <div class="flex items-start space-x-4 text-sm lg:space-x-12">
            <dt class="w-37 shrink-0 font-medium">Status Surat</dt>
            <dd>
                <Badge v-if="letter.status === 'open'">Open</Badge>
                <Badge v-else variant="secondary" class="bg-blue-500 text-white dark:bg-blue-600"> <BadgeCheck /> Close </Badge>
            </dd>
        </div>
    </dl>
</template>
