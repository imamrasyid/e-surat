<script setup lang="ts">
const steps = [
    'Pertama, nomor agenda terbit otomatis.',
    'Kedua, pimpinan memberi disposisi.',
    'Ketiga, pelaksana menindaklanjuti.',
    'Terakhir, surat ditutup dan diarsipkan.',
];
</script>

<template>
    <section id="alur" class="wl-how" aria-labelledby="wl-how-title">
        <div class="wl-how-inner">
            <h2 id="wl-how-title" class="wl-h2 wl-how-title text-(--wl-accent)">Empat langkah, satu catatan</h2>

            <ol class="wl-steps">
                <li v-for="step in steps" :key="step" class="wl-step">
                    {{ step }}
                </li>
            </ol>

            <p class="wl-how-note">
                Nomor mengikuti kode klasifikasi dan tahun. Setiap surat punya satu catatan yang bisa ditelusuri dari kotak masuk sampai arsip.
            </p>
        </div>
    </section>
</template>
