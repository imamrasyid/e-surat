<script setup lang="ts">
const questions = [
    {
        q: 'Siapa yang bisa membuka surat?',
        a: 'Surat masuk ke inbox jabatan penerima. Menu dan aksi yang tersedia mengikuti izin peran pengguna.',
    },
    {
        q: 'Bagaimana disposisi berjenjang dicatat?',
        a: 'Setiap penerusan menyimpan jabatan pengirim, jabatan penerima, instruksi, tindak lanjut, tembusan, waktu kirim, dan waktu baca.',
    },
    {
        q: 'Lampiran apa yang bisa dikirim?',
        a: 'Surat masuk wajib memiliki minimal satu PDF. Disposisi dan Nota Dinas dapat menyertakan lampiran PDF.',
    },
    {
        q: 'Apa yang terjadi setelah surat ditutup?',
        a: 'Nota Dinas penutup mengubah status surat menjadi Close. Surat tidak lagi dapat diedit atau menerima disposisi/Nota Dinas baru dari aksi yang tersedia.',
    },
    {
        q: 'Bagaimana menerima pemberitahuan?',
        a: 'Email dikirim bila alamat email tersedia. Telegram dapat dihubungkan dari Profil melalui tombol Hubungkan Telegram; notifikasi mencakup surat masuk, disposisi, dan Nota Dinas.',
    },
    {
        q: 'Mengapa undangan punya inbox sendiri?',
        a: 'Kategori Undangan dipisahkan dari Primary dan jumlahnya muncul sebagai badge di sidebar.',
    },
];
</script>

<template>
    <section id="tanya" class="scroll-mt-28 space-y-8" aria-labelledby="about-faq-title">
        <div data-section-head class="space-y-3 text-center">
            <p class="text-muted-foreground text-xs font-medium tracking-[0.18em] uppercase">Tanya</p>
            <h2 id="about-faq-title" data-section-title tabindex="-1" class="text-2xl font-semibold tracking-tight sm:text-3xl">
                Pertanyaan yang sering muncul.
            </h2>
        </div>

        <dl class="grid grid-cols-1 gap-x-10 gap-y-6 sm:grid-cols-2">
            <div v-for="item in questions" :key="item.q" data-faq class="border-t pt-4">
                <dt class="text-sm font-medium">{{ item.q }}</dt>
                <dd class="text-muted-foreground mt-2 text-sm leading-relaxed">{{ item.a }}</dd>
            </div>
        </dl>
    </section>
</template>
