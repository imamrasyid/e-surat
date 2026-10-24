<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { usePermissions } from '@/composables/usePermissions';
import type { Letter, Memo } from '@/types';
import { router } from '@inertiajs/vue3';
import { BookOpenCheck } from '@lucide/vue';
import { computed } from 'vue';

const props = defineProps<{
    memo: Memo;
    letter: Letter;
}>();

const { can } = usePermissions();

const isDisabled = computed(() => !can('memo.close') || props.memo.is_cc || props.letter.status === 'close');

function closeMemo() {
    router.get(route('memo.create.close', [props.memo.id, props.letter.letter_id]));
}
</script>

<template>
    <TooltipProvider>
        <Tooltip>
            <TooltipTrigger as-child>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="cursor-pointer rounded-full"
                    aria-label="Tandai sebagai selesai"
                    :disabled="isDisabled"
                    @click="closeMemo"
                >
                    <BookOpenCheck class="size-5" />
                </Button>
            </TooltipTrigger>
            <TooltipContent>
                <p>Tandai Sebagai Selesai</p>
            </TooltipContent>
        </Tooltip>
    </TooltipProvider>
</template>
