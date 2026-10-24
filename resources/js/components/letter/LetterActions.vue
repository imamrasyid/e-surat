<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import type { Letter } from '@/types';
import { router } from '@inertiajs/vue3';
import { Forward, Reply } from '@lucide/vue';
import { now } from '@vueuse/core';
import { computed } from 'vue';

const props = defineProps<{
    letter: Letter;
    recipient: number;
    context: string;
}>();

const isClosed = computed(() => props.letter.status === 'close');

function createFollowUp(routeName: 'memo.create' | 'dispo.create') {
    const key = btoa(String(now().toPrecision(21)) + props.letter.letter_id);

    sessionStorage.setItem('dsp_c_helper', props.context);
    router.get(route(routeName, [props.recipient, key]));
}
</script>

<template>
    <TooltipProvider>
        <div class="flex gap-2">
            <Tooltip>
                <TooltipTrigger as-child>
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        class="cursor-pointer rounded-full"
                        aria-label="Buat Nota Dinas"
                        :disabled="isClosed"
                        @click="createFollowUp('memo.create')"
                    >
                        <Reply class="size-5" />
                    </Button>
                </TooltipTrigger>
                <TooltipContent>
                    <p>Buat Nota Dinas</p>
                </TooltipContent>
            </Tooltip>
            <Tooltip>
                <TooltipTrigger as-child>
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        class="cursor-pointer rounded-full"
                        aria-label="Buat Disposisi"
                        :disabled="isClosed"
                        @click="createFollowUp('dispo.create')"
                    >
                        <Forward class="size-5" />
                    </Button>
                </TooltipTrigger>
                <TooltipContent>
                    <p>Buat Disposisi</p>
                </TooltipContent>
            </Tooltip>
        </div>
    </TooltipProvider>
</template>
