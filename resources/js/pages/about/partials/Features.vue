<script setup lang="ts">
import { Clock, History, Inbox, Mails, SendHorizonal, ShieldCheck, StickyNote } from '@lucide/vue';

const features = [
    {
        icon: Inbox,
        title: 'Surat Masuk & Klasifikasi',
        description:
            'Surat masuk langsung tercatat rapi — nomor agenda otomatis, kategori surat jelas, dan lampiran digital tersimpan di satu tempat.',
        badge: 'Agenda Otomatis',
        theme: {
            iconBg: 'bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 group-hover:bg-emerald-600 group-hover:text-white',
            badge: 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border-emerald-500/20',
            cardHover: 'hover:border-emerald-500/40 hover:shadow-emerald-500/5',
            titleHover: 'group-hover:text-emerald-600 dark:group-hover:text-emerald-400',
            tag: 'text-emerald-600 dark:text-emerald-400',
        },
    },
    {
        icon: SendHorizonal,
        title: 'Compose & Distribusi Surat',
        description: 'Buat draf surat, pilih penerima, dan kirim — semuanya digital. Tidak perlu lagi cetak dan antar surat secara manual.',
        badge: 'Tanpa Kertas',
        theme: {
            iconBg: 'bg-sky-500/10 dark:bg-sky-500/20 text-sky-600 dark:text-sky-400 group-hover:bg-sky-600 group-hover:text-white',
            badge: 'bg-sky-500/10 text-sky-700 dark:text-sky-300 border-sky-500/20',
            cardHover: 'hover:border-sky-500/40 hover:shadow-sky-500/5',
            titleHover: 'group-hover:text-sky-600 dark:group-hover:text-sky-400',
            tag: 'text-sky-600 dark:text-sky-400',
        },
    },
    {
        icon: Mails,
        title: 'Disposisi Pimpinan',
        description:
            'Pimpinan bisa langsung baca surat dan memberikan instruksi disposisi dari mana saja — cepat, tanpa harus menunggu berkas fisik.',
        badge: 'Berjenjang',
        theme: {
            iconBg: 'bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 group-hover:bg-amber-600 group-hover:text-white',
            badge: 'bg-amber-500/10 text-amber-700 dark:text-amber-300 border-amber-500/20',
            cardHover: 'hover:border-amber-500/40 hover:shadow-amber-500/5',
            titleHover: 'group-hover:text-amber-600 dark:group-hover:text-amber-400',
            tag: 'text-amber-600 dark:text-amber-400',
        },
    },
    {
        icon: StickyNote,
        title: 'Nota Dinas & Memo',
        description: 'Koordinasi antar unit kerja jadi lebih terstruktur. Setiap nota dinas dan memo tercatat, jadi tidak ada yang terlewat.',
        badge: 'Koordinasi Internal',
        theme: {
            iconBg: 'bg-violet-500/10 dark:bg-violet-500/20 text-violet-600 dark:text-violet-400 group-hover:bg-violet-600 group-hover:text-white',
            badge: 'bg-violet-500/10 text-violet-700 dark:text-violet-300 border-violet-500/20',
            cardHover: 'hover:border-violet-500/40 hover:shadow-violet-500/5',
            titleHover: 'group-hover:text-violet-600 dark:group-hover:text-violet-400',
            tag: 'text-violet-600 dark:text-violet-400',
        },
    },
    {
        icon: History,
        title: 'Timeline & Riwayat',
        description: 'Selalu tahu surat sedang di mana dan siapa yang menangani. Riwayat lengkap dari diterima sampai selesai, semuanya terekam.',
        badge: 'Transparan',
        theme: {
            iconBg: 'bg-teal-500/10 dark:bg-teal-500/20 text-teal-600 dark:text-teal-400 group-hover:bg-teal-600 group-hover:text-white',
            badge: 'bg-teal-500/10 text-teal-700 dark:text-teal-300 border-teal-500/20',
            cardHover: 'hover:border-teal-500/40 hover:shadow-teal-500/5',
            titleHover: 'group-hover:text-teal-600 dark:group-hover:text-teal-400',
            tag: 'text-teal-600 dark:text-teal-400',
        },
    },
    {
        icon: ShieldCheck,
        title: 'Keamanan Akun',
        description: 'Akun Anda dilindungi verifikasi ganda (2FA) dan bisa login tanpa password lewat Passkey. Hak akses diatur sesuai jabatan.',
        badge: 'Aman & Terpercaya',
        theme: {
            iconBg: 'bg-rose-500/10 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 group-hover:bg-rose-600 group-hover:text-white',
            badge: 'bg-rose-500/10 text-rose-700 dark:text-rose-300 border-rose-500/20',
            cardHover: 'hover:border-rose-500/40 hover:shadow-rose-500/5',
            titleHover: 'group-hover:text-rose-600 dark:group-hover:text-rose-400',
            tag: 'text-rose-600 dark:text-rose-400',
        },
    },
];
</script>

<template>
    <section id="fitur" class="border-border/50 bg-muted/20 border-t py-12 sm:py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="mx-auto max-w-3xl text-center">
                <span
                    class="border-primary/20 bg-primary/10 text-primary inline-flex items-center gap-1 rounded-full border px-3 py-1 text-xs font-semibold"
                >
                    Apa Saja yang Bisa Dilakukan?
                </span>
                <h2 class="text-foreground mt-4 text-2xl font-bold tracking-tight sm:text-3xl lg:text-4xl">
                    Semua Kebutuhan Persuratan, Satu Aplikasi
                </h2>
                <p class="text-muted-foreground mt-3 text-sm leading-relaxed sm:text-base">
                    Tidak perlu lagi repot dengan tumpukan kertas atau berkas yang tercecer. e-Surat menyederhanakan seluruh alur persuratan dinas
                    Anda dari awal sampai akhir.
                </p>
            </div>

            <!-- Features Grid -->
            <div class="mt-10 grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3">
                <div
                    v-for="feature in features"
                    :key="feature.title"
                    class="group border-border/70 bg-card relative flex flex-col justify-between rounded-xl border p-5 shadow-xs transition-all duration-300 hover:-translate-y-1 hover:shadow-md sm:rounded-2xl sm:p-6"
                    :class="feature.theme.cardHover"
                >
                    <div>
                        <!-- Icon & Badge -->
                        <div class="flex items-center justify-between">
                            <div
                                class="flex size-11 items-center justify-center rounded-xl transition-colors sm:size-12"
                                :class="feature.theme.iconBg"
                            >
                                <component :is="feature.icon" class="size-5 sm:size-6" />
                            </div>
                            <span class="rounded-full border px-2.5 py-0.5 text-[11px] font-medium" :class="feature.theme.badge">
                                {{ feature.badge }}
                            </span>
                        </div>

                        <!-- Title & Description -->
                        <h3 class="text-foreground mt-4 text-base font-bold transition-colors sm:mt-5 sm:text-lg" :class="feature.theme.titleHover">
                            {{ feature.title }}
                        </h3>
                        <p class="text-muted-foreground mt-2 text-xs leading-relaxed sm:text-sm">
                            {{ feature.description }}
                        </p>
                    </div>

                    <div
                        class="mt-5 flex items-center gap-1.5 text-xs font-semibold opacity-80 transition-opacity group-hover:opacity-100 sm:mt-6"
                        :class="feature.theme.tag"
                    >
                        <span>Sudah Terintegrasi</span>
                        <Clock class="size-3.5" />
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
