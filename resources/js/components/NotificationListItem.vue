<script setup lang="ts">
import { Item, ItemActions, ItemContent, ItemDescription, ItemGroup, ItemMedia, ItemTitle } from '@/components/ui/item';
import { useFormattedDate } from '@/composables/useFormattedDate';
import { letterReceivedVia, letterSecurityClassification } from '@/data/helpers';
import type { NotificationItemType, NotificationListItem } from '@/types';
import { ChevronRight, Inbox, Mails, Radio, StickyNote } from '@lucide/vue';

withDefaults(
    defineProps<{
        notifications: NotificationListItem[];
        compact?: boolean;
    }>(),
    {
        compact: false,
    },
);

const emit = defineEmits<{
    open: [notification: NotificationListItem];
}>();

const { formatCreate } = useFormattedDate();

const mediaClasses: Record<NotificationItemType, string> = {
    primary: 'bg-chart-1/10 text-chart-1',
    invitation: 'bg-chart-2/10 text-chart-2',
    disposition: 'bg-chart-3/10 text-chart-3',
    memo: 'bg-chart-4/10 text-chart-4',
};

function labelOf(options: { value: string; name: string }[], value: string): string {
    return options.find((option) => option.value === value)?.name ?? '-';
}
</script>

<template>
    <div class="flex w-full flex-col gap-6">
        <ItemGroup>
            <template v-for="notification in notifications" :key="notification.id">
                <Item class="hover:bg-muted rounded-lg" as-child>
                    <button type="button" @click="emit('open', notification)" class="hover:cursor-pointer">
                        <ItemMedia variant="icon" :class="['rounded-full', mediaClasses[notification.item_type]]">
                            <Inbox v-if="notification.item_type === 'primary'" />
                            <Radio v-else-if="notification.item_type === 'invitation'" />
                            <Mails v-else-if="notification.item_type === 'disposition'" />
                            <StickyNote v-else />
                        </ItemMedia>
                        <ItemContent class="gap-1">
                            <div class="flex items-baseline justify-between gap-3">
                                <ItemTitle>
                                    <span v-if="notification.read_at === null" class="bg-primary mr-0.5 size-2 animate-pulse rounded-full" />
                                    {{ notification.title }}
                                </ItemTitle>
                                <p class="text-muted-foreground text-xs">{{ formatCreate(notification.created_at) }}</p>
                            </div>
                            <ItemDescription class="text-start text-wrap">{{ notification.body }}</ItemDescription>
                            <div v-if="!compact" class="mt-2 flex gap-8 text-start">
                                <dl class="flex flex-col gap-0.5 sm:flex-row sm:gap-2">
                                    <dt class="text-muted-foreground text-xs">Sifat Surat:</dt>
                                    <dd class="text-xs">
                                        {{ labelOf(letterSecurityClassification, notification.security_classification) }}
                                    </dd>
                                </dl>
                                <dl class="flex flex-col gap-0.5 sm:flex-row sm:gap-2">
                                    <dt class="text-muted-foreground text-xs">Sumber Penerimaan:</dt>
                                    <dd class="text-xs">
                                        {{ labelOf(letterReceivedVia, notification.received_via) }}
                                    </dd>
                                </dl>
                            </div>
                        </ItemContent>
                        <ItemActions>
                            <ChevronRight class="size-4" />
                        </ItemActions>
                    </button>
                </Item>
            </template>
        </ItemGroup>
    </div>
</template>
