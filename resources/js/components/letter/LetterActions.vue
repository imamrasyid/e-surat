<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import type { Letter } from '@/types';
import { router } from '@inertiajs/vue3';
import { Forward, Reply } from '@lucide/vue';
import { now } from '@vueuse/core';
import { computed } from 'vue';

type FollowUpAction = 'memo' | 'dispo';

const props = withDefaults(
    defineProps<{
        letter: Letter;
        recipient: number;
        context: string;
        disabled?: boolean;
        actions?: FollowUpAction[];
    }>(),
    {
        disabled: false,
        actions: () => ['memo', 'dispo'] as FollowUpAction[],
    },
);

const isDisabled = computed(() => props.disabled || props.letter.status === 'close');

const buttons = computed(() =>
    props.actions.map((action) => ({
        action,
        route: action === 'memo' ? ('memo.create' as const) : ('dispo.create' as const),
        label: action === 'memo' ? 'Buat Nota Dinas' : 'Buat Disposisi',
        icon: action === 'memo' ? Reply : Forward,
    })),
);

function createFollowUp(routeName: 'memo.create' | 'dispo.create') {
    const key = btoa(String(now().toPrecision(21)) + props.letter.letter_id);

    sessionStorage.setItem('dsp_c_helper', props.context);
    router.get(route(routeName, [props.recipient, key]));
}
</script>

<template>
    <TooltipProvider>
        <div class="flex gap-2">
            <Tooltip v-for="button in buttons" :key="button.action">
                <TooltipTrigger as-child>
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        class="cursor-pointer rounded-full"
                        :aria-label="button.label"
                        :disabled="isDisabled"
                        @click="createFollowUp(button.route)"
                    >
                        <component :is="button.icon" class="size-5" />
                    </Button>
                </TooltipTrigger>
                <TooltipContent>
                    <p>{{ button.label }}</p>
                </TooltipContent>
            </Tooltip>
        </div>
    </TooltipProvider>
</template>
