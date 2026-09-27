<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Features from './partials/Features.vue';
import Footer from './partials/Footer.vue';
import Hero from './partials/Hero.vue';
import Navbar from './partials/Navbar.vue';
import Workflow from './partials/Workflow.vue';
</script>

<template>
    <Head title="e-Surat • Sistem Persuratan & Disposisi Elektronik" />

    <div class="bg-background text-foreground selection:bg-primary/20 selection:text-primary min-h-screen">
        <Navbar />
        <main>
            <Hero />
            <Features />
            <Workflow />
        </main>
        <Footer />
    </div>
</template>
