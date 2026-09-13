<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { useToast } from '@/components/ui/toast/use-toast';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Letter } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { useDateFormat } from '@vueuse/core';
import { ChevronLeft, Loader2, Plus, Save, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

const { toast } = useToast();

const props = defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    letter: Letter;
}>();

const form = useForm({
    lampiran: [] as File[],
    hapus: [] as number[],
});

// Daftar lampiran yang sudah ada (reaktif, untuk tampilan)
const existingDocs = ref<Letter['docs']>(props.letter.docs);

// Dialog konfirmasi hapus
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

// Upload file baru
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
    // Reset input agar bisa upload file yang sama lagi
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
        onSuccess: () => {
            form.reset();
            toast({
                title: 'Berhasil!',
                description: 'Data lampiran surat berhasil diperbarui.',
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
                    description: 'Sepertinya data belum lengkap.',
                    variant: 'warning',
                });
            }
        },
    });
};
</script>

<template>
    <Head title="Perbarui Lampiran Surat" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6 xl:px-12">
            <div class="flex flex-col gap-4 sm:flex-row sm:justify-between">
                <HeadingSmall title="Perbarui Lampiran Surat" description="Kelola lampiran surat yang telah terkirim" />
                <div class="sm:text-end">
                    <p class="text-xs text-gray-500">
                        {{ useDateFormat(letter.created_at, 'dddd, DD MMM YYYY, HH.mm', { locales: 'id-ID' }) }}
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

            <!-- Info Surat (Read-Only) -->
            <div class="mt-6 space-y-2">
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
                        <h6 class="text-sm font-medium">Perihal</h6>
                    </aside>
                    <div class="flex-1 sm:max-w-md">
                        <h6 class="text-sm dark:text-gray-300">{{ letter.subject }}</h6>
                    </div>
                </div>
            </div>

            <Separator class="my-6" />

            <!-- Form Lampiran -->
            <form @submit.prevent="onSubmit">
                <div class="space-y-8">
                    <div class="flex flex-col lg:flex-row lg:space-x-12">
                        <aside class="w-full max-w-xl lg:w-[250px] xl:w-[350px] 2xl:w-md">
                            <HeadingSmall title="Lampiran" description="Kelola lampiran surat masuk" />
                        </aside>

                        <Separator class="my-6 lg:hidden" />

                        <div class="flex-1 md:max-w-2xl">
                            <section class="max-w-2xl">
                                <!-- Lampiran yang sudah ada -->
                                <div v-if="existingDocs && existingDocs.length > 0" class="mb-6">
                                    <Label class="mb-2 block">Lampiran Saat Ini</Label>
                                    <Card>
                                        <CardContent>
                                            <div
                                                v-for="(item, index) in existingDocs"
                                                :key="item.id"
                                                class="flex items-center justify-between py-4"
                                                :class="{ 'border-b dark:border-gray-500': index !== existingDocs.length - 1 }"
                                            >
                                                <a :href="item.url" target="_blank" class="mr-2 flex items-center gap-2">
                                                    <img class="block size-10 dark:hidden" src="../../../assets/media/pdf.svg" alt="pdf icon" />
                                                    <img class="hidden size-10 dark:block" src="../../../assets/media/pdf-dark.svg" alt="pdf icon" />
                                                    <h6 class="line-clamp-1 text-sm font-medium text-gray-800 hover:underline dark:text-gray-300">
                                                        {{ item.name }}
                                                    </h6>
                                                </a>
                                                <Button
                                                    type="button"
                                                    size="icon"
                                                    class="size-6 shrink-0 cursor-pointer rounded-full bg-red-50 hover:bg-red-100 dark:bg-red-500 dark:hover:bg-red-800"
                                                    @click="openDeleteDialog(item, index)"
                                                >
                                                    <Trash2 class="size-3 text-red-500 dark:text-white" />
                                                </Button>
                                            </div>
                                        </CardContent>
                                    </Card>
                                </div>

                                <!-- Upload lampiran baru -->
                                <div>
                                    <div class="flex items-center justify-between">
                                        <div class="grid gap-1">
                                            <Label for="lampiran">Tambah Lampiran Baru</Label>
                                            <p class="text-xs text-muted-foreground">Supported Format: PDF</p>
                                        </div>
                                        <Button
                                            id="lampiran"
                                            type="button"
                                            size="icon"
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
                                    <Card class="mt-2" :class="{ 'border-red-500': form.errors.lampiran }">
                                        <CardContent>
                                            <div v-if="form.lampiran.length === 0" class="text-center">
                                                <p class="text-sm text-muted-foreground">Belum ada lampiran baru. Klik tombol + untuk mengunggah.</p>
                                            </div>
                                            <div
                                                v-for="(item, index) in form.lampiran"
                                                :key="index"
                                                class="flex items-center justify-between py-4"
                                                :class="{ 'border-b dark:border-gray-500': index !== form.lampiran.length - 1 }"
                                            >
                                                <div class="mr-2 flex items-center gap-2">
                                                    <img class="block size-10 dark:hidden" src="../../../assets/media/pdf.svg" alt="pdf icon" />
                                                    <img class="hidden size-10 dark:block" src="../../../assets/media/pdf-dark.svg" alt="pdf icon" />
                                                    <div>
                                                        <h6 class="line-clamp-1 text-sm font-medium text-gray-800 dark:text-gray-300">
                                                            {{ item.name }}
                                                        </h6>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                                            {{ formatBytes(Number(item.size)) }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <Button
                                                    type="button"
                                                    size="icon"
                                                    class="size-6 shrink-0 cursor-pointer rounded-full bg-red-50 hover:bg-red-100 dark:bg-red-500 dark:hover:bg-red-800"
                                                    @click="form.lampiran.splice(index, 1)"
                                                >
                                                    <Trash2 class="size-3 text-red-500 dark:text-white" />
                                                </Button>
                                            </div>
                                        </CardContent>
                                    </Card>
                                    <InputError :message="form.errors.lampiran" class="mt-1" />
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

        <!-- Dialog Konfirmasi Hapus -->
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent>
                <DialogHeader class="space-y-3">
                    <DialogTitle>Hapus Lampiran</DialogTitle>
                    <DialogDescription>
                        Apakah Anda yakin ingin menghapus lampiran
                        <strong>{{ deleteTarget?.name }}</strong
                        >? Lampiran yang dihapus tidak dapat dikembalikan.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary"> Batal </Button>
                    </DialogClose>
                    <Button variant="destructive" @click="confirmDelete"> Hapus </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
