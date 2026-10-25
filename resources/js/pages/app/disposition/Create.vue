<script setup lang="ts">
import pdfIcon from '@/assets/media/pdf.svg';
import pdfIconDark from '@/assets/media/pdf-dark.svg';
import MultiSelectCombobox from '@/components/forms/MultiSelectCombobox.vue';
import Heading from '@/components/Heading.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import LetterMeta from '@/components/letter/LetterMeta.vue';
import LetterTimelineTable from '@/components/letter/LetterTimelineTable.vue';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import { useClearErrorsOnChange } from '@/composables/useClearErrorsOnChange';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Followup, Letter, Position, TimelineRecipient } from '@/types';
import { Head, InertiaForm, useForm } from '@inertiajs/vue3';
import { Loader2, Plus, Send, Trash2, Undo2 } from '@lucide/vue';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    letter: Letter;
    recipients: TimelineRecipient[];
    recipient_id: string;
    positions: Position[];
    followup: Followup[];
}>();

type SelectOption = { id: number; name: string };

type FormInput = {
    type: string;
    letter_id_primary: number;
    letter_id: string;
    letter_recipient_id: number;
    letter_receipt_date: string;
    penerima: SelectOption[];
    penerima_cc: SelectOption[];
    catatan: string;
    followup: SelectOption[];
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

const selectedPenerima = ref<SelectOption[]>([]);
const selectedPenerimaCc = ref<SelectOption[]>([]);
const selectedFollowup = ref<SelectOption[]>([]);

const toOptions = (items: SelectOption[]): SelectOption[] => items.map(({ id, name }) => ({ id, name }));

watch(
    selectedPenerima,
    (items) => {
        form.penerima = toOptions(items);
    },
    { deep: true },
);
watch(
    selectedPenerimaCc,
    (items) => {
        form.penerima_cc = toOptions(items);
    },
    { deep: true },
);
watch(
    selectedFollowup,
    (items) => {
        form.followup = toOptions(items);
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
    form.type = sessionStorage.getItem('dsp_c_helper') || '';

    form.post(route('dispo.store'), {
        onSuccess: () => {
            form.reset();
            selectedPenerima.value = [];
            selectedPenerimaCc.value = [];
            selectedFollowup.value = [];
        },
    });
};

const hasHistory = ref(false);
const goBack = () => {
    window.history.back();
};

onMounted(() => {
    hasHistory.value = window.history.length > 1;
});

onUnmounted(() => {
    sessionStorage.removeItem('dsp_c_helper');
});
</script>

<template>
    <Head title="Tambah Baru Disposisi Surat" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="w-full max-w-7xl px-4 py-6 xl:px-12">
            <form class="mb-12" @submit.prevent="onSubmit">
                <Heading title="Buat Disposisi" description="Tambah baru disposisi surat" />

                <Accordion type="single" collapsible class="mb-8">
                    <AccordionItem value="letter">
                        <AccordionTrigger class="cursor-pointer text-sm">Detail Surat:</AccordionTrigger>
                        <AccordionContent>
                            <LetterMeta :letter="letter" show-category />
                            <div class="mt-4">
                                <LetterTimelineTable :items="recipients" />
                            </div>
                        </AccordionContent>
                    </AccordionItem>
                </Accordion>

                <div class="space-y-8">
                    <div class="flex flex-col lg:flex-row lg:space-x-12">
                        <aside class="w-full max-w-xl lg:w-62.5 xl:w-87.5 2xl:w-md">
                            <HeadingSmall title="Penerima" description="Atur dan pilih penerima disposisi" />
                        </aside>

                        <Separator class="my-6 lg:hidden" />

                        <section class="flex-1 items-start space-y-6">
                            <div class="grid gap-2">
                                <Label for="penerima">Penerima</Label>
                                <MultiSelectCombobox
                                    id="penerima"
                                    v-model="selectedPenerima"
                                    :options="positions"
                                    :tabindex="1"
                                    placeholder="Pilih penerima disposisi"
                                    search-placeholder="Cari penerima..."
                                />
                                <InputError :message="form.errors.penerima" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="penerima_cc">CC Penerima</Label>
                                <MultiSelectCombobox
                                    id="penerima_cc"
                                    v-model="selectedPenerimaCc"
                                    :options="positions"
                                    :tabindex="2"
                                    placeholder="Pilih CC penerima disposisi"
                                    search-placeholder="Cari penerima..."
                                />
                                <InputError :message="form.errors.penerima_cc" />
                            </div>
                        </section>
                    </div>

                    <Separator class="hidden lg:block" />

                    <div class="flex flex-col lg:flex-row lg:space-x-12">
                        <aside class="w-full max-w-xl lg:w-62.5 xl:w-87.5 2xl:w-md">
                            <HeadingSmall title="Ditujukan" description="Isi instruksi dan tindaklanjut disposisi" />
                        </aside>

                        <Separator class="my-6 lg:hidden" />

                        <section class="flex-1 items-start space-y-6">
                            <div class="grid gap-2">
                                <Label for="followup">Followup</Label>
                                <MultiSelectCombobox
                                    id="followup"
                                    v-model="selectedFollowup"
                                    :options="followup"
                                    :tabindex="3"
                                    placeholder="Pilih tindaklanjut"
                                    search-placeholder="Cari tindaklanjut..."
                                />
                                <InputError :message="form.errors.followup" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="catatan">Catatan</Label>
                                <Textarea
                                    id="catatan"
                                    :tabindex="4"
                                    v-model="form.catatan"
                                    placeholder="Masukkan catatan disposisi (Opsional)"
                                    class="dark:bg-input/30"
                                />
                                <InputError :message="form.errors.catatan" />
                            </div>
                        </section>
                    </div>

                    <Separator class="hidden lg:block" />

                    <div class="flex flex-col lg:flex-row lg:space-x-12">
                        <aside class="w-full max-w-xl lg:w-62.5 xl:w-87.5 2xl:w-md">
                            <HeadingSmall title="Lampiran" description="Unggah lampiran disposisi" />
                        </aside>

                        <Separator class="my-6 lg:hidden" />

                        <section class="flex-1">
                            <div class="flex items-center justify-between">
                                <div class="grid gap-1">
                                    <Label for="lampiran">Dokumen</Label>
                                    <p class="text-muted-foreground text-xs">Supported Format: PDF</p>
                                </div>
                                <Button
                                    id="lampiran"
                                    :tabindex="5"
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
                                        <p class="text-muted-foreground text-sm">Silahkan unggah bila disposisi memiliki lampiran.</p>
                                    </div>
                                    <div
                                        v-for="(item, index) in form.lampiran"
                                        :key="index"
                                        class="flex items-center justify-between"
                                        :class="{ 'border-input border-b border-dashed pb-4': index !== form.lampiran.length - 1 }"
                                    >
                                        <div class="mr-2 flex min-w-0 flex-1 items-center gap-2">
                                            <img class="block size-10 shrink-0 dark:hidden" :src="pdfIcon" alt="pdf icon" />
                                            <img class="hidden size-10 shrink-0 dark:block" :src="pdfIconDark" alt="pdf icon" />
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

                    <div class="mt-12 flex flex-col gap-4 lg:flex-row lg:justify-end">
                        <Button v-if="hasHistory" type="button" variant="outline" class="w-full cursor-pointer lg:w-40" @click="goBack">
                            <Undo2 class="size-4" />
                            Batal
                        </Button>
                        <Button type="submit" :disabled="form.penerima.length === 0 || form.processing" class="w-full cursor-pointer lg:w-50">
                            <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                            <Send v-else class="size-4" />
                            Kirim Disposisi
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
