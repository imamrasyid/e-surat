<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { useFormattedDate } from '@/composables/useFormattedDate';
import { categoryInbox, letterReceivedVia, letterSecurityClassification } from '@/data/helpers';
import type { Letter } from '@/types';
import { BadgeCheck } from '@lucide/vue';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        letter: Letter;
        showCategory?: boolean;
    }>(),
    {
        showCategory: false,
    },
);

const { formatDate } = useFormattedDate();

function labelOf(options: { value: string; name: string }[], value?: string | null): string {
    return options.find((option) => option.value === value)?.name ?? '-';
}

const fields = computed(() => {
    const items = [
        { label: 'Nomor Agenda', value: props.letter.agenda },
        { label: 'Pengirim', value: props.letter.sender },
        { label: 'Nomor Surat', value: props.letter.number ?? '-' },
        { label: 'Perihal', value: props.letter.subject },
        { label: 'Tanggal Surat', value: formatDate(props.letter.date) },
        { label: 'Tanggal Diterima', value: formatDate(props.letter.receipt_date) },
    ];

    if (props.showCategory) {
        items.push({
            label: 'Kategori',
            value: categoryInbox.find((category) => category.value === props.letter.category)?.name ?? '-',
        });
    }

    items.push(
        {
            label: 'Sifat Surat',
            value: labelOf(letterSecurityClassification, props.letter.security_classification),
        },
        {
            label: 'Sumber Penerimaan',
            value: labelOf(letterReceivedVia, props.letter.received_via),
        },
    );

    return items;
});
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
                <Badge v-if="letter.status === 'open'">Terbuka</Badge>
                <Badge v-else variant="secondary" class="bg-blue-500 text-white dark:bg-blue-600"> <BadgeCheck /> Selesai </Badge>
            </dd>
        </div>
    </dl>
</template>
