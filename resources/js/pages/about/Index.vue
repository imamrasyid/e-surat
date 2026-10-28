<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import '../welcome/welcome.css';
import Closing from '../welcome/Closing.vue';
import Faq from '../welcome/Faq.vue';
import FeaturesPanel from '../welcome/FeaturesPanel.vue';
import HeroBoard from '../welcome/HeroBoard.vue';
import HowItWorks from '../welcome/HowItWorks.vue';
import ShowcaseStates from '../welcome/ShowcaseStates.vue';
import { mountAboutMotion } from './about-motion';

const page = usePage();
const signedIn = computed(() => Boolean(page.props.auth?.user));
const actionHref = computed(() => (signedIn.value ? route('dashboard') : route('login')));

const root = ref<HTMLElement | null>(null);
let motion: ReturnType<typeof mountAboutMotion> | null = null;

onMounted(() => {
    if (root.value) {
        motion = mountAboutMotion(root.value);
    }
});

onBeforeUnmount(() => motion?.revert());
</script>

<template>
    <Head title="Tentang e-Surat · Sistem Persuratan dan Disposisi Elektronik" />

    <div id="top" ref="root" class="wl-page">
        <main>
            <HeroBoard :signed-in="signedIn" :action-href="actionHref" about />
            <ShowcaseStates />
            <HowItWorks />
            <FeaturesPanel />
            <Faq />
        </main>

        <Closing :signed-in="signedIn" :action-href="actionHref" about />
    </div>
</template>
