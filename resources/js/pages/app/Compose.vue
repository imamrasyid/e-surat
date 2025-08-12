<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Card, CardContent } from '@/components/ui/card';
import { Combobox, ComboboxAnchor, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxList } from '@/components/ui/combobox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { TagsInput, TagsInputInput, TagsInputItem, TagsInputItemDelete, TagsInputItemText } from '@/components/ui/tags-input';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from '@/components/ui/toast/use-toast';
import { useClearErrorsOnChange } from '@/composables/useClearErrorsOnChange';
import { createDecadae } from '@/composables/useDecadae';
import { categoryInbox } from '@/data/helpers';
import AppLayout from '@/layouts/AppLayout.vue';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, InertiaForm, useForm } from '@inertiajs/vue3';
import type { DateValue } from '@internationalized/date';
import { DateFormatter, getLocalTimeZone, today } from '@internationalized/date';
import { Calendar as CalendarIcon, Loader2, Plus, Send, Trash2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const { toast } = useToast();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Input Surat Masuk',
        href: '/app/compose',
    },
];

const props = defineProps<{
    kdKlasifikasi: any;
    positions: any;
}>();

const df = new DateFormatter('en-US', {
    dateStyle: 'long',
});

type FormInput = {
    pengirim: string;
    nomor: string;
    tglSuratStr: string | null;
    tglDiterimaStr: string | null;
    perihal: string;
    kategori: string;
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
    agenda: '',
    agendaTahun: '',
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

const openPenerima = ref(false);
const searchPenerima = ref();
const penerimaSelect = (item: any) => {
    console.log(item.value.name);
    form.penerima.push({ id: item.value.id, name: item.value.name });
    searchPenerima.value = '';
    if (filteredPenerima.value.length === 0) {
        openPenerima.value = false;
    }
};
const filteredPenerima = computed(() => {
    const selectedIds = form.penerima.map((p) => p.id);
    const options = props.positions.filter((i: any) => !selectedIds.includes(i.id));
    if (searchPenerima.value) {
        const keyword = searchPenerima.value.toLowerCase();
        return options.filter((option: any) => option.name.toLowerCase().includes(keyword));
    }
    return options;
});

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
                toast({
                    title: 'Format file tidak didukung',
                    description: 'Hanya file PDF yang dapat diunggah.',
                    variant: 'destructive',
                });
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
            toast({
                title: 'Success! Data terkirim.',
                description: 'Data input surat masuk berhasil terkirim',
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
    <Head title="Input Surat Masuk" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6">
            <form class="mb-12" @submit.prevent="onSubmit">
                <div class="mb-12 flex items-center justify-between gap-4">
                    <Heading class="!mb-0" title="Input Baru" description="Buat baru input data surat masuk" />
                    <Transition
                        enter-active-class="transition duration-200 ease-in-out"
                        enter-from-class="opacity-40 translate-x-2"
                        enter-to-class="opacity-100 translate-x-0"
                        leave-active-class="transition duration-200 ease-in-out"
                        leave-from-class="opacity-100 translate-x-0"
                        leave-to-class="opacity-40 translate-x-2"
                    >
                        <Button v-show="form.isDirty" :disabled="form.processing">
                            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            <Send v-else class="h-4 w-4" />
                            Kirim Surat
                        </Button>
                    </Transition>
                </div>
                <div class="space-y-8 xl:px-12">
                    <div class="flex flex-col lg:flex-row lg:space-x-12">
                        <aside class="w-full max-w-xl lg:w-[250px] xl:w-[350px] 2xl:w-md">
                            <HeadingSmall title="Pengirim" description="Masukkan pengirim dan nomor surat" />
                        </aside>

                        <Separator class="my-6 lg:hidden" />

                        <div class="flex-1 md:max-w-2xl">
                            <section class="max-w-2xl space-y-6 lg:flex lg:flex-auto lg:space-y-0 lg:space-x-6">
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
                                        placeholder="Nomor surat masuk"
                                    />
                                    <InputError :message="form.errors.nomor" />
                                </div>
                            </section>
                        </div>
                    </div>

                    <Separator class="hidden lg:block" />

                    <div class="flex flex-col lg:flex-row lg:space-x-12">
                        <aside class="w-full max-w-xl lg:w-[250px] xl:w-[350px] 2xl:w-md">
                            <HeadingSmall title="Informasi Surat" description="Atur informasi data surat masuk" />
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
                                                    cn('max-w-[200px] justify-start text-left font-normal', !form.tglSurat && 'text-muted-foreground')
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
                                    <InputError :message="form.errors.tglSuratStr" />
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
                                                        'max-w-[200px] justify-start text-left font-normal',
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
                        <aside class="w-full max-w-xl lg:w-[250px] xl:w-[350px] 2xl:w-md">
                            <HeadingSmall title="Agenda Surat" description="Tentukan kategori dan agenda surat" />
                        </aside>

                        <Separator class="my-6 lg:hidden" />

                        <div class="flex-1 md:max-w-2xl">
                            <section class="max-w-2xl space-y-6 lg:flex lg:flex-auto lg:space-y-0 lg:space-x-6">
                                <div class="grid gap-2 lg:basis-1/3">
                                    <Label for="kaetgori">Kategori</Label>
                                    <Select v-model="form.kategori">
                                        <SelectTrigger id="kaetgori" :tabindex="6" aria-label="Select kategori">
                                            <SelectValue placeholder="Pilih kategori" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectItem v-for="item in categoryInbox" :key="item.value" :value="item.value">
                                                    {{ item.name }}
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.kategori" />
                                </div>
                                <div class="grid gap-2 lg:basis-2/3">
                                    <Label for="agenda">Agenda</Label>
                                    <div class="flex w-full items-center justify-between gap-2">
                                        <div class="grid w-[60%] gap-2">
                                            <Select v-model="form.agenda">
                                                <SelectTrigger id="agenda" :tabindex="7" aria-label="Select agenda">
                                                    <SelectValue placeholder="Pilih klasifikasi" />
                                                </SelectTrigger>
                                                <SelectContent class="max-h-[300px]">
                                                    <SelectGroup>
                                                        <SelectItem v-for="item in kdKlasifikasi" :key="item.id" :value="item.kode">
                                                            {{ item.nama }}
                                                        </SelectItem>
                                                    </SelectGroup>
                                                </SelectContent>
                                            </Select>

                                            <InputError :message="form.errors.agenda" />
                                        </div>
                                        <div class="grid w-[40%] gap-2">
                                            <Select v-model="form.agendaTahun">
                                                <SelectTrigger :tabindex="8" aria-label="Select year">
                                                    <SelectValue placeholder="Tahun agenda" />
                                                </SelectTrigger>
                                                <SelectContent class="max-h-[200px]">
                                                    <SelectItem
                                                        v-for="yearValue in createDecadae({
                                                            dateObj: today(getLocalTimeZone()),
                                                            startIndex: -1,
                                                            endIndex: 0,
                                                        })"
                                                        :key="yearValue.toString()"
                                                        :value="yearValue.year.toString()"
                                                    >
                                                        {{ yearValue.year }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>

                                            <InputError :message="form.errors.agendaTahun" />
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>

                    <Separator class="hidden lg:block" />

                    <div class="flex flex-col lg:flex-row lg:space-x-12">
                        <aside class="w-full max-w-xl lg:w-[250px] xl:w-[350px] 2xl:w-md">
                            <HeadingSmall title="Ditujukan" description="Pilih penerima dan isi catatan surat" />
                        </aside>

                        <Separator class="my-6 lg:hidden" />

                        <div class="flex-1 md:max-w-2xl">
                            <section class="max-w-2xl space-y-6">
                                <div class="grid gap-2">
                                    <Label for="penerima">Penerima</Label>
                                    <Combobox :tabindex="9" v-model="form.penerima" v-model:open="openPenerima" :ignore-filter="true">
                                        <ComboboxAnchor as-child>
                                            <TagsInput id="penerima" v-model="form.penerima" class="w-full gap-2 px-2">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <TagsInputItem
                                                        v-for="(item, index) in form.penerima"
                                                        :key="index"
                                                        :value="item.name ? item.name : ''"
                                                    >
                                                        <TagsInputItemText />
                                                        <TagsInputItemDelete />
                                                    </TagsInputItem>
                                                </div>

                                                <ComboboxInput v-model="searchPenerima" as-child>
                                                    <TagsInputInput
                                                        placeholder="Penerima surat..."
                                                        class="h-auto w-full min-w-[200px] border-none p-0 shadow-none focus-visible:ring-0"
                                                        @keydown.enter.prevent
                                                    />
                                                </ComboboxInput>
                                            </TagsInput>

                                            <ComboboxList class="w-[--reka-popper-anchor-width]">
                                                <ComboboxEmpty />
                                                <ComboboxGroup>
                                                    <ComboboxItem
                                                        v-for="item in filteredPenerima"
                                                        :key="item.id"
                                                        :value="item"
                                                        @select.prevent="
                                                            (ev) => {
                                                                penerimaSelect(ev.detail);
                                                            }
                                                        "
                                                    >
                                                        {{ item.name }}
                                                    </ComboboxItem>
                                                </ComboboxGroup>
                                            </ComboboxList>
                                        </ComboboxAnchor>
                                    </Combobox>
                                    <InputError :message="form.errors.penerima" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="catatan">Catatan</Label>
                                    <Textarea id="catatan" :tabindex="10" v-model="form.catatan" placeholder="Masukkan catatan surat (Opsional)" />
                                    <InputError :message="form.errors.catatan" />
                                </div>
                            </section>
                        </div>
                    </div>

                    <Separator class="hidden lg:block" />

                    <div class="flex flex-col lg:flex-row lg:space-x-12">
                        <aside class="w-full max-w-xl lg:w-[250px] xl:w-[350px] 2xl:w-md">
                            <HeadingSmall title="Lampiran" description="Unggah surat masuk" />
                        </aside>

                        <Separator class="my-6 lg:hidden" />

                        <div class="flex-1 md:max-w-2xl">
                            <section class="max-w-2xl">
                                <div class="flex items-center justify-between">
                                    <div class="grid gap-1">
                                        <Label for="lampiran">Dokumen</Label>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Supported Format: PDF</p>
                                    </div>
                                    <Button
                                        id="lampiran"
                                        :tabindex="11"
                                        type="button"
                                        size="icon"
                                        class="rounded-full"
                                        aria-label="Button lampiran"
                                        @click="triggerFileSelect"
                                    >
                                        <Plus class="h-4 w-4" />
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
                                <Card class="mt-2" :class="{ 'border-red-500': form.errors.lampiran }">
                                    <CardContent>
                                        <div v-if="form.lampiran.length === 0" class="text-center">
                                            <p class="text-sm text-gray-500">Silahkan unggah lampiran surat masuk.</p>
                                        </div>
                                        <div
                                            v-for="(item, index) in form.lampiran"
                                            :key="index"
                                            class="flex items-center justify-between py-4"
                                            :class="{ 'border-b dark:border-gray-500': index != form.lampiran.length - 1 }"
                                        >
                                            <div class="mr-2 flex items-center gap-2">
                                                <img class="block size-10 dark:hidden" src="../../assets/media/pdf.svg" alt="pdf icon" />
                                                <img class="hidden size-10 dark:block" src="../../assets/media/pdf-dark.svg" alt="pdf icon" />
                                                <div>
                                                    <h6 class="line-clamp-1 text-sm font-medium text-gray-800 dark:text-gray-300">
                                                        {{ item.name }}
                                                    </h6>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ formatBytes(Number(item.size)) }}</p>
                                                </div>
                                            </div>
                                            <Button
                                                type="button"
                                                size="icon"
                                                class="size-6 rounded-full bg-red-50 hover:bg-red-100 dark:bg-red-500 dark:hover:bg-red-800"
                                                @click="form.lampiran.splice(index, 1)"
                                            >
                                                <Trash2 class="size-3" :class="'text-red-500 dark:text-white'" />
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
