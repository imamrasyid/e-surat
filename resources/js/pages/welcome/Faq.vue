<script setup lang="ts">
const questions = [
    {
        q: 'Apakah surat lama bisa dicari lagi?',
        a: 'Bisa. Cari dari perihal, pengirim, nomor surat, atau nomor agenda. Surat yang sudah ditutup tetap ada di arsip.',
    },
    {
        q: 'Siapa yang boleh membuka surat?',
        a: 'Akses mengikuti jabatan dan izin yang diberikan. Penerima disposisi melihat surat yang ditujukan kepadanya; pimpinan melihat disposisi yang ia kirim.',
    },
    {
        q: 'Bagaimana disposisi berjenjang dicatat?',
        a: 'Setiap penerusan mencatat pengirim, penerima, instruksi, waktu kirim, dan waktu baca. Riwayatnya tetap ada di halaman surat.',
    },
    {
        q: 'Apa saja yang bisa dilampirkan?',
        a: 'Berkas PDF surat asli. Lampiran disimpan bersama nomor agenda, jadi tidak ada berkas yang terpisah dari catatannya.',
    },
    {
        q: 'Apakah harus masuk dengan akun kantor?',
        a: 'Ya. Gunakan akun yang diberikan administrator. Verifikasi dua langkah dan passkey tersedia di pengaturan keamanan.',
    },
    {
        q: 'Bagaimana surat undangan dibedakan?',
        a: 'Kategori Undangan punya kotak masuk sendiri, terpisah dari surat utama, dengan hitungan yang tampil di sidebar.',
    },
];
</script>

<template>
    <section id="tanya" class="wl-section wl-faq" aria-labelledby="wl-faq-title">
        <div class="wl-faq-grid">
            <h2 id="wl-faq-title" class="wl-faq-q wl-faq-heading">Pertanyaan yang sering muncul</h2>
            <span class="wl-faq-blank" aria-hidden="true"></span>

            <template v-for="item in questions" :key="item.q">
                <h3 class="wl-faq-q">{{ item.q }}</h3>
                <p class="wl-faq-a">{{ item.a }}</p>
            </template>
        </div>
    </section>
</template>
