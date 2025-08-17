<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { categoryInbox } from '@/data/helpers';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { now, useDateFormat } from '@vueuse/core';
import { ChevronLeft, Printer, Reply } from 'lucide-vue-next';

const props = defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    from: any;
    memo: any;
    recipients: any;
    letter: any;
}>();

function getCategoryName(value: number) {
    const found = categoryInbox.find((cat) => cat.value === value);
    return found ? found.name : '-';
}

const createMemo = (letter_id: string) => {
    const key = btoa(String(now().toPrecision(21)) + letter_id);
    router.get(
        route('memo.create', [props.memo.letter_recipient_id, key]),
        {},
        {
            onSuccess: () => {
                sessionStorage.setItem('dsp_c_helper', 'memo');
            },
            preserveState: true,
        },
    );
};
</script>

<template>
    <Head title="Lihat Nota Dinas Masuk" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6 xl:px-12">
            <div class="flex flex-col gap-4 sm:flex-row sm:justify-between">
                <HeadingSmall title="Nota Dinas" description="Detail data nota dinas masuk" />
                <div class="sm:text-end">
                    <p class="text-xs text-gray-500">
                        {{ useDateFormat(memo.received_at, 'dddd, DD MMM YYYY, HH.mm', { locales: 'id-ID' }) }}
                    </p>
                    <h6 class="text-sm">Oleh {{ from.name }}</h6>
                </div>
            </div>

            <Separator class="my-6" />

            <div class="flex items-center justify-between">
                <TooltipProvider>
                    <Tooltip>
                        <TooltipTrigger>
                            <Button variant="outline" size="icon" class="cursor-pointer rounded-full" @click="$inertia.get(route('memo.index'))">
                                <ChevronLeft class="size-5" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>Kembali</p>
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider>
                <div class="flex gap-2">
                    <Button type="button" variant="ghost" size="icon" class="cursor-pointer">
                        <Printer class="size-5" />
                    </Button>
                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    class="cursor-pointer"
                                    :disabled="memo.is_cc === 1 || letter.status === 'close'"
                                    @click="createMemo(letter.letter_id)"
                                >
                                    <Reply class="size-5" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>Buat Nota Dinas</p>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                </div>
            </div>

            <div class="mt-6">
                <Accordion type="single" collapsible>
                    <AccordionItem value="item-1">
                        <AccordionTrigger class="cursor-pointer text-sm">Detail Surat:</AccordionTrigger>
                        <AccordionContent>
                            <div class="space-y-2">
                                <div class="flex space-x-4 lg:space-x-12">
                                    <aside class="w-full max-w-[150px] sm:max-w-[150px] lg:w-[150px]">
                                        <h6 class="text-sm font-medium">Nomor Agenda</h6>
                                    </aside>

                                    <div class="flex-1">
                                        <h6 class="text-sm dark:text-gray-300">{{ letter.agenda }}</h6>
                                    </div>
                                </div>

                                <div class="flex space-x-4 lg:space-x-12">
                                    <aside class="w-full max-w-[150px] sm:max-w-[150px] lg:w-[150px]">
                                        <h6 class="text-sm font-medium">Pengirim</h6>
                                    </aside>

                                    <div class="flex-1">
                                        <h6 class="text-sm dark:text-gray-300">{{ letter.sender }}</h6>
                                    </div>
                                </div>

                                <div class="flex space-x-4 lg:space-x-12">
                                    <aside class="w-full max-w-[150px] sm:max-w-[150px] lg:w-[150px]">
                                        <h6 class="text-sm font-medium">Nomor Surat</h6>
                                    </aside>

                                    <div class="flex-1">
                                        <h6 class="text-sm dark:text-gray-300">{{ letter.number }}</h6>
                                    </div>
                                </div>

                                <div class="flex space-x-4 lg:space-x-12">
                                    <aside class="w-full max-w-[150px] sm:max-w-[150px] lg:w-[150px]">
                                        <h6 class="text-sm font-medium">Perihal</h6>
                                    </aside>

                                    <div class="flex-1 sm:max-w-md">
                                        <h6 class="text-sm dark:text-gray-300">{{ letter.subject }}</h6>
                                    </div>
                                </div>

                                <div class="flex space-x-4 lg:space-x-12">
                                    <aside class="w-full max-w-[150px] sm:max-w-[150px] lg:w-[150px]">
                                        <h6 class="text-sm font-medium">Tanggal Surat</h6>
                                    </aside>

                                    <div class="flex-1">
                                        <h6 class="text-sm dark:text-gray-300">
                                            {{ useDateFormat(letter.date, 'DD MMMM YYYY', { locales: 'id-ID' }) }}
                                        </h6>
                                    </div>
                                </div>

                                <div class="flex space-x-4 lg:space-x-12">
                                    <aside class="w-full max-w-[150px] sm:max-w-[150px] lg:w-[150px]">
                                        <h6 class="text-sm font-medium">Tanggal Diterima</h6>
                                    </aside>

                                    <div class="flex-1">
                                        <h6 class="text-sm dark:text-gray-300">
                                            {{ useDateFormat(letter.receipt_date, 'DD MMMM YYYY', { locales: 'id-ID' }) }}
                                        </h6>
                                    </div>
                                </div>

                                <div class="flex space-x-4 lg:space-x-12">
                                    <aside class="w-full max-w-[150px] sm:max-w-[150px] lg:w-[150px]">
                                        <h6 class="text-sm font-medium">Kategori</h6>
                                    </aside>

                                    <div class="flex-1">
                                        <h6 class="text-sm dark:text-gray-300">{{ getCategoryName(Number(letter.category)) }}</h6>
                                    </div>
                                </div>

                                <div class="flex space-x-4 lg:space-x-12">
                                    <aside class="w-full max-w-[150px] sm:max-w-[150px] lg:w-[150px]">
                                        <h6 class="text-sm font-medium">Status Surat</h6>
                                    </aside>

                                    <div class="flex-1">
                                        <span
                                            v-if="letter.status === 'open'"
                                            class="inline-block rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-500 dark:bg-gray-800"
                                        >
                                            Open
                                        </span>
                                        <span
                                            v-else
                                            class="inline-block rounded-full bg-indigo-100 px-3 py-1 text-sm font-medium text-indigo-500 dark:bg-gray-800"
                                        >
                                            Close
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </AccordionContent>
                    </AccordionItem>
                </Accordion>
            </div>

            <div class="mt-6">
                <Accordion default-value="item-1" type="single" collapsible>
                    <AccordionItem value="item-1">
                        <AccordionTrigger class="cursor-pointer text-sm">Timeline:</AccordionTrigger>
                        <AccordionContent>
                            <div class="rounded-md border">
                                <Table>
                                    <TableHeader class="bg-muted/30">
                                        <TableRow class="hover:bg-transparent">
                                            <TableHead class="min-w-[75px]">Tgl Kirim</TableHead>
                                            <TableHead class="min-w-[175px]">Dari</TableHead>
                                            <TableHead class="min-w-[175px]">Kepada</TableHead>
                                            <TableHead class="min-w-[250px]">Catatan</TableHead>
                                            <TableHead class="min-w-[175px]">Followup</TableHead>
                                            <TableHead class="min-w-[75px]">Tgl Baca</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="item in recipients" :key="item.id">
                                            <TableCell class="align-top">
                                                {{ useDateFormat(item.received_at, 'DD/MM/YY, HH:mm', { locales: 'id-ID' }) }}
                                            </TableCell>
                                            <TableCell class="align-top">{{ item.from_position.name }}</TableCell>
                                            <TableCell class="align-top">
                                                {{ item.is_cc === 1 ? 'cc: ' : '' }} {{ item.to_position.name }}
                                            </TableCell>
                                            <TableCell class="align-top">{{ item.instruction ?? '-' }}</TableCell>
                                            <TableCell class="align-top">{{ item.followup ?? '-' }}</TableCell>
                                            <TableCell class="align-top">
                                                {{ item.read_at ? useDateFormat(item.read_at, 'DD/MM/YY, HH:mm', { locales: 'id-ID' }) : '-' }}
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                        </AccordionContent>
                    </AccordionItem>
                </Accordion>
            </div>

            <div class="mt-6">
                <Accordion default-value="item-1" type="single" collapsible>
                    <AccordionItem value="item-1">
                        <AccordionTrigger class="cursor-pointer text-sm">Lampiran:</AccordionTrigger>
                        <AccordionContent>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6">
                                <a :href="item.url" target="_blank" v-for="(item, index) in letter.docs" :key="index">
                                    <Card class="cursor-pointer py-4">
                                        <CardContent class="px-4">
                                            <picture class="flex justify-center">
                                                <img class="flex size-20 dark:hidden" src="../../../assets/media/pdf.svg" alt="pdf icon" />
                                                <img class="hidden size-20 dark:flex" src="../../../assets/media/pdf-dark.svg" alt="pdf icon" />
                                            </picture>
                                            <p class="mt-2 line-clamp-1 text-center text-sm">{{ item.name }}</p>
                                        </CardContent>
                                    </Card>
                                </a>
                            </div>
                        </AccordionContent>
                    </AccordionItem>
                </Accordion>
            </div>
        </div>
    </AppLayout>
</template>
