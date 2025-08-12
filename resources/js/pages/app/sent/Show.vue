<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Separator } from '@/components/ui/separator';
import { categoryInbox } from '@/data/helpers';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    letter: any;
}>();

function getCategoryName(value: number) {
    const found = categoryInbox.find((cat) => cat.value === value);
    return found ? found.name : '-';
}
</script>

<template>
    <Head title="Lihat Surat Terkirim" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:justify-between">
                <HeadingSmall title="Surat Terkirim" description="Detail data surat terkirim" />
                <div class="sm:text-end">
                    <p class="text-xs text-gray-500">{{ letter.created_at }}</p>
                    <h6 class="text-sm">Oleh {{ letter.sender_user.name }}</h6>
                </div>
            </div>

            <Separator class="my-6" />

            <div class="space-y-2">
                <div class="flex flex-col lg:flex-row lg:space-x-12">
                    <aside class="w-full max-w-md lg:w-[150px]">
                        <h6 class="text-sm font-medium">Nomor Agenda</h6>
                    </aside>

                    <div class="flex-1">
                        <h6 class="text-sm dark:text-gray-300">{{ letter.agenda }}</h6>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row lg:space-x-12">
                    <aside class="w-full max-w-md lg:w-[150px]">
                        <h6 class="text-sm font-medium">Pengirim</h6>
                    </aside>

                    <div class="flex-1">
                        <h6 class="text-sm dark:text-gray-300">{{ letter.sender }}</h6>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row lg:space-x-12">
                    <aside class="w-full max-w-md lg:w-[150px]">
                        <h6 class="text-sm font-medium">Nomor Surat</h6>
                    </aside>

                    <div class="flex-1">
                        <h6 class="text-sm dark:text-gray-300">{{ letter.number }}</h6>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row lg:space-x-12">
                    <aside class="w-full max-w-md lg:w-[150px]">
                        <h6 class="text-sm font-medium">Perihal</h6>
                    </aside>

                    <div class="flex-1 sm:max-w-md">
                        <h6 class="text-sm dark:text-gray-300">{{ letter.subject }}</h6>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row lg:space-x-12">
                    <aside class="w-full max-w-md lg:w-[150px]">
                        <h6 class="text-sm font-medium">Tanggal Surat</h6>
                    </aside>

                    <div class="flex-1">
                        <h6 class="text-sm dark:text-gray-300">{{ letter.date }}</h6>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row lg:space-x-12">
                    <aside class="w-full max-w-md lg:w-[150px]">
                        <h6 class="text-sm font-medium">Tanggal Diterima</h6>
                    </aside>

                    <div class="flex-1">
                        <h6 class="text-sm dark:text-gray-300">{{ letter.receipt_date }}</h6>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row lg:space-x-12">
                    <aside class="w-full max-w-md lg:w-[150px]">
                        <h6 class="text-sm font-medium">Kategori</h6>
                    </aside>

                    <div class="flex-1">
                        <h6 class="text-sm dark:text-gray-300">{{ getCategoryName(Number(letter.category)) }}</h6>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row lg:space-x-12">
                    <aside class="w-full max-w-md lg:w-[150px]">
                        <h6 class="text-sm font-medium">Status Surat</h6>
                    </aside>

                    <div class="flex-1">
                        <div v-if="letter.status === 'open'" class="inline rounded-lg bg-green-50 px-3 py-1 text-sm text-green-500 dark:bg-gray-800">
                            Open
                        </div>
                        <div v-else class="inline rounded-lg bg-indigo-50 px-3 py-1 text-sm text-indigo-500 dark:bg-gray-800">Close</div>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <h6 class="text-sm font-medium">Penerima:</h6>
                <span>
                    {{ letter.recipients }}
                </span>
            </div>

            <div class="mt-6">
                <h6 class="text-sm font-medium">Lampiran:</h6>
                <span>
                    {{ letter.docs }}
                </span>
            </div>
        </div>
    </AppLayout>
</template>
