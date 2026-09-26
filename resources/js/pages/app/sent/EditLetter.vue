<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { useClearErrorsOnChange } from '@/composables/useClearErrorsOnChange';
import { categoryInbox } from '@/data/helpers';
import AppLayout from '@/layouts/AppLayout.vue';
import { cn } from '@/lib/utils';
import type { BreadcrumbItem, Letter } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import type { DateValue } from '@internationalized/date';
import { DateFormatter, getLocalTimeZone, parseDate, today } from '@internationalized/date';
import { Calendar as CalendarIcon, ChevronLeft, Loader2, Save } from '@lucide/vue';
import { useDateFormat } from '@vueuse/core';
import { watch } from 'vue';

const props = defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    letter: Letter;
}>();

const df = new DateFormatter('en-US', {
    dateStyle: 'long',
});

function toDateValue(dateStr: string | null): DateValue | undefined {
    if (!dateStr) return undefined;
    try {
        return parseDate(dateStr.substring(0, 10));
    } catch {
        return undefined;
    }
}

const form = useForm({
    sender: props.letter.sender ?? '',
    number: props.letter.number ?? '',
    date: props.letter.date ? props.letter.date.substring(0, 10) : null,
    receipt_date: props.letter.receipt_date ? props.letter.receipt_date.substring(0, 10) : null,
    category: props.letter.category ? String(props.letter.category) : '',
    subject: props.letter.subject ?? '',
}) as ReturnType<
    typeof useForm<{
        sender: string;
        number: string;
        date: string | null;
        receipt_date: string | null;
        category: string;
        subject: string;
    }>
> & {
    tglSurat: DateValue | undefined;
    tglDiterima: DateValue | undefined;
};

form.tglSurat = toDateValue(props.letter.date);
form.tglDiterima = toDateValue(props.letter.receipt_date);

watch(
    () => form.tglSurat,
    (val) => {
        form.date = val ? val.toString() : null;
    },
);
watch(
    () => form.tglDiterima,
    (val) => {
        form.receipt_date = val ? val.toString() : null;
    },
);

useClearErrorsOnChange(form, { debounceMs: 120 });

const onSubmit = () => {
    form.put(route('sent.updateletter', props.letter.letter_id));
};
</script>

<template>
    <Head title="Perbarui Surat Terkirim" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6 xl:px-12">
            <div class="flex flex-col gap-4 sm:flex-row sm:justify-between">
                <HeadingSmall title="Perbarui Surat Terkirim" description="Perbarui data surat yang telah terkirim" />
                <div class="sm:text-end">
                    <p class="text-xs text-gray-500">
                        {{
                            useDateFormat(letter.created_at, 'dddd, DD MMM YYYY, HH.mm', {
                                locales: 'id-ID',
                            })
                        }}
                    </p>
                </div>
            </div>

            <Separator class="my-6" />

            <div class="flex items-center justify-between">
                <TooltipProvider>
                    <Tooltip>
                        <TooltipTrigger>
                            <Button
                                variant="outline"
                                size="icon"
                                class="cursor-pointer rounded-full"
                                @click="$inertia.get(route('sent.show', letter.letter_id))"
                            >
                                <ChevronLeft class="size-5" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>Kembali</p>
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider>
            </div>

            <form class="mt-6" @submit.prevent="onSubmit">
                <!-- Informasi Read-Only -->
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

                <Separator class="my-6" />

                <!-- Form Editable Fields -->
                <div class="space-y-8">
                    <!-- Pengirim & Nomor Surat -->
                    <div class="flex flex-col lg:flex-row lg:space-x-12">
                        <aside class="w-full max-w-xl lg:w-[250px] xl:w-[350px] 2xl:w-md">
                            <HeadingSmall title="Pengirim" description="Perbarui pengirim dan nomor surat" />
                        </aside>

                        <Separator class="my-6 lg:hidden" />

                        <div class="flex-1 md:max-w-2xl">
                            <section class="max-w-2xl space-y-6 lg:flex lg:flex-auto lg:space-y-0 lg:space-x-6">
                                <div class="grid gap-2 lg:basis-1/2">
                                    <Label for="sender">Pengirim</Label>
                                    <Input
                                        id="sender"
                                        type="text"
                                        autofocus
                                        :tabindex="1"
                                        autocomplete="off"
                                        v-model="form.sender"
                                        placeholder="Nama pengirim surat"
                                        class="h-10"
                                    />
                                    <InputError :message="form.errors.sender" />
                                </div>
                                <div class="grid gap-2 lg:basis-1/2">
                                    <Label for="number">Nomor Surat</Label>
                                    <Input
                                        id="number"
                                        type="text"
                                        :tabindex="2"
                                        autocomplete="off"
                                        v-model="form.number"
                                        placeholder="Nomor surat"
                                        class="h-10"
                                    />
                                    <InputError :message="form.errors.number" />
                                </div>
                            </section>
                        </div>
                    </div>

                    <Separator class="hidden lg:block" />

                    <!-- Tanggal Surat & Tanggal Diterima -->
                    <div class="flex flex-col lg:flex-row lg:space-x-12">
                        <aside class="w-full max-w-xl lg:w-[250px] xl:w-[350px] 2xl:w-md">
                            <HeadingSmall title="Tanggal" description="Perbarui tanggal surat dan tanggal diterima" />
                        </aside>

                        <Separator class="my-6 lg:hidden" />

                        <div class="flex-1 md:max-w-2xl">
                            <section class="max-w-2xl space-y-6 lg:flex lg:flex-auto lg:space-y-0 lg:space-x-6">
                                <div class="grid gap-2 lg:basis-1/2">
                                    <Label for="tglsurat">Tanggal Surat</Label>
                                    <Popover>
                                        <PopoverTrigger as-child>
                                            <Button
                                                id="tglsurat"
                                                variant="outline"
                                                :tabindex="3"
                                                :class="
                                                    cn(
                                                        'h-10 max-w-[200px] justify-start text-left font-normal',
                                                        !form.tglSurat && 'text-muted-foreground',
                                                    )
                                                "
                                            >
                                                <CalendarIcon class="mr-2 h-4 w-4" />
                                                {{ form.tglSurat ? df.format(form.tglSurat.toDate(getLocalTimeZone())) : 'Pilih tanggal' }}
                                            </Button>
                                        </PopoverTrigger>
                                        <PopoverContent class="w-auto p-0">
                                            <Calendar v-model="form.tglSurat" initial-focus :max-value="today(getLocalTimeZone())" />
                                        </PopoverContent>
                                    </Popover>
                                    <InputError :message="form.errors.date" />
                                </div>
                                <div class="grid gap-2 lg:basis-1/2">
                                    <Label for="tglditerima">Tanggal Diterima</Label>
                                    <Popover>
                                        <PopoverTrigger as-child>
                                            <Button
                                                id="tglditerima"
                                                variant="outline"
                                                :tabindex="4"
                                                :class="
                                                    cn(
                                                        'h-10 max-w-[200px] justify-start text-left font-normal',
                                                        !form.tglDiterima && 'text-muted-foreground',
                                                    )
                                                "
                                            >
                                                <CalendarIcon class="mr-2 h-4 w-4" />
                                                {{ form.tglDiterima ? df.format(form.tglDiterima.toDate(getLocalTimeZone())) : 'Pilih tanggal' }}
                                            </Button>
                                        </PopoverTrigger>
                                        <PopoverContent class="w-auto p-0">
                                            <Calendar v-model="form.tglDiterima" initial-focus :max-value="today(getLocalTimeZone())" />
                                        </PopoverContent>
                                    </Popover>
                                    <InputError :message="form.errors.receipt_date" />
                                </div>
                            </section>
                        </div>
                    </div>

                    <Separator class="hidden lg:block" />

                    <!-- Kategori -->
                    <div class="flex flex-col lg:flex-row lg:space-x-12">
                        <aside class="w-full max-w-xl lg:w-[250px] xl:w-[350px] 2xl:w-md">
                            <HeadingSmall title="Kategori" description="Perbarui kategori surat" />
                        </aside>

                        <Separator class="my-6 lg:hidden" />

                        <div class="flex-1 md:max-w-2xl">
                            <section class="max-w-2xl">
                                <div class="grid gap-2 lg:max-w-[200px]">
                                    <Label for="category">Kategori</Label>
                                    <Select v-model="form.category">
                                        <SelectTrigger id="category" :tabindex="5" aria-label="Select kategori" class="dark:bg-input/30">
                                            <SelectValue placeholder="Pilih kategori" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectItem v-for="item in categoryInbox" :key="item.value" :value="String(item.value)">
                                                    {{ item.name }}
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.category" />
                                </div>
                            </section>
                        </div>
                    </div>

                    <Separator class="hidden lg:block" />

                    <!-- Perihal -->
                    <div class="flex flex-col lg:flex-row lg:space-x-12">
                        <aside class="w-full max-w-xl lg:w-[250px] xl:w-[350px] 2xl:w-md">
                            <HeadingSmall title="Perihal" description="Perbarui perihal isi surat" />
                        </aside>

                        <Separator class="my-6 lg:hidden" />

                        <div class="flex-1 md:max-w-2xl">
                            <section class="max-w-2xl">
                                <div class="grid gap-2">
                                    <Label for="subject">Perihal</Label>
                                    <Textarea
                                        id="subject"
                                        :tabindex="6"
                                        v-model="form.subject"
                                        placeholder="Perihal isi surat"
                                        class="dark:bg-input/30"
                                    />
                                    <InputError :message="form.errors.subject" />
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

                <Separator class="my-6" />

                <div class="flex justify-end">
                    <Button type="submit" :disabled="form.processing || !form.isDirty" class="cursor-pointer">
                        <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                        <Save v-else class="h-4 w-4" />
                        Simpan Perubahan
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
