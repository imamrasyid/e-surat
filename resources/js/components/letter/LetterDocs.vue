<script setup lang="ts">
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { Item, ItemContent, ItemGroup, ItemMedia, ItemTitle } from '@/components/ui/item';
import pdfIcon from '@/assets/media/pdf.svg';
import pdfIconDark from '@/assets/media/pdf-dark.svg';
import type { LetterDoc } from '@/types';

defineProps<{
    docs: LetterDoc[];
}>();
</script>

<template>
    <div class="mt-6">
        <Accordion type="single" collapsible default-value="docs">
            <AccordionItem value="docs">
                <AccordionTrigger class="cursor-pointer text-sm">Lampiran:</AccordionTrigger>
                <AccordionContent>
                    <ItemGroup v-if="docs.length > 0" class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 2xl:grid-cols-4">
                        <a v-for="doc in docs" :key="doc.id" :href="doc.url" target="_blank" :title="doc.name" rel="noopener noreferrer">
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
                        </a>
                    </ItemGroup>
                    <p v-else class="text-muted-foreground text-sm">Belum ada lampiran.</p>
                </AccordionContent>
            </AccordionItem>
        </Accordion>
    </div>
</template>
