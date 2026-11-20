<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { useFormattedDate } from '@/composables/useFormattedDate';
import { letterReceivedVia, letterSecurityClassification } from '@/data/helpers';
import type { NotificationListItem } from '@/types';
import { Trash2 } from '@lucide/vue';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        notification: NotificationListItem;
        compact?: boolean;
    }>(),
    {
        compact: false,
    },
);

const emit = defineEmits<{
    open: [notification: NotificationListItem];
    delete: [id: string];
}>();

const { formatCreate } = useFormattedDate();

function labelOf(options: { value: string; name: string }[], value: string): string {
    return options.find((option) => option.value === value)?.name ?? '-';
}

const classification = computed(() => labelOf(letterSecurityClassification, props.notification.security_classification));
const receivedVia = computed(() => labelOf(letterReceivedVia, props.notification.received_via));
const unread = computed(() => props.notification.read_at === null);
</script>

<template>
    <div class="group flex items-start gap-2 border-b last:border-b-0" :class="[compact ? 'px-3 py-2' : 'px-4 py-3', unread ? 'bg-muted/50' : '']">
        <button type="button" class="min-w-0 flex-1 text-left" @click="emit('open', notification)">
            <p class="truncate text-sm font-medium" :class="unread ? 'text-foreground' : 'text-muted-foreground'">
                {{ notification.title }}
            </p>
            <p class="text-muted-foreground mt-1 line-clamp-2 text-sm">{{ notification.body }}</p>
            <div class="text-muted-foreground mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs">
                <span>{{ formatCreate(notification.created_at) }}</span>
                <span>Sifat Surat: {{ classification }}</span>
                <span>Sumber Penerimaan: {{ receivedVia }}</span>
            </div>
        </button>

        <Button
            variant="ghost"
            size="icon-sm"
            class="shrink-0 opacity-100 focus-visible:opacity-100 sm:opacity-0 sm:group-hover:opacity-100"
            aria-label="Hapus pemberitahuan"
            @click="emit('delete', notification.id)"
        >
            <Trash2 />
        </Button>
    </div>
</template>
