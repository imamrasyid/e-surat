<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import LetterDocPreview from '@/components/letter/LetterDocPreview.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Letter, LetterDoc } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ChevronLeft, Loader2, Plus, Save, Trash2 } from '@lucide/vue';
import { onMounted, ref } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    letter: Letter;
}>();

const form = useForm({
    lampiran: [] as File[],
    hapus: [] as number[],
});

const existingDocs = ref<Letter['docs']>(props.letter.docs);
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

    if (fileInputRef.value) {
        fileInputRef.value.value = '';
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
    form.post(route('sent.updatedocs', props.letter.letter_id), {
        forceFormData: true,
    });
};

const showDeleteDialog = ref(false);
const deleteTarget = ref<{ id: number; name: string; index: number } | null>(null);

const openDeleteDialog = (doc: { id: number; name: string }, index: number) => {
    deleteTarget.value = { id: doc.id, name: doc.name, index };
    showDeleteDialog.value = true;
};

const confirmDelete = () => {
    if (deleteTarget.value && existingDocs.value) {
        form.hapus.push(deleteTarget.value.id);
        existingDocs.value.splice(deleteTarget.value.index, 1);
        deleteTarget.value = null;
        showDeleteDialog.value = false;
    }
};

const isPreviewOpen = ref(false);
const selectedDoc = ref<LetterDoc | null>(null);

const openPreview = (doc: LetterDoc) => {
    selectedDoc.value = doc;
    isPreviewOpen.value = true;
};

const hasHistory = ref(false);
const goBack = () => {
    window.history.back();
};

onMounted(() => {
    hasHistory.value = window.history.length > 1;
});
</script>

<template>
    <Head title="Perbarui Lampiran Surat" />

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
                <Heading class="mb-0!" title="Perbarui Lampiran Surat" description="Kelola lampiran surat yang telah terkirim" />
            </div>

            <dl class="mt-8 space-y-2">
                <div class="flex items-start space-x-4 text-sm lg:space-x-12">
                    <dt class="w-37 shrink-0 font-medium">Nomor Agenda</dt>
                    <dd class="max-w-md wrap-break-word">{{ letter.agenda }}</dd>
                </div>
                <div class="flex items-start space-x-4 text-sm lg:space-x-12">
                    <dt class="w-37 shrink-0 font-medium">Pengirim</dt>
                    <dd class="max-w-md wrap-break-word">{{ letter.sender }}</dd>
                </div>
                <div class="flex items-start space-x-4 text-sm lg:space-x-12">
                    <dt class="w-37 shrink-0 font-medium">Nomor Surat</dt>
                    <dd class="max-w-md wrap-break-word">{{ letter.number ?? '-' }}</dd>
                </div>
                <div class="flex items-start space-x-4 text-sm lg:space-x-12">
                    <dt class="w-37 shrink-0 font-medium">Perihal</dt>
                    <dd class="max-w-md wrap-break-word">{{ letter.subject }}</dd>
                </div>
            </dl>

            <Separator class="my-6" />

            <form @submit.prevent="onSubmit">
                <div class="flex flex-col lg:flex-row lg:space-x-12">
                    <aside class="w-full max-w-xl lg:w-62.5 xl:w-87.5 2xl:w-md">
                        <HeadingSmall title="Lampiran" description="Kelola lampiran surat masuk" />
                    </aside>

                    <Separator class="my-6 lg:hidden" />

                    <div class="flex-1 md:max-w-2xl">
                        <section class="max-w-2xl">
                            <Card v-if="existingDocs && existingDocs.length > 0" class="dark:bg-input/30 mb-6 rounded-lg">
                                <CardHeader>
                                    <CardTitle class="text-sm leading-none font-medium">Lampiran Saat Ini</CardTitle>
                                    <CardDescription class="text-xs">Daftar lampiran yang telah terkirim</CardDescription>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div
                                        v-for="(item, index) in existingDocs"
                                        :key="item.id"
                                        class="flex items-center justify-between"
                                        :class="{ 'border-input border-b border-dashed pb-4': index !== existingDocs.length - 1 }"
                                    >
                                        <button
                                            type="button"
                                            class="mr-2 flex min-w-0 flex-1 cursor-pointer items-center gap-2 text-left text-sm hover:underline"
                                            :title="`Pratinjau ${item.name}`"
                                            @click="openPreview(item)"
                                        >
                                            <img class="block size-10 shrink-0 dark:hidden" src="../../../assets/media/pdf.svg" alt="pdf icon" />
                                            <img class="hidden size-10 shrink-0 dark:block" src="../../../assets/media/pdf-dark.svg" alt="pdf icon" />
                                            <span class="line-clamp-2 min-w-0 break-all">
                                                {{ item.name }}
                                            </span>
                                        </button>
                                        <Button
                                            type="button"
                                            size="xs"
                                            class="rounded-full bg-red-50 hover:bg-red-100 dark:bg-red-500 dark:hover:bg-red-800"
                                            @click="openDeleteDialog(item, index)"
                                        >
                                            <Trash2 class="text-red-500 dark:text-white" />
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>

                            <!-- Upload lampiran baru -->
                            <div>
                                <div class="flex items-center justify-between">
                                    <div class="grid gap-1">
                                        <Label for="lampiran">Tambah Lampiran Baru</Label>
                                        <p class="text-muted-foreground text-xs">Supported Format: PDF</p>
                                    </div>
                                    <Button
                                        id="lampiran"
                                        type="button"
                                        size="icon-sm"
                                        class="cursor-pointer rounded-full"
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
                                <Card class="dark:bg-input/30 mt-2 rounded-lg" :class="[form.errors.lampiran ? 'border-red-500' : '']">
                                    <CardContent class="space-y-4">
                                        <div v-if="form.lampiran.length === 0" class="text-center">
                                            <p class="text-muted-foreground text-sm">Belum ada lampiran baru. Klik tombol + untuk mengunggah.</p>
                                        </div>
                                        <div
                                            v-for="(item, index) in form.lampiran"
                                            :key="index"
                                            class="flex items-center justify-between"
                                            :class="{ 'border-input border-b border-dashed pb-4': index != form.lampiran.length - 1 }"
                                        >
                                            <div class="mr-2 flex min-w-0 flex-1 items-center gap-2">
                                                <img class="block size-10 shrink-0 dark:hidden" src="../../../assets/media/pdf.svg" alt="pdf icon" />
                                                <img
                                                    class="hidden size-10 shrink-0 dark:block"
                                                    src="../../../assets/media/pdf-dark.svg"
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
                                                size="xs"
                                                class="rounded-full bg-red-50 hover:bg-red-100 dark:bg-red-500 dark:hover:bg-red-800"
                                                @click="form.lampiran.splice(index, 1)"
                                            >
                                                <Trash2 class="text-red-500 dark:text-white" />
                                            </Button>
                                        </div>
                                    </CardContent>
                                </Card>
                                <InputError :message="form.errors.lampiran" class="mt-1" />
                            </div>
                        </section>
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

        <AlertDialog v-model:open="showDeleteDialog">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Yakin menghapus lampiran?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Lampiran <strong>{{ deleteTarget?.name }}</strong>
                        akan dihapus secara permanen dan tidak dapat dikembalikan.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Batal</AlertDialogCancel>
                    <AlertDialogAction @click="confirmDelete">Ya, Hapus</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <LetterDocPreview v-model:open="isPreviewOpen" :doc="selectedDoc" />
    </AppLayout>
</template>
