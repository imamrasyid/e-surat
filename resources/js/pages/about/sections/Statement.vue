<script setup lang="ts">
const statement =
    'Setiap surat punya satu nomor agenda. Disposisi dan nota dinas mengalir ke jabatan yang tepat. Waktu kirim dan baca tercatat sampai surat ditutup.';
</script>

<template>
    <section class="py-4 sm:py-8" aria-labelledby="about-statement-title">
        <h2 id="about-statement-title" data-section-head class="text-muted-foreground text-center text-xs font-medium tracking-[0.18em] uppercase">
            Satu jejak, dari masuk sampai ditutup.
        </h2>

        <p
            data-statement-visual
            aria-hidden="true"
            class="mx-auto mt-8 max-w-4xl text-center text-2xl leading-snug font-medium tracking-tight sm:text-4xl"
        >
            {{ statement }}
        </p>

        <p class="sr-only">{{ statement }}</p>
    </section>
</template>
