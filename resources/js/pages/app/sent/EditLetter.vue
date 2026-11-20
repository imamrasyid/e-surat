<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
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
import { BadgeCheck, Calendar as CalendarIcon, ChevronLeft, Loader2, Save } from '@lucide/vue';
import { onMounted, ref, watch } from 'vue';

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

const hasHistory = ref(false);
const goBack = () => {
    window.history.back();
};

onMounted(() => {
    hasHistory.value = window.history.length > 1;
    form.tglSurat = toDateValue(props.letter.date);
    form.tglDiterima = toDateValue(props.letter.receipt_date);
});
</script>

<template>
    <Head title="Perbarui Surat Terkirim" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6 xl:px-12">
            <div class="flex items-center gap-4">
                <TooltipProvider v-if="hasHistory">
                    <Tooltip>
                        <TooltipTrigger>
                            <Button variant="outline" size="icon" class="cursor-pointer rounded-full" @click="goBack()">
                                <ChevronLeft class="size-5" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>Kembali</p>
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider>
                <Heading class="mb-0!" title="Perbarui Surat Terkirim" description="Perbarui data surat yang telah terkirim" />
            </div>

            <dl class="mt-8 space-y-2">
                <div class="flex items-start space-x-4 text-sm lg:space-x-12">
                    <dt class="w-37 shrink-0 font-medium">Nomor Agenda</dt>
                    <dd class="max-w-md wrap-break-word">{{ letter.agenda }}</dd>
                </div>
                <div class="flex items-start space-x-4 text-sm lg:space-x-12">
                    <dt class="w-37 shrink-0 font-medium">Status Surat</dt>
                    <Badge v-if="letter.status === 'open'"> Terbuka </Badge>
                    <Badge v-else variant="secondary" class="bg-blue-500 text-white dark:bg-blue-600"> <BadgeCheck /> Selesai </Badge>
                </div>
            </dl>

            <Separator class="mt-6" />

            <form class="mt-6" @submit.prevent="onSubmit">
                <div class="space-y-8">
                    <div class="flex flex-col lg:flex-row lg:space-x-12">
                        <aside class="w-full max-w-xl lg:w-62.5 xl:w-87.5 2xl:w-md">
                            <HeadingSmall title="Pengirim" description="Masukkan pengirim dan nomor surat" />
                        </aside>

                        <Separator class="my-6 lg:hidden" />

                        <div class="flex-1 md:max-w-2xl">
                            <section class="max-w-2xl items-start space-y-6 lg:flex lg:flex-auto lg:space-y-0 lg:space-x-6">
                                <div class="grid gap-2 lg:basis-1/2">
                                    <Label for="pengirim">Pengirim</Label>
                                    <Input
                                        id="pengirim"
                                        type="text"
                                        autofocus
                                        :tabindex="1"
                                        autocomplete="off"
                                        v-model="form.sender"
                                        placeholder="Nama pengirim surat"
                                    />
                                    <InputError :message="form.errors.sender" />
                                </div>
                                <div class="grid gap-2 lg:basis-1/2">
                                    <Label for="nomor">Nomor</Label>
                                    <Input
                                        id="nomor"
                                        type="text"
                                        :tabindex="2"
                                        autocomplete="off"
                                        v-model="form.number"
                                        placeholder="Nomor surat masuk (Opsional)"
                                    />
                                    <InputError :message="form.errors.number" />
                                </div>
                            </section>
                        </div>
                    </div>

                    <Separator class="hidden lg:block" />

                    <div class="flex flex-col lg:flex-row lg:space-x-12">
                        <aside class="w-full max-w-xl lg:w-62.5 xl:w-87.5 2xl:w-md">
                            <HeadingSmall title="Informasi Surat" description="Atur informasi data surat masuk" />
                        </aside>

                        <Separator class="my-6 lg:hidden" />

                        <div class="flex-1 md:max-w-2xl">
                            <section class="max-w-2xl items-start space-y-6 lg:flex lg:flex-auto lg:space-y-0 lg:space-x-6">
                                <div class="grid gap-2 lg:basis-1/2">
                                    <Label for="tglsurat">Tanggal Surat</Label>
                                    <Popover>
                                        <PopoverTrigger as-child>
                                            <Button
                                                id="tglsurat"
                                                type="button"
                                                variant="outline"
                                                :tabindex="3"
                                                :class="cn('w-full justify-start text-left font-normal', !form.tglSurat && 'text-muted-foreground')"
                                            >
                                                <CalendarIcon class="h-4 w-4" />
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
                                                type="button"
                                                variant="outline"
                                                :tabindex="4"
                                                :class="
                                                    cn('w-full justify-start text-left font-normal', !form.tglDiterima && 'text-muted-foreground')
                                                "
                                            >
                                                <CalendarIcon class="h-4 w-4" />
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
                            <section class="mt-6 max-w-2xl">
                                <div class="grid gap-2">
                                    <Label for="perihal">Perihal</Label>
                                    <Textarea id="perihal" :tabindex="4" v-model="form.subject" placeholder="Perihal isi surat" />
                                    <InputError :message="form.errors.subject" />
                                </div>
                            </section>
                        </div>
                    </div>

                    <Separator class="hidden lg:block" />

                    <div class="flex flex-col lg:flex-row lg:space-x-12">
                        <aside class="w-full max-w-xl lg:w-62.5 xl:w-87.5 2xl:w-md">
                            <HeadingSmall title="Kategori Surat" description="Tentukan kategori surat" />
                        </aside>

                        <Separator class="my-6 lg:hidden" />

                        <div class="flex-1 md:max-w-2xl">
                            <section class="max-w-2xl items-start space-y-6 lg:flex lg:flex-auto lg:space-y-0 lg:space-x-6">
                                <div class="grid gap-2 lg:basis-1/3">
                                    <Label for="kategori">Kategori</Label>
                                    <Select v-model="form.category">
                                        <SelectTrigger id="kategori" :tabindex="5" aria-label="Select kategori" class="w-full">
                                            <SelectValue placeholder="Pilih kategori" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectLabel>Kategori surat</SelectLabel>
                                            <SelectItem v-for="item in categoryInbox" :key="item.value" :value="item.value">
                                                {{ item.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.category" />
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

                <Separator class="my-6" />

                <div class="flex justify-end">
                    <Button type="submit" :disabled="form.processing || !form.isDirty" class="cursor-pointer">
                        <Loader2 v-if="form.processing" class="animate-spin" />
                        <Save v-else />
                        Simpan Perubahan
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
