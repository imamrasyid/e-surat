<script setup lang="ts">
import TonePanel from '../components/TonePanel.vue';
import type { Tone } from '../tone';

type Role = {
    id: string;
    title: string;
    tone: Tone;
    body: string;
};

const roles: Role[] = [
    {
        id: 'pengelola',
        title: 'Pengelola',
        tone: 'primary',
        body: 'Mencatat surat lewat Compose, memantau Surat Terkirim, mengelola disposisi dan Nota Dinas, tindak lanjut, klasifikasi, serta menutup surat melalui izin memo.close.',
    },
    {
        id: 'pengguna',
        title: 'Pengguna',
        tone: 'info',
        body: 'Membaca inbox Utama dan Undangan, lalu membuat atau membaca disposisi dan Nota Dinas sesuai jabatannya.',
    },
    {
        id: 'super-admin',
        title: 'Super Admin',
        tone: 'warning',
        body: 'Melewati pemeriksaan izin melalui Gate::before dan mengelola akses pengguna serta master data.',
    },
    {
        id: 'keamanan',
        title: 'Masuk dengan aman',
        tone: 'success',
        body: 'Verifikasi dua langkah dan passkey tersedia di pengaturan keamanan akun.',
    },
];
</script>

<template>
    <section id="peran" class="scroll-mt-28 space-y-6" aria-labelledby="about-roles-title">
        <div data-section-head class="space-y-3 text-center">
            <p class="text-muted-foreground text-xs font-medium tracking-[0.18em] uppercase">Peran</p>
            <h2 id="about-roles-title" data-section-title tabindex="-1" class="text-2xl font-semibold tracking-tight sm:text-3xl">
                Akses mengikuti peran dan jabatan.
            </h2>
            <p class="text-muted-foreground mx-auto max-w-[56ch] text-sm text-pretty sm:text-base">
                Menu mengikuti izin, sementara surat masuk ke inbox jabatan penerima.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <TonePanel v-for="role in roles" :key="role.id" :tone="role.tone" data-role-card>
                <div class="space-y-2 p-5 sm:p-6">
                    <h3 class="text-base font-semibold tracking-tight">{{ role.title }}</h3>
                    <p class="text-foreground/80 text-sm leading-relaxed">{{ role.body }}</p>
                </div>
            </TonePanel>
        </div>

        <div data-role-card class="bg-card dark:bg-input/30 rounded-2xl border p-5 sm:p-6">
            <h3 class="text-base font-semibold tracking-tight">Inbox melekat pada jabatan</h3>
            <p class="text-muted-foreground mt-2 max-w-[80ch] text-sm leading-relaxed">
                Disposisi dan Nota Dinas menyimpan jabatan tujuan, jadi item masuk ke inbox jabatan penerima, bukan ke akun pribadi.
            </p>
        </div>
    </section>
</template>
