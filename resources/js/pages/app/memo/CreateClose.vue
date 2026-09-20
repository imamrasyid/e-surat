<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { Button } from '@/components/ui/button';
import Label from '@/components/ui/label/Label.vue';
import Separator from '@/components/ui/separator/Separator.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from '@/components/ui/toast';
import { useClearErrorsOnChange } from '@/composables/useClearErrorsOnChange';
import { categoryInbox } from '@/data/helpers';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Letter, Memo, TimelineRecipient } from '@/types';
import { Head, InertiaForm, useForm } from '@inertiajs/vue3';
import { Loader2, Send } from '@lucide/vue';
import { useDateFormat } from '@vueuse/core';

const { toast } = useToast();

const props = defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    letter: Letter;
    recipients: TimelineRecipient[];
    memo: Memo;
}>();

type FormInput = {
    letter_id_primary: number;
    letter_recipient_id: number;
    to_position_id: number;
    catatan: string;
};

const form: InertiaForm<FormInput> = useForm<FormInput>({
    letter_id_primary: Number(props.letter.id),
    letter_recipient_id: Number(props.memo.letter_recipient_id),
    to_position_id: Number(props.memo.from_position_id),
    catatan: '',
});
useClearErrorsOnChange(form, { debounceMs: 120 });

function getCategoryName(value: number) {
    const found = categoryInbox.find((cat) => cat.value === value);
    return found ? found.name : '-';
}

const onSubmit = () => {
    form.post(route('memo.store.close'), {
        onSuccess: () => {
            form.reset();
            toast({
                title: 'Success! Data terkirim.',
                description: 'Surat masuk berhasil ditandai sebagai selesai.',
                variant: 'success',
            });
        },
        onError: (errors: any) => {
            if (errors.confirm) {
                toast({
                    title: 'Uh oh! Something went wrong.',
                    description: errors.confirm,
                    variant: 'destructive',
                });
            } else {
                toast({
                    title: 'Uh oh! Something went wrong.',
                    description: 'Sepertinya data belum lengkap',
                    variant: 'warning',
                });
            }
        },
    });
};
</script>

<template>
    <Head title="Buat Penutupan Surat Masuk" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6 xl:px-12">
            <Heading title="Penutupan" description="Buat penutupan surat masuk" />

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
                        </div>
                        <div class="mt-4 rounded-md border">
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
                                    <TableRow v-for="(item, index) in recipients" :key="index">
                                        <TableCell class="align-top">
                                            {{ item.received_at ? useDateFormat(item.received_at, 'DD/MM/YY, HH:mm', { locales: 'id-ID' }) : '' }}
                                        </TableCell>
                                        <TableCell class="align-top">{{ item.from_position.name }}</TableCell>
                                        <TableCell class="align-top"> {{ item.is_cc ? 'cc: ' : '' }} {{ item.to_position.name }} </TableCell>
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

            <form class="my-12 space-y-8" @submit.prevent="onSubmit">
                <div class="flex flex-col lg:flex-row lg:space-x-12">
                    <aside class="w-full max-w-xl lg:w-[250px] xl:w-[350px] 2xl:w-md">
                        <HeadingSmall title="Ditujukan" description="Isi catatan penutupan surat masuk" />
                    </aside>

                    <Separator class="my-6 lg:hidden" />

                    <div class="flex-1 md:max-w-2xl">
                        <div class="grid gap-2">
                            <Label for="catatan">Catatan</Label>
                            <Textarea
                                id="catatan"
                                :tabindex="1"
                                v-model="form.catatan"
                                placeholder="Masukkan catatan (Opsional)"
                                class="dark:bg-input/30"
                            />
                            <InputError :message="form.errors.catatan" />
                        </div>
                    </div>
                </div>

                <div class="justify-self-end-safe">
                    <Button type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        <Send v-else class="h-4 w-4" />
                        Simpan
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
