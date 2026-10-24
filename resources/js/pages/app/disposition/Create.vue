<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Combobox, ComboboxAnchor, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxList } from '@/components/ui/combobox';
import Label from '@/components/ui/label/Label.vue';
import Separator from '@/components/ui/separator/Separator.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { TagsInput, TagsInputInput, TagsInputItem, TagsInputItemDelete, TagsInputItemText } from '@/components/ui/tags-input';
import { Textarea } from '@/components/ui/textarea';
import { useClearErrorsOnChange } from '@/composables/useClearErrorsOnChange';
import { categoryInbox } from '@/data/helpers';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Followup, Letter, Position, TimelineRecipient } from '@/types';
import { Head, InertiaForm, useForm } from '@inertiajs/vue3';
import { Loader2, Plus, Send, Trash2, XCircle } from '@lucide/vue';
import { useDateFormat } from '@vueuse/core';
import { computed, onUnmounted, ref } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    letter: Letter;
    recipients: TimelineRecipient[];
    recipient_id: string;
    positions: Position[];
    followup: Followup[];
}>();

type FormInput = {
    type: string;
    letter_id_primary: number;
    letter_id: string;
    letter_recipient_id: number;
    letter_receipt_date: string;
    penerima: { id: number; name: string }[];
    penerima_cc: { id: number; name: string }[];
    catatan: string;
    followup: { id: number; name: string }[];
    lampiran: File[];
};

const form: InertiaForm<FormInput> = useForm<FormInput>({
    type: '',
    letter_id_primary: Number(props.letter.id),
    letter_id: props.letter.letter_id,
    letter_recipient_id: Number(props.recipient_id),
    letter_receipt_date: props.letter.receipt_date,
    penerima: [],
    penerima_cc: [],
    catatan: '',
    followup: [],
    lampiran: [],
});
useClearErrorsOnChange(form, { debounceMs: 120 });

function getCategoryName(value: string) {
    const found = categoryInbox.find((cat) => cat.value === value);
    return found ? found.name : '-';
}

const openPenerima = ref<boolean>(false);
const searchPenerima = ref<string>('');
const penerimaSelect = (item: any) => {
    form.penerima.push({ id: item.value.id, name: item.value.name });
    searchPenerima.value = '';
    if (filteredPenerima.value.length === 0) {
        openPenerima.value = false;
    }
};
const filteredPenerima = computed(() => {
    const selectedIds = form.penerima.map((p) => p.id);
    const options = props.positions.filter((i) => !selectedIds.includes(i.id));
    if (searchPenerima.value) {
        const keyword = searchPenerima.value.toLowerCase();
        return options.filter((option) => option.name.toLowerCase().includes(keyword));
    }
    return options;
});

const openPenerimacc = ref<boolean>(false);
const searchPenerimacc = ref<string>('');
const penerimaSelectcc = (item: any) => {
    form.penerima_cc.push({ id: item.value.id, name: item.value.name });
    searchPenerimacc.value = '';
    if (filteredPenerimacc.value.length === 0) {
        openPenerimacc.value = false;
    }
};
const filteredPenerimacc = computed(() => {
    const selectedIds = form.penerima_cc.map((p) => p.id);
    const options = props.positions.filter((i) => !selectedIds.includes(i.id));
    if (searchPenerimacc.value) {
        const keyword = searchPenerimacc.value.toLowerCase();
        return options.filter((option) => option.name.toLowerCase().includes(keyword));
    }
    return options;
});

const openFollowup = ref<boolean>(false);
const searchFollowup = ref<string>('');
const followupSelect = (item: any) => {
    form.followup.push({ id: item.value.id, name: item.value.name });
    searchFollowup.value = '';
    if (filteredFollowup.value.length === 0) {
        openFollowup.value = false;
    }
};
const filteredFollowup = computed(() => {
    const selectedIds = form.followup.map((p) => p.id);
    const options = props.followup.filter((i) => !selectedIds.includes(i.id));
    if (searchFollowup.value) {
        const keyword = searchFollowup.value.toLowerCase();
        return options.filter((option) => option.name.toLowerCase().includes(keyword));
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
    const type = sessionStorage.getItem('dsp_c_helper') || '';
    form.type = type;

    form.post(route('dispo.store'), {
        onSuccess: () => {
            form.reset();
            openPenerima.value = false;
            openPenerimacc.value = false;
            openFollowup.value = false;
        },
    });
};

onUnmounted(() => {
    sessionStorage.removeItem('dsp_c_helper');
});
</script>

<template>
    <Head title="Tambah Baru Disposisi Surat" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6 xl:px-12">
            <Heading title="Tambah Baru" description="Buat disposisi surat" />

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
                                    <h6 class="text-sm dark:text-gray-300">{{ getCategoryName(letter.category) }}</h6>
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
                                            {{ item.received_at ? useDateFormat(item.received_at, 'DD/MM/YY, HH:mm', { locales: 'id-ID' }) : '-' }}
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
                        <HeadingSmall title="Penerima" description="Atur dan pilih penerima disposisi" />
                    </aside>

                    <Separator class="my-6 lg:hidden" />

                    <div class="flex-1 md:max-w-2xl">
                        <section class="max-w-2xl space-y-6">
                            <div class="grid gap-2">
                                <Label for="penerima">Pilih Penerima</Label>
                                <Combobox v-model="form.penerima" v-model:open="openPenerima" :ignore-filter="true">
                                    <ComboboxAnchor as-child>
                                        <TagsInput id="penerima" v-model="form.penerima" class="dark:bg-input/30 w-full gap-2 px-2">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <TagsInputItem v-for="(item, index) in form.penerima" :key="index" :value="item.name">
                                                    <TagsInputItemText />
                                                    <TagsInputItemDelete :as-child="true">
                                                        <button type="button" class="cursor-pointer" @click="form.penerima.splice(index, 1)">
                                                            <XCircle class="size-4 text-gray-700 dark:text-gray-300" />
                                                        </button>
                                                    </TagsInputItemDelete>
                                                </TagsInputItem>
                                            </div>

                                            <ComboboxInput v-model="searchPenerima" as-child>
                                                <TagsInputInput
                                                    :tabindex="1"
                                                    placeholder="Penerima disposisi..."
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
                                <Label for="penerima_cc">CC Penerima</Label>
                                <Combobox v-model="form.penerima_cc" v-model:open="openPenerimacc" :ignore-filter="true">
                                    <ComboboxAnchor as-child>
                                        <TagsInput id="penerima_cc" v-model="form.penerima_cc" class="dark:bg-input/30 w-full gap-2 px-2">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <TagsInputItem v-for="(item, index) in form.penerima_cc" :key="index" :value="item.name">
                                                    <TagsInputItemText />
                                                    <TagsInputItemDelete :as-child="true">
                                                        <button type="button" class="cursor-pointer" @click="form.penerima_cc.splice(index, 1)">
                                                            <XCircle class="size-4 text-gray-700 dark:text-gray-300" />
                                                        </button>
                                                    </TagsInputItemDelete>
                                                </TagsInputItem>
                                            </div>

                                            <ComboboxInput v-model="searchPenerimacc" as-child>
                                                <TagsInputInput
                                                    :tabindex="2"
                                                    placeholder="CC Penerima disposisi..."
                                                    class="h-auto w-full min-w-[200px] border-none p-0 shadow-none focus-visible:ring-0"
                                                    @keydown.enter.prevent
                                                />
                                            </ComboboxInput>
                                        </TagsInput>

                                        <ComboboxList class="w-[--reka-popper-anchor-width]">
                                            <ComboboxEmpty />
                                            <ComboboxGroup>
                                                <ComboboxItem
                                                    v-for="item in filteredPenerimacc"
                                                    :key="item.id"
                                                    :value="item"
                                                    @select.prevent="
                                                        (ev) => {
                                                            penerimaSelectcc(ev.detail);
                                                        }
                                                    "
                                                >
                                                    {{ item.name }}
                                                </ComboboxItem>
                                            </ComboboxGroup>
                                        </ComboboxList>
                                    </ComboboxAnchor>
                                </Combobox>
                                <InputError :message="form.errors.penerima_cc" />
                            </div>
                        </section>
                    </div>
                </div>

                <Separator class="hidden lg:block" />

                <div class="flex flex-col lg:flex-row lg:space-x-12">
                    <aside class="w-full max-w-xl lg:w-[250px] xl:w-[350px] 2xl:w-md">
                        <HeadingSmall title="Ditujukan" description="Isi instruksi dan tindaklanjut disposisi" />
                    </aside>

                    <Separator class="my-6 lg:hidden" />

                    <div class="flex-1 md:max-w-2xl">
                        <section class="max-w-2xl space-y-6">
                            <div class="grid gap-2">
                                <Label for="catatan">Catatan</Label>
                                <Textarea
                                    id="catatan"
                                    :tabindex="3"
                                    v-model="form.catatan"
                                    placeholder="Masukkan catatan disposisi (Opsional)"
                                    class="dark:bg-input/30"
                                />
                                <InputError :message="form.errors.catatan" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="followup">Followup</Label>
                                <Combobox v-model="form.followup" v-model:open="openFollowup" :ignore-filter="true">
                                    <ComboboxAnchor as-child>
                                        <TagsInput id="followup" v-model="form.followup" class="dark:bg-input/30 w-full gap-2 px-2">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <TagsInputItem v-for="(item, index) in form.followup" :key="index" :value="item.name">
                                                    <TagsInputItemText />
                                                    <TagsInputItemDelete :as-child="true">
                                                        <button type="button" class="cursor-pointer" @click="form.followup.splice(index, 1)">
                                                            <XCircle class="size-4 text-gray-700 dark:text-gray-300" />
                                                        </button>
                                                    </TagsInputItemDelete>
                                                </TagsInputItem>
                                            </div>

                                            <ComboboxInput v-model="searchFollowup" as-child>
                                                <TagsInputInput
                                                    :tabindex="4"
                                                    placeholder="Pilih tindaklanjut..."
                                                    class="h-auto w-full min-w-[200px] border-none p-0 shadow-none focus-visible:ring-0"
                                                    @keydown.enter.prevent
                                                />
                                            </ComboboxInput>
                                        </TagsInput>

                                        <ComboboxList class="w-[--reka-popper-anchor-width]">
                                            <ComboboxEmpty />
                                            <ComboboxGroup>
                                                <ComboboxItem
                                                    v-for="item in filteredFollowup"
                                                    :key="item.id"
                                                    :value="item"
                                                    @select.prevent="
                                                        (ev) => {
                                                            followupSelect(ev.detail);
                                                        }
                                                    "
                                                >
                                                    {{ item.name }}
                                                </ComboboxItem>
                                            </ComboboxGroup>
                                        </ComboboxList>
                                    </ComboboxAnchor>
                                </Combobox>
                                <InputError :message="form.errors.followup" />
                            </div>
                        </section>
                    </div>
                </div>

                <Separator class="hidden lg:block" />

                <div class="flex flex-col lg:flex-row lg:space-x-12">
                    <aside class="w-full max-w-xl lg:w-[250px] xl:w-[350px] 2xl:w-md">
                        <HeadingSmall title="Lampiran" description="Unggah lampiran disposisi" />
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
                                    :tabindex="5"
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
                                        <p class="text-muted-foreground text-sm">Silahkan unggah bila disposisi memiliki lampiran.</p>
                                    </div>
                                    <div
                                        v-for="(item, index) in form.lampiran"
                                        :key="index"
                                        class="flex items-center justify-between py-4"
                                        :class="{ 'border-b dark:border-gray-500': index != form.lampiran.length - 1 }"
                                    >
                                        <div class="mr-2 flex items-center gap-2">
                                            <img class="block size-10 dark:hidden" src="../../../assets/media/pdf.svg" alt="pdf icon" />
                                            <img class="hidden size-10 dark:block" src="../../../assets/media/pdf-dark.svg" alt="pdf icon" />
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

                <div class="justify-self-end-safe">
                    <Button type="submit" :disabled="form.penerima.length == 0 || form.processing">
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        <Send v-else class="h-4 w-4" />
                        Kirim Disposisi
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
