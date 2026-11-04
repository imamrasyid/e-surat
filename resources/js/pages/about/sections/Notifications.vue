<script setup lang="ts">
import { primaryLetter } from '../letterData';
</script>

<template>
    <section id="notifikasi" class="scroll-mt-28 space-y-6" aria-labelledby="about-notifications-title">
        <div data-section-head class="space-y-3 text-center">
            <p class="text-muted-foreground text-xs font-medium tracking-[0.18em] uppercase">Notifikasi</p>
            <h2 id="about-notifications-title" data-section-title tabindex="-1" class="text-2xl font-semibold tracking-tight sm:text-3xl">
                Pemberitahuan tiba di email dan Telegram.
            </h2>
            <p class="text-muted-foreground mx-auto max-w-[56ch] text-sm text-pretty sm:text-base">
                Surat masuk, disposisi, dan Nota Dinas dikirim ke email. Hubungkan Telegram dari Profil untuk menerima salinan di chat.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div
                data-notify
                class="bg-muted/30 space-y-3 rounded-2xl border p-4 sm:p-5"
                role="img"
                :aria-label="`Contoh pesan Telegram: Disposisi baru, perihal ${primaryLetter.subject}, pengirim ${primaryLetter.recipients[0].from}, baris Buka e-Surat, dan footer pesan otomatis sistem e-Surat.`"
            >
                <div class="flex items-center justify-between gap-2">
                    <span class="text-sm font-medium">Telegram</span>
                    <span class="text-muted-foreground text-[11px]">Kanal opsional</span>
                </div>

                <div class="bg-card dark:bg-input/30 space-y-1.5 rounded-xl border p-4 text-xs">
                    <p class="text-sm font-medium">Disposisi baru</p>
                    <p>Perihal: {{ primaryLetter.subject }}</p>
                    <p>Pengirim: {{ primaryLetter.recipients[0].from }}</p>
                    <p class="text-primary underline underline-offset-4">Buka e-Surat</p>
                    <p class="text-muted-foreground border-t pt-1.5">Pesan otomatis sistem e-Surat.</p>
                </div>
            </div>

            <div
                data-notify
                class="bg-muted/30 space-y-3 rounded-2xl border p-4 sm:p-5"
                role="img"
                :aria-label="`Contoh email: subjek Disposisi baru dengan perihal ${primaryLetter.subject}, sapaan Halo Bapak/Ibu, baris Disposisi baru telah masuk ke inbox jabatan Anda, pengirim ${primaryLetter.recipients[0].from}, tombol Buka e-Surat, dan footer otomatis.`"
            >
                <div class="flex items-center justify-between gap-2">
                    <span class="text-sm font-medium">Email</span>
                    <span class="text-muted-foreground truncate text-[11px]">Disposisi baru: {{ primaryLetter.subject }}</span>
                </div>

                <div class="bg-card dark:bg-input/30 space-y-2 rounded-xl border p-4 text-xs">
                    <p class="text-sm font-medium">Halo Bapak/Ibu,</p>
                    <p>Disposisi baru telah masuk ke inbox jabatan Anda.</p>
                    <p class="text-muted-foreground">Perihal: {{ primaryLetter.subject }}</p>
                    <p class="text-muted-foreground">Pengirim: {{ primaryLetter.recipients[0].from }}</p>
                    <span class="bg-primary text-primary-foreground inline-flex min-h-8 items-center rounded-md px-3 text-[11px] font-medium"
                        >Buka e-Surat</span
                    >
                    <p class="text-muted-foreground border-t pt-1.5">Email ini dikirim otomatis oleh sistem e-Surat.</p>
                </div>
            </div>
        </div>

        <p class="text-muted-foreground text-center text-[11px]">
            Contoh payload <code class="font-mono">InboxItemNotification</code> — halaman ini tidak mengirim notifikasi.
        </p>
    </section>
</template>
