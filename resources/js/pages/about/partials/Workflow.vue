<script setup lang="ts">
import { Archive, ArrowDown, ArrowRight, CheckCircle, FileCheck, Mails, PenTool } from '@lucide/vue';

const steps = [
    {
        number: '01',
        icon: PenTool,
        title: 'Catat & Registrasi',
        description:
            'Surat masuk diterima dan dicatat oleh bagian persuratan. Nomor agenda langsung terbit otomatis, lalu lampiran digitalnya diunggah.',
        color: 'text-emerald-600 dark:text-emerald-400 bg-emerald-500/10',
    },
    {
        number: '02',
        icon: Mails,
        title: 'Disposisi Pimpinan',
        description:
            'Pimpinan membaca surat langsung dari gawai dan meneruskan instruksi ke bawahan. Prosesnya berjenjang, bisa sampai beberapa level.',
        color: 'text-amber-600 dark:text-amber-400 bg-amber-500/10',
    },
    {
        number: '03',
        icon: FileCheck,
        title: 'Tindak Lanjut',
        description: 'Tim pelaksana menerima instruksi, lalu menindaklanjuti — bisa berupa surat balasan, nota dinas, atau tindakan lainnya.',
        color: 'text-sky-600 dark:text-sky-400 bg-sky-500/10',
    },
    {
        number: '04',
        icon: Archive,
        title: 'Selesai & Arsip',
        description: 'Surat ditandai selesai, seluruh riwayat penanganannya tersimpan rapi di arsip digital. Aman dan mudah dicari kapan saja.',
        color: 'text-violet-600 dark:text-violet-400 bg-violet-500/10',
    },
];
</script>

<template>
    <section id="alur-kerja" class="py-12 sm:py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="mx-auto max-w-3xl text-center">
                <span
                    class="border-primary/20 bg-primary/10 text-primary inline-flex items-center gap-1 rounded-full border px-3 py-1 text-xs font-semibold"
                >
                    Cara Kerjanya
                </span>
                <h2 class="text-foreground mt-4 text-2xl font-bold tracking-tight sm:text-3xl lg:text-4xl">Empat Langkah Sederhana, Surat Beres</h2>
                <p class="text-muted-foreground mt-3 text-sm leading-relaxed sm:text-base">
                    Dari surat diterima sampai terarsip — setiap tahapan tercatat jelas, jadi tidak ada lagi surat yang terselip atau lupa
                    ditindaklanjuti.
                </p>
            </div>

            <!-- Workflow Steps Grid -->
            <div class="mt-10 grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2 lg:grid-cols-4 lg:gap-8">
                <div
                    v-for="(step, index) in steps"
                    :key="step.number"
                    class="border-border/60 bg-card hover:border-primary/40 relative flex flex-col justify-between rounded-xl border p-5 shadow-xs transition-all hover:shadow-sm sm:rounded-2xl sm:p-6"
                >
                    <div>
                        <!-- Step Indicator -->
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground/40 font-mono text-xl font-black sm:text-2xl">
                                {{ step.number }}
                            </span>
                            <div class="flex size-10 items-center justify-center rounded-lg sm:size-11" :class="step.color">
                                <component :is="step.icon" class="size-5 sm:size-5.5" />
                            </div>
                        </div>

                        <!-- Step Content -->
                        <h3 class="text-foreground mt-4 text-base font-bold sm:mt-5 sm:text-lg">
                            {{ step.title }}
                        </h3>
                        <p class="text-muted-foreground mt-2 text-xs leading-relaxed sm:text-sm">
                            {{ step.description }}
                        </p>
                    </div>

                    <!-- Arrow connector for desktop -->
                    <div
                        v-if="index < steps.length - 1"
                        class="border-border bg-background text-muted-foreground absolute top-1/2 -right-4 z-10 hidden size-8 -translate-y-1/2 items-center justify-center rounded-full border shadow-xs lg:flex"
                    >
                        <ArrowRight class="size-3.5" />
                    </div>

                    <!-- Arrow down indicator for mobile -->
                    <div v-if="index < steps.length - 1" class="text-muted-foreground/50 mt-4 flex justify-center md:hidden">
                        <ArrowDown class="size-4" />
                    </div>
                </div>
            </div>

            <!-- Security & Compliance Banner -->
            <div
                id="keamanan"
                class="border-primary/20 from-primary/10 via-card to-background mt-12 rounded-xl border bg-gradient-to-br p-5 sm:mt-16 sm:rounded-2xl sm:p-8"
            >
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="space-y-2">
                        <div class="text-primary flex items-center gap-2 text-xs font-semibold sm:text-sm">
                            <CheckCircle class="size-4 shrink-0 sm:size-4.5" />
                            <span>Keamanan yang Bisa Diandalkan</span>
                        </div>
                        <h3 class="text-foreground text-lg font-bold sm:text-xl lg:text-2xl">Data dan Dokumen Anda Terlindungi dengan Baik</h3>
                        <p class="text-muted-foreground max-w-2xl text-xs leading-relaxed sm:text-sm">
                            Setiap akun diamankan dengan verifikasi dua langkah (2FA), dan Anda juga bisa login tanpa password menggunakan Passkey.
                            Siapa bisa mengakses apa? Diatur sesuai wewenang jabatan masing-masing.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 sm:gap-3 lg:justify-center">
                        <span
                            class="border-border bg-background/80 text-foreground rounded-lg border px-3 py-1.5 text-xs font-semibold shadow-xs sm:px-3.5 sm:py-2"
                        >
                            🔒 Verifikasi 2 Langkah
                        </span>
                        <span
                            class="border-border bg-background/80 text-foreground rounded-lg border px-3 py-1.5 text-xs font-semibold shadow-xs sm:px-3.5 sm:py-2"
                        >
                            🔑 Login Tanpa Password
                        </span>
                        <span
                            class="border-border bg-background/80 text-foreground rounded-lg border px-3 py-1.5 text-xs font-semibold shadow-xs sm:px-3.5 sm:py-2"
                        >
                            🛡️ Akses Sesuai Jabatan
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
