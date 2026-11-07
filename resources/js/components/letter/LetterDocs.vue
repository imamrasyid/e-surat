<script setup lang="ts">
import LetterDocPreview from '@/components/letter/LetterDocPreview.vue';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { Item, ItemContent, ItemGroup, ItemMedia, ItemTitle } from '@/components/ui/item';
import pdfIcon from '@/assets/media/pdf.svg';
import pdfIconDark from '@/assets/media/pdf-dark.svg';
import type { LetterDoc } from '@/types';
import { ref } from 'vue';

defineProps<{
    docs: LetterDoc[];
}>();

const isPreviewOpen = ref(false);
const selectedDoc = ref<LetterDoc | null>(null);

const openPreview = (doc: LetterDoc) => {
    selectedDoc.value = doc;
    isPreviewOpen.value = true;
};
</script>

<template>
    <div class="mt-6">
        <Accordion type="single" collapsible default-value="docs">
            <AccordionItem value="docs">
                <AccordionTrigger class="cursor-pointer text-sm">Lampiran:</AccordionTrigger>
                <AccordionContent>
                    <ItemGroup v-if="docs.length > 0" class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 2xl:grid-cols-4">
                        <button
                            v-for="doc in docs"
                            :key="doc.id"
                            type="button"
                            class="w-full cursor-pointer text-left"
                            :title="`Pratinjau ${doc.name}`"
                            @click="openPreview(doc)"
                        >
                            <Item variant="outline" class="rounded-lg">
                                <ItemMedia variant="image">
                                    <img :src="pdfIcon" :alt="doc.name" class="size-12 dark:hidden" />
                                    <img :src="pdfIconDark" :alt="doc.name" class="hidden size-12 dark:block" />
                                </ItemMedia>
                                <ItemContent class="min-w-0 flex-1">
                                    <ItemTitle class="line-clamp-2 min-w-0 font-normal break-all">
                                        {{ doc.name }}
                                    </ItemTitle>
                                </ItemContent>
                            </Item>
                        </button>
                    </ItemGroup>
                    <p v-else class="text-muted-foreground text-sm">Belum ada lampiran.</p>
                </AccordionContent>
            </AccordionItem>
        </Accordion>

        <LetterDocPreview v-model:open="isPreviewOpen" :doc="selectedDoc" />
    </div>
</template>
