<script setup lang="ts">
import { CheckCircle2, Clock, FileText, Inbox, Mails, Search } from '@lucide/vue';
</script>

<template>
    <section class="relative mx-auto max-w-6xl px-3 pb-12 sm:px-6 sm:pb-20 lg:px-8">
        <!-- Mock App Frame -->
        <div
            class="border-border/80 bg-card/90 shadow-primary/5 relative rounded-xl border p-2 shadow-xl backdrop-blur-xl sm:rounded-2xl sm:p-4 sm:shadow-2xl"
        >
            <!-- Window Titlebar -->
            <div class="border-border/60 flex items-center justify-between border-b px-2 pb-2.5 sm:px-3 sm:pb-3">
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <span class="bg-destructive/80 size-2.5 rounded-full sm:size-3" />
                    <span class="bg-warning/80 size-2.5 rounded-full sm:size-3" />
                    <span class="bg-primary/80 size-2.5 rounded-full sm:size-3" />
                    <span class="text-muted-foreground ml-1.5 hidden text-xs font-semibold sm:inline md:ml-2"> e-Surat Hub • Antarmuka Terpadu </span>
                </div>
                <div
                    class="border-border/50 bg-background/60 text-muted-foreground flex max-w-[200px] items-center gap-1.5 rounded-md border px-2.5 py-1 text-xs sm:max-w-xs sm:gap-2 sm:px-3"
                >
                    <Search class="size-3.5 shrink-0" />
                    <span class="truncate">Cari surat, agenda, disposisi...</span>
                </div>
                <div class="text-muted-foreground hidden items-center gap-2 text-xs sm:flex">
                    <span class="bg-primary size-2 animate-ping rounded-full" />
                    <span>Real-time</span>
                </div>
            </div>

            <!-- Inner Dashboard Mock Preview -->
            <div class="mt-3 space-y-3 p-1 sm:mt-4 sm:space-y-4 sm:p-4">
                <!-- Mini Metric Ticker Cards -->
                <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-3 sm:gap-3">
                    <div class="border-primary/20 bg-primary/5 flex items-center gap-3 rounded-lg border p-3 sm:rounded-xl sm:p-3.5">
                        <div class="bg-primary/15 text-primary flex size-9 shrink-0 items-center justify-center rounded-lg sm:size-10">
                            <Inbox class="size-4.5 sm:size-5" />
                        </div>
                        <div>
                            <div class="text-muted-foreground text-[11px] font-medium sm:text-xs">Surat Masuk Bulan Ini</div>
                            <div class="text-foreground text-base font-bold sm:text-lg">
                                1.428 <span class="text-muted-foreground text-[11px] font-normal sm:text-xs">Surat</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 rounded-lg border border-amber-500/20 bg-amber-500/5 p-3 sm:rounded-xl sm:p-3.5">
                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-amber-500/15 text-amber-600 sm:size-10 dark:text-amber-400"
                        >
                            <Clock class="size-4.5 sm:size-5" />
                        </div>
                        <div>
                            <div class="text-muted-foreground text-[11px] font-medium sm:text-xs">Dalam Proses Disposisi</div>
                            <div class="text-foreground text-base font-bold sm:text-lg">
                                14 <span class="text-muted-foreground text-[11px] font-normal sm:text-xs">Surat Aktif</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 rounded-lg border border-emerald-500/20 bg-emerald-500/5 p-3 sm:rounded-xl sm:p-3.5">
                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-600 sm:size-10 dark:text-emerald-400"
                        >
                            <CheckCircle2 class="size-4.5 sm:size-5" />
                        </div>
                        <div>
                            <div class="text-muted-foreground text-[11px] font-medium sm:text-xs">Tingkat Penyelesaian</div>
                            <div class="text-foreground text-base font-bold sm:text-lg">
                                98.4% <span class="text-muted-foreground text-[11px] font-normal sm:text-xs">Tepat Waktu</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Simulated Correspondence Table / Records -->
                <div class="border-border/70 bg-background/50 overflow-hidden rounded-lg border sm:rounded-xl">
                    <div
                        class="border-border/60 bg-muted/30 text-muted-foreground border-b px-3 py-2 text-[11px] font-semibold sm:px-4 sm:py-2.5 sm:text-xs"
                    >
                        Daftar Surat & Status Disposisi Terkini
                    </div>

                    <div class="divide-border/40 divide-y text-xs">
                        <!-- Record 1 -->
                        <div
                            class="hover:bg-accent/40 flex flex-col gap-2 p-2.5 transition-colors sm:flex-row sm:items-center sm:justify-between sm:p-3"
                        >
                            <div class="flex items-start gap-2.5 sm:gap-3">
                                <div
                                    class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-md bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"
                                >
                                    <FileText class="size-3.5 sm:size-4" />
                                </div>
                                <div>
                                    <p class="text-foreground text-xs font-semibold sm:text-sm">Undangan Rapat Koordinasi Anggaran Tahun 2026</p>
                                    <p class="text-muted-foreground text-[11px] sm:text-xs">Kementerian Keuangan RI • No: S-1042/MK.02/2026</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-1.5 self-start sm:gap-2 sm:self-center">
                                <span
                                    class="rounded-md border border-emerald-500/20 bg-emerald-500/10 px-2 py-0.5 text-[10px] font-medium text-emerald-700 sm:px-2.5 sm:py-1 sm:text-[11px] dark:text-emerald-300"
                                >
                                    Undangan
                                </span>
                                <span
                                    class="inline-flex items-center gap-1 rounded-md bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-600 sm:px-2.5 sm:py-1 sm:text-[11px] dark:text-emerald-400"
                                >
                                    <span class="size-1.5 rounded-full bg-emerald-500" />
                                    Selesai Diproses
                                </span>
                            </div>
                        </div>

                        <!-- Record 2 -->
                        <div
                            class="hover:bg-accent/40 flex flex-col gap-2 p-2.5 transition-colors sm:flex-row sm:items-center sm:justify-between sm:p-3"
                        >
                            <div class="flex items-start gap-2.5 sm:gap-3">
                                <div
                                    class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400"
                                >
                                    <Mails class="size-3.5 sm:size-4" />
                                </div>
                                <div>
                                    <p class="text-foreground text-xs font-semibold sm:text-sm">Nota Dinas Pelaksanaan Uji Kompetensi Pegawai</p>
                                    <p class="text-muted-foreground text-[11px] sm:text-xs">Sekretariat Badan Kepegawaian • No: ND-031/BKP/2026</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-1.5 self-start sm:gap-2 sm:self-center">
                                <span
                                    class="rounded-md border border-amber-500/20 bg-amber-500/10 px-2 py-0.5 text-[10px] font-medium text-amber-700 sm:px-2.5 sm:py-1 sm:text-[11px] dark:text-amber-300"
                                >
                                    Disposisi
                                </span>
                                <span
                                    class="inline-flex items-center gap-1 rounded-md bg-amber-500/10 px-2 py-0.5 text-[10px] font-semibold text-amber-600 sm:px-2.5 sm:py-1 sm:text-[11px] dark:text-amber-400"
                                >
                                    <span class="size-1.5 animate-pulse rounded-full bg-amber-500" />
                                    Perlu Tindak Lanjut
                                </span>
                            </div>
                        </div>

                        <!-- Record 3 -->
                        <div
                            class="hover:bg-accent/40 flex flex-col gap-2 p-2.5 transition-colors sm:flex-row sm:items-center sm:justify-between sm:p-3"
                        >
                            <div class="flex items-start gap-2.5 sm:gap-3">
                                <div
                                    class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-md bg-sky-500/10 text-sky-600 dark:text-sky-400"
                                >
                                    <Inbox class="size-3.5 sm:size-4" />
                                </div>
                                <div>
                                    <p class="text-foreground text-xs font-semibold sm:text-sm">Penyampaian Laporan Kinerja Instansi Triwulan III</p>
                                    <p class="text-muted-foreground text-[11px] sm:text-xs">Inspektorat Utama • No: LP-210/INSP/2026</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-1.5 self-start sm:gap-2 sm:self-center">
                                <span
                                    class="rounded-md border border-sky-500/20 bg-sky-500/10 px-2 py-0.5 text-[10px] font-medium text-sky-700 sm:px-2.5 sm:py-1 sm:text-[11px] dark:text-sky-300"
                                >
                                    Surat Masuk
                                </span>
                                <span
                                    class="inline-flex items-center gap-1 rounded-md bg-sky-500/10 px-2 py-0.5 text-[10px] font-semibold text-sky-600 sm:px-2.5 sm:py-1 sm:text-[11px] dark:text-sky-400"
                                >
                                    Terarsip Digital
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
