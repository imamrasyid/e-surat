<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { mountAboutMotion } from './about-motion';
import SiteDock from './SiteDock.vue';
import SiteHeader from './SiteHeader.vue';
import Closing from './sections/Closing.vue';
import Faq from './sections/Faq.vue';
import FeaturesPanel from './sections/FeaturesPanel.vue';
import Hero from './sections/Hero.vue';
import LetterStrip from './sections/LetterStrip.vue';
import Notifications from './sections/Notifications.vue';
import RolesSecurity from './sections/RolesSecurity.vue';
import Statement from './sections/Statement.vue';
import StepStack from './sections/StepStack.vue';

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

    <div id="top" ref="root" class="bg-background text-foreground min-h-svh">
        <div class="mx-auto w-full max-w-6xl px-4 pt-3 sm:px-6 sm:pt-4">
            <SiteHeader :signed-in="signedIn" :action-href="actionHref" />
        </div>

        <SiteDock :signed-in="signedIn" :action-href="actionHref" />

        <main class="mx-auto flex w-full max-w-6xl flex-col gap-16 px-4 pt-6 pb-16 sm:gap-24 sm:px-6 sm:pt-8 sm:pb-24">
            <Hero :signed-in="signedIn" :action-href="actionHref" />
            <LetterStrip />
            <Statement />
            <StepStack />
            <FeaturesPanel />
            <Notifications />
            <RolesSecurity />
            <Faq />
            <Closing :signed-in="signedIn" :action-href="actionHref" />
        </main>
    </div>
</template>
