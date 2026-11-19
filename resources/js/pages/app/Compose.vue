<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Card, CardContent } from '@/components/ui/card';
import {
    Combobox,
    ComboboxAnchor,
    ComboboxEmpty,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxList,
    ComboboxTrigger,
    ComboboxViewport,
} from '@/components/ui/combobox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import { useClearErrorsOnChange } from '@/composables/useClearErrorsOnChange';
import { createDecades } from '@/composables/useDecades';
import { categoryInbox, letterReceivedVia, letterSecurityClassification } from '@/data/helpers';
import AppLayout from '@/layouts/AppLayout.vue';
import { cn } from '@/lib/utils';
import type { BreadcrumbItem, LetterReceivedVia, LetterSecurityClassification, Position, RefKdKlasifikasi } from '@/types';
import { Head, InertiaForm, useForm } from '@inertiajs/vue3';
import type { DateValue } from '@internationalized/date';
import { DateFormatter, getLocalTimeZone, today } from '@internationalized/date';
import { Calendar as CalendarIcon, CheckIcon, ChevronDownIcon, Loader2, Plus, Send, Trash2 } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Input Surat Masuk',
        href: '/app/compose',
    },
];

const props = defineProps<{
    kdKlasifikasi: RefKdKlasifikasi[];
    positions: Position[];
}>();

const df = new DateFormatter('en-US', {
    dateStyle: 'long',
});

const thisYear = computed(() => {
    return today(getLocalTimeZone()).year.toString();
});

const decades = createDecades({ dateObj: today(getLocalTimeZone()), startIndex: -1, endIndex: 0 });

type FormInput = {
    pengirim: string;
    nomor: string;
    tglSuratStr: string | null;
    tglDiterimaStr: string | null;
    perihal: string;
    kategori: string;
    security_classification: LetterSecurityClassification;
    received_via: LetterReceivedVia;
    agenda: string;
    agendaTahun: string;
    penerima: { id: number; name: string }[];
    catatan: string;
    lampiran: File[];
};

const form: InertiaForm<FormInput> & {
    tglSurat: DateValue | undefined;
    tglDiterima: DateValue | undefined;
} = useForm<FormInput>({
    pengirim: '',
    nomor: '',
    tglSuratStr: null,
    tglDiterimaStr: null,
    perihal: '',
    kategori: '',
    security_classification: 'B',
    received_via: 'internal',
    agenda: '',
    agendaTahun: thisYear.value,
    penerima: [],
    catatan: '',
    lampiran: [],
}) as InertiaForm<FormInput> & {
    tglSurat: DateValue | undefined;
    tglDiterima: DateValue | undefined;
};
watch(
    () => form.tglSurat,
    (val) => {
        form.tglSuratStr = val ? val.toString() : null;
    },
);
watch(
    () => form.tglDiterima,
    (val) => {
        form.tglDiterimaStr = val ? val.toString() : null;
    },
);
useClearErrorsOnChange(form, { debounceMs: 120 });

const selectedAgenda = ref<RefKdKlasifikasi | null>(null);
const selectAgenda = (item: RefKdKlasifikasi) => {
    selectedAgenda.value = item;
    form.agenda = item.kode.toString();
};

const selectedPenerimas = ref<Position[]>([]);
watch(
    selectedPenerimas,
    (items) => {
        form.penerima = items.map((p) => ({ id: p.id, name: p.name }));
    },
    { deep: true },
);

const fileInputRef = ref<HTMLInputElement | null>(null);

const triggerFileSelect = () => {
    fileInputRef.value?.click();
};

const handleFileChange = (event: Event) => {
    const files = (event.target as HTMLInputElement).files;
    if (files && files.length > 0) {
        Array.from(files).forEach((file) => {
            if (file.type === 'application/pdf') {
                form.lampiran.push(file);
            } else {
                toast.error('Format file tidak didukung! Hanya file PDF yang dapat diunggah.');
            }
        });
    }
};

function formatBytes(bytes: number, decimals = 2): string {
    if (bytes === 0) return '0 Bytes';

    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

const onSubmit = () => {
    form.post(route('app.compose.store'), {
        onSuccess: () => {
            form.reset();
            form.tglSurat = undefined;
            form.tglDiterima = undefined;
        },
    });
};
</script>

<template>
    <Head title="Input Surat Masuk" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6">
            <form class="mb-12" @submit.prevent="onSubmit">
                <div class="mb-12 flex items-center justify-between gap-4">
                    <Heading class="mb-0!" title="Input Baru" description="Buat baru input data surat masuk" />
                    <Transition
                        enter-active-class="transition duration-200 ease-in-out"
                        enter-from-class="opacity-40 translate-x-2"
                        enter-to-class="opacity-100 translate-x-0"
                        leave-active-class="transition duration-200 ease-in-out"
                        leave-from-class="opacity-100 translate-x-0"
                        leave-to-class="opacity-40 translate-x-2"
                    >
                        <Button v-show="form.isDirty" :disabled="form.processing">
                            <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                            <Send v-else class="size-4" />
                            Kirim Surat
                        </Button>
                    </Transition>
                </div>
                <div class="space-y-8 2xl:px-12">
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
                                        v-model="form.pengirim"
                                        placeholder="Nama pengirim surat"
                                    />
                                    <InputError :message="form.errors.pengirim" />
                                </div>
                                <div class="grid gap-2 lg:basis-1/2">
                                    <Label for="nomor">Nomor</Label>
                                    <Input
                                        id="nomor"
                                        type="text"
                                        :tabindex="2"
                                        autocomplete="off"
                                        v-model="form.nomor"
                                        placeholder="Nomor surat masuk (Opsional)"
                                    />
                                    <InputError :message="form.errors.nomor" />
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
                                    <InputError :message="form.errors.tglSuratStr" />
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
                                    <InputError :message="form.errors.tglDiterimaStr" />
                                </div>
                            </section>
                            <section class="mt-6 max-w-2xl">
                                <div class="grid gap-2">
                                    <Label for="perihal">Perihal</Label>
                                    <Textarea id="perihal" :tabindex="5" v-model="form.perihal" placeholder="Perihal isi surat" />
                                    <InputError :message="form.errors.perihal" />
                                </div>
                            </section>
                        </div>
                    </div>

                    <Separator class="hidden lg:block" />

                    <div class="flex flex-col lg:flex-row lg:space-x-12">
                        <aside class="w-full max-w-xl lg:w-62.5 xl:w-87.5 2xl:w-md">
                            <HeadingSmall title="Agenda Surat" description="Tentukan kategori dan agenda surat" />
                        </aside>

                        <Separator class="my-6 lg:hidden" />

                        <div class="flex-1 md:max-w-2xl">
                            <section class="max-w-2xl items-start space-y-6 lg:flex lg:flex-auto lg:space-y-0 lg:space-x-6">
                                <div class="grid gap-2 lg:basis-1/3">
                                    <Label for="kategori">Kategori</Label>
                                    <Select v-model="form.kategori">
                                        <SelectTrigger id="kategori" :tabindex="6" aria-label="Select kategori" class="w-full">
                                            <SelectValue placeholder="Pilih kategori" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectLabel>Kategori surat</SelectLabel>
                                            <SelectItem v-for="item in categoryInbox" :key="item.value" :value="item.value">
                                                {{ item.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.kategori" />
                                </div>
                                <div class="grid gap-2 lg:basis-2/3">
                                    <Label for="agenda">Agenda</Label>
                                    <div class="flex w-full items-start justify-between gap-6">
                                        <div class="grid w-full gap-2 lg:w-3/4">
                                            <Combobox v-model="selectedAgenda" by="nama">
                                                <ComboboxAnchor as-child>
                                                    <ComboboxTrigger as-child>
                                                        <Button
                                                            id="agenda"
                                                            type="button"
                                                            variant="outline"
                                                            :tabindex="7"
                                                            :class="
                                                                cn(
                                                                    'w-full justify-between font-normal',
                                                                    !selectedAgenda?.nama && 'text-muted-foreground',
                                                                )
                                                            "
                                                        >
                                                            {{ selectedAgenda?.nama || 'Pilih agenda' }}
                                                            <ChevronDownIcon class="text-muted-foreground" />
                                                        </Button>
                                                    </ComboboxTrigger>
                                                </ComboboxAnchor>
                                                <ComboboxList
                                                    align="start"
                                                    class="w-(--reka-combobox-trigger-width) min-w-(--reka-combobox-trigger-width)"
                                                >
                                                    <ComboboxInput id="search-clasification" placeholder="Cari..." />
                                                    <ComboboxViewport class="max-h-65 w-full min-w-(--reka-combobox-trigger-width) p-1">
                                                        <ComboboxEmpty>Tidak ada data.</ComboboxEmpty>
                                                        <ComboboxItem
                                                            v-for="item in kdKlasifikasi"
                                                            :key="item.kode"
                                                            :value="item"
                                                            @select="(event) => selectAgenda(event.detail.value as RefKdKlasifikasi)"
                                                        >
                                                            {{ item.nama }}
                                                            <ComboboxItemIndicator>
                                                                <CheckIcon />
                                                            </ComboboxItemIndicator>
                                                        </ComboboxItem>
                                                    </ComboboxViewport>
                                                </ComboboxList>
                                            </Combobox>
                                            <InputError :message="form.errors.agenda" />
                                        </div>
                                        <div class="grid w-1/4 gap-2">
                                            <Select v-model="form.agendaTahun">
                                                <SelectTrigger :tabindex="8" aria-label="Select year" class="w-full">
                                                    <SelectValue placeholder="Tahun agenda" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectLabel>Tahun agenda</SelectLabel>
                                                    <SelectItem v-for="(yearValue, index) in decades" :key="index" :value="yearValue.year.toString()">
                                                        {{ yearValue.year }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                            <InputError :message="form.errors.agendaTahun" />
                                        </div>
                                    </div>
                                </div>
                            </section>
                            <section class="mt-6 max-w-2xl items-start space-y-6 lg:flex lg:flex-auto lg:space-y-0 lg:space-x-6">
                                <div class="grid gap-2 lg:basis-1/2">
                                    <Label for="security_classification">Sifat Surat</Label>
                                    <Select v-model="form.security_classification">
                                        <SelectTrigger
                                            id="security_classification"
                                            :tabindex="9"
                                            aria-label="Sifat klasifikasi keamanan"
                                            class="w-full"
                                        >
                                            <SelectValue placeholder="Pilih klasifikasi" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectLabel>Sifat/klasifikasi keamanan</SelectLabel>
                                            <SelectItem v-for="item in letterSecurityClassification" :key="item.value" :value="item.value">
                                                {{ item.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.security_classification" />
                                </div>
                                <div class="grid gap-2 lg:basis-1/2">
                                    <Label for="received_via">Sumber Penerimaan</Label>
                                    <Select v-model="form.received_via">
                                        <SelectTrigger id="received_via" :tabindex="10" aria-label="Sumber penerimaan" class="w-full">
                                            <SelectValue placeholder="Pilih sumber penerimaan" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectLabel>Sumber penerimaan surat</SelectLabel>
                                            <SelectItem v-for="item in letterReceivedVia" :key="item.value" :value="item.value">
                                                {{ item.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.received_via" />
                                </div>
                            </section>
                        </div>
                    </div>

                    <Separator class="hidden lg:block" />

                    <div class="flex flex-col lg:flex-row lg:space-x-12">
                        <aside class="w-full max-w-xl lg:w-62.5 xl:w-87.5 2xl:w-md">
                            <HeadingSmall title="Ditujukan" description="Pilih penerima dan isi catatan surat" />
                        </aside>

                        <Separator class="my-6 lg:hidden" />

                        <div class="flex-1 md:max-w-2xl">
                            <section class="max-w-2xl space-y-6">
                                <div class="grid gap-2">
                                    <Label for="penerima">Penerima</Label>
                                    <Combobox v-model="selectedPenerimas" multiple by="id">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <Button
                                                    id="penerima"
                                                    type="button"
                                                    variant="outline"
                                                    :tabindex="11"
                                                    :class="
                                                        cn(
                                                            'w-full justify-between font-normal whitespace-normal',
                                                            selectedPenerimas.length === 0 && 'text-muted-foreground',
                                                        )
                                                    "
                                                >
                                                    <span class="line-clamp-1 text-left">
                                                        {{
                                                            selectedPenerimas.length > 0
                                                                ? selectedPenerimas.map((p) => p.name).join(', ')
                                                                : 'Pilih penerima'
                                                        }}
                                                    </span>
                                                    <ChevronDownIcon class="text-muted-foreground" />
                                                </Button>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList align="start" class="w-(--reka-combobox-trigger-width) min-w-(--reka-combobox-trigger-width)">
                                            <ComboboxInput id="search-position" placeholder="Cari..." />
                                            <ComboboxViewport class="max-h-65 w-full min-w-(--reka-combobox-trigger-width) p-1">
                                                <ComboboxEmpty>Tidak ada data.</ComboboxEmpty>
                                                <ComboboxItem v-for="item in positions" :key="item.id" :value="item">
                                                    <div
                                                        class="border-input data-[selected=true]:border-primary data-[selected=true]:bg-primary data-[selected=true]:text-primary-foreground pointer-events-none size-4 shrink-0 rounded-lg border transition-all select-none *:[svg]:opacity-0 data-[selected=true]:*:[svg]:opacity-100"
                                                        :data-selected="selectedPenerimas.some((p) => p.id === item.id)"
                                                    >
                                                        <CheckIcon class="size-3.5 text-current" />
                                                    </div>
                                                    {{ item.name }}
                                                </ComboboxItem>
                                            </ComboboxViewport>
                                        </ComboboxList>
                                    </Combobox>
                                    <InputError :message="form.errors.penerima" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="catatan">Catatan</Label>
                                    <Textarea id="catatan" :tabindex="12" v-model="form.catatan" placeholder="Masukkan catatan surat (Opsional)" />
                                    <InputError :message="form.errors.catatan" />
                                </div>
                            </section>
                        </div>
                    </div>

                    <Separator class="hidden lg:block" />

                    <div class="flex flex-col lg:flex-row lg:space-x-12">
                        <aside class="w-full max-w-xl lg:w-62.5 xl:w-87.5 2xl:w-md">
                            <HeadingSmall title="Lampiran" description="Unggah surat masuk" />
                        </aside>

                        <Separator class="my-6 lg:hidden" />

                        <div class="flex-1 md:max-w-2xl">
                            <section class="max-w-2xl">
                                <div class="flex items-center justify-between">
                                    <div class="grid gap-1">
                                        <Label for="lampiran">Dokumen</Label>
                                        <p class="text-muted-foreground text-xs">Supported Format: PDF</p>
                                    </div>
                                    <Button
                                        id="lampiran"
                                        :tabindex="13"
                                        type="button"
                                        size="icon-sm"
                                        class="rounded-full"
                                        aria-label="Button lampiran"
                                        @click="triggerFileSelect"
                                    >
                                        <Plus />
                                    </Button>
                                    <input
                                        ref="fileInputRef"
                                        type="file"
                                        multiple
                                        accept="application/pdf"
                                        hidden
                                        aria-label="Input file"
                                        @change="handleFileChange"
                                    />
                                </div>
                                <Card class="dark:bg-input/30 mt-2 rounded-lg" :class="[form.errors.lampiran ? 'border-red-500' : '']">
                                    <CardContent class="space-y-4">
                                        <div v-if="form.lampiran.length === 0" class="text-center">
                                            <p class="text-muted-foreground text-sm">Silahkan unggah lampiran surat masuk.</p>
                                        </div>
                                        <div
                                            v-for="(item, index) in form.lampiran"
                                            :key="index"
                                            class="flex items-center justify-between"
                                            :class="{ 'border-input border-b border-dashed pb-4': index != form.lampiran.length - 1 }"
                                        >
                                            <div class="mr-2 flex min-w-0 flex-1 items-center gap-2">
                                                <img class="block size-10 shrink-0 dark:hidden" src="../../assets/media/pdf.svg" alt="pdf icon" />
                                                <img
                                                    class="hidden size-10 shrink-0 dark:block"
                                                    src="../../assets/media/pdf-dark.svg"
                                                    alt="pdf icon"
                                                />
                                                <div class="space-y-0.5">
                                                    <p class="line-clamp-1 min-w-0 text-sm break-all">
                                                        {{ item.name }}
                                                    </p>
                                                    <p class="text-muted-foreground text-xs">{{ formatBytes(Number(item.size)) }}</p>
                                                </div>
                                            </div>
                                            <Button
                                                type="button"
                                                size="icon-xs"
                                                class="rounded-full bg-red-50 hover:bg-red-100 dark:bg-red-500 dark:hover:bg-red-800"
                                                @click="form.lampiran.splice(index, 1)"
                                            >
                                                <Trash2 class="text-red-500 dark:text-white" />
                                            </Button>
                                        </div>
                                    </CardContent>
                                </Card>
                            </section>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
