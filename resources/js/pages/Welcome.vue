<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import { useAppearance } from '@/composables/useAppearance';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, Moon, Sun } from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { mountWelcomeGate } from './welcome/gate-motion';
import { cn } from 'cn';
import { buttonVariants } from '@/components/ui/button';

const page = usePage();
const signedIn = computed(() => Boolean(page.props.auth?.user));
const actionHref = computed(() => (signedIn.value ? route('dashboard') : route('login')));
const { resolvedAppearance, updateAppearance } = useAppearance();

function toggleTheme() {
    updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
}

const year = new Date().getFullYear();
const gate = ref<HTMLElement | null>(null);
let motion: ReturnType<typeof mountWelcomeGate> | null = null;

onMounted(() => {
    if (gate.value) {
        motion = mountWelcomeGate(gate.value);
    }
});

onBeforeUnmount(() => motion?.revert());
</script>

<template>
    <Head title="e-Surat · Selamat Datang" />

    <div id="top" ref="gate" class="bg-background text-foreground min-h-svh">
        <div class="mx-auto flex min-h-svh w-full max-w-7xl flex-col gap-4 px-4 py-4 sm:gap-6 sm:px-6 sm:py-6">
            <header class="bg-card dark:bg-input/30 flex flex-wrap items-center justify-between gap-4 rounded-xl border px-4 py-3">
                <Link
                    :href="route('home')"
                    class="focus-visible:ring-ring focus-visible:ring-offset-background flex items-center gap-2.5 transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none"
                    aria-label="e-Surat"
                    data-load
                >
                    <AppLogo />
                </Link>

                <nav class="flex items-center gap-1 sm:gap-2" aria-label="Navigasi utama">
                    <Link :href="route('about')" :class="cn(buttonVariants({ variant: 'link', size: 'sm' }), 'text-foreground')" data-load>
                        Tentang aplikasi
                    </Link>
                    <Link :href="actionHref" :class="cn(buttonVariants({ variant: 'link', size: 'sm' }), 'text-foreground')" data-load>
                        {{ signedIn ? 'Dashboard' : 'Masuk' }}
                    </Link>
                    <button
                        type="button"
                        class="focus-visible:ring-ring focus-visible:ring-offset-background hover:bg-accent hover:text-accent-foreground dark:bg-input/30 bg-background text-foreground ml-1 grid size-10 place-items-center rounded-lg border transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none"
                        :aria-label="resolvedAppearance === 'dark' ? 'Ganti ke mode terang' : 'Ganti ke mode gelap'"
                        data-load
                        @click="toggleTheme"
                    >
                        <Sun v-if="resolvedAppearance === 'dark'" class="size-4" aria-hidden="true" />
                        <Moon v-else class="size-4" aria-hidden="true" />
                    </button>
                </nav>
            </header>

            <main class="flex flex-1 flex-col justify-center">
                <section class="grid gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] lg:gap-8" aria-labelledby="wl-gate-title">
                    <div class="dark:bg-input/30 bg-card flex flex-col justify-center gap-6 rounded-xl border p-6 sm:gap-8 sm:p-10 lg:p-12">
                        <h1
                            id="wl-gate-title"
                            class="max-w-[12ch] text-4xl leading-[1.02] font-semibold tracking-tighter text-balance sm:text-5xl lg:text-6xl"
                            data-gate-reveal
                        >
                            Selamat datang
                        </h1>
                        <p class="text-muted-foreground max-w-[52ch] text-base leading-relaxed text-pretty sm:text-lg" data-gate-reveal>
                            e-Surat, sistem persuratan dan disposisi elektronik untuk tata kelola surat yang lebih cepat, rapi, dan efisien.
                        </p>
                        <div data-gate-reveal>
                            <Link :href="actionHref" :class="cn(buttonVariants({ variant: 'default', size: 'lg' }))">
                                <span>{{ signedIn ? 'Buka dashboard' : 'Masuk ke sistem' }}</span>
                                <ArrowRight class="size-4" aria-hidden="true" />
                            </Link>
                        </div>
                    </div>

                    <div
                        class="wl-gate-accent bg-primary text-primary-foreground relative grid place-items-center overflow-clip rounded-xl border p-5 sm:p-6"
                    >
                        <svg class="wl-scene aspect-square w-full max-w-120" viewBox="0 0 400 400" fill="none" aria-hidden="true">
                            <defs>
                                <linearGradient id="wl-stage" x1="0" y1="0" x2="400" y2="400" gradientUnits="userSpaceOnUse">
                                    <stop offset="0" stop-color="#ffffff" stop-opacity=".12" />
                                    <stop offset=".45" stop-color="#ffffff" stop-opacity="0" />
                                    <stop offset="1" stop-color="#00240f" stop-opacity=".22" />
                                </linearGradient>
                                <radialGradient id="wl-glow" cx="50%" cy="46%" r="56%">
                                    <stop offset="0" stop-color="#ffffff" stop-opacity=".18" />
                                    <stop offset="1" stop-color="#ffffff" stop-opacity="0" />
                                </radialGradient>
                                <radialGradient id="wl-ground-shadow" cx="50%" cy="50%" r="50%">
                                    <stop offset="0" stop-color="#03180c" stop-opacity=".5" />
                                    <stop offset="1" stop-color="#03180c" stop-opacity="0" />
                                </radialGradient>
                                <linearGradient id="wl-trail" x1="46" y1="46" x2="356" y2="356" gradientUnits="userSpaceOnUse">
                                    <stop offset="0" stop-color="#e6ffe9" stop-opacity=".35" />
                                    <stop offset=".45" stop-color="#c8ffd6" stop-opacity=".85" />
                                    <stop offset="1" stop-color="#f6fff0" stop-opacity=".45" />
                                </linearGradient>
                                <linearGradient id="wl-paper" x1="140" y1="118" x2="252" y2="300" gradientUnits="userSpaceOnUse">
                                    <stop offset="0" stop-color="#f8fff1" />
                                    <stop offset=".55" stop-color="#e7f8dc" />
                                    <stop offset="1" stop-color="#cdeac2" />
                                </linearGradient>
                                <linearGradient id="wl-paper-side" x1="238" y1="150" x2="256" y2="296" gradientUnits="userSpaceOnUse">
                                    <stop offset="0" stop-color="#3fae67" />
                                    <stop offset="1" stop-color="#0a3a20" />
                                </linearGradient>
                                <linearGradient id="wl-paper-base" x1="150" y1="296" x2="250" y2="302" gradientUnits="userSpaceOnUse">
                                    <stop offset="0" stop-color="#0f4a2a" />
                                    <stop offset="1" stop-color="#041d10" />
                                </linearGradient>
                                <linearGradient id="wl-fold" x1="210" y1="118" x2="238" y2="146" gradientUnits="userSpaceOnUse">
                                    <stop offset="0" stop-color="#d8efcc" />
                                    <stop offset="1" stop-color="#a9d6a0" />
                                </linearGradient>
                                <radialGradient id="wl-seal-face" cx="38%" cy="30%" r="78%">
                                    <stop offset="0" stop-color="#ffe39a" />
                                    <stop offset=".5" stop-color="#e8ac3c" />
                                    <stop offset="1" stop-color="#8a4f06" />
                                </radialGradient>
                                <linearGradient id="wl-plane-light" x1="82" y1="11" x2="298" y2="130" gradientUnits="userSpaceOnUse">
                                    <stop offset="0" stop-color="#f8fff0" />
                                    <stop offset="1" stop-color="#b6f0bd" />
                                </linearGradient>
                                <linearGradient id="wl-plane-mid" x1="74" y1="107" x2="291" y2="131" gradientUnits="userSpaceOnUse">
                                    <stop offset="0" stop-color="#8fe8a4" />
                                    <stop offset="1" stop-color="#2f9e52" />
                                </linearGradient>
                                <linearGradient id="wl-plane-body" x1="1" y1="58" x2="298" y2="130" gradientUnits="userSpaceOnUse">
                                    <stop offset="0" stop-color="#57c47a" />
                                    <stop offset="1" stop-color="#08301b" />
                                </linearGradient>
                                <linearGradient id="wl-plane-shade" x1="82" y1="35" x2="298" y2="130" gradientUnits="userSpaceOnUse">
                                    <stop offset="0" stop-color="#0f4a2a" />
                                    <stop offset="1" stop-color="#03200f" />
                                </linearGradient>
                                <path
                                    id="wl-seal-alt"
                                    d="M258.00 213.60C262.29 213.60 267.04 213.38 270.86 214.96C274.68 216.54 277.88 220.06 280.91 223.09C283.94 226.12 287.46 229.32 289.04 233.14C290.62 236.96 290.40 241.71 290.40 246.00C290.40 250.29 290.62 255.04 289.04 258.86C287.46 262.68 283.94 265.88 280.91 268.91C277.88 271.94 274.68 275.46 270.86 277.04C267.04 278.62 262.29 278.40 258.00 278.40C253.71 278.40 248.96 278.62 245.14 277.04C241.32 275.46 238.12 271.94 235.09 268.91C232.06 265.88 228.54 262.68 226.96 258.86C225.38 255.04 225.60 250.29 225.60 246.00C225.60 241.71 225.38 236.96 226.96 233.14C228.54 229.32 232.06 226.12 235.09 223.09C238.12 220.06 241.32 216.54 245.14 214.96C248.96 213.38 253.71 213.60 258.00 213.60Z"
                                />
                            </defs>

                            <ellipse cx="200" cy="198" rx="186" ry="178" fill="url(#wl-glow)" />

                            <ellipse class="wl-ground" cx="200" cy="322" rx="138" ry="26" stroke="#eafff1" stroke-opacity=".22" stroke-width="1" />
                            <ellipse class="wl-doc-shadow" cx="206" cy="316" rx="96" ry="15" fill="url(#wl-ground-shadow)" />

                            <path
                                id="wl-agenda-orbit"
                                class="wl-trail"
                                d="M200 32 A168 168 0 1 1 199.9 32"
                                stroke="url(#wl-trail)"
                                stroke-width="2"
                                stroke-linecap="round"
                            />

                            <g class="wl-node" fill="#eafff1">
                                <circle class="wl-node-pulse" cx="200" cy="32" r="5" stroke="#eafff1" stroke-width="1.5" />
                                <circle cx="200" cy="32" r="3.5" stroke="none" />
                            </g>
                            <g class="wl-node" fill="#eafff1">
                                <circle class="wl-node-pulse" cx="345.5" cy="284" r="5" stroke="#eafff1" stroke-width="1.5" />
                                <circle cx="345.5" cy="284" r="3.5" stroke="none" />
                            </g>
                            <g class="wl-node" fill="#eafff1">
                                <circle class="wl-node-pulse" cx="54.5" cy="284" r="5" stroke="#eafff1" stroke-width="1.5" />
                                <circle cx="54.5" cy="284" r="3.5" stroke="none" />
                            </g>

                            <g class="wl-plane" transform="translate(200 32)">
                                <g transform="translate(-51 -24.14) scale(0.34)">
                                    <path fill="url(#wl-plane-shade)" opacity=".45" d="M82.8 35 L298.7 129.6 L79 92 Z" />
                                    <path fill="url(#wl-plane-light)" d="M82.8 35 L134.8 11.5 L298.7 129.6 Z" />
                                    <path fill="url(#wl-plane-mid)" d="M76.8 107.1 L291.2 126.7 L74.7 131 Z" />
                                    <path fill="url(#wl-plane-body)" d="M298.8 130.4 L1.9 103.3 L55.9 58.3 Z" />
                                </g>
                            </g>

                            <g class="wl-doc">
                                <path d="M238 144 L256 160 V302 L238 286 Z" fill="url(#wl-paper-side)" />
                                <path d="M140 286 H238 L256 302 H158 Z" fill="url(#wl-paper-base)" />
                                <path d="M140 118 H212 L238 144 V286 H140 Z" fill="url(#wl-paper)" />
                                <path d="M212 118 L238 144 H212 Z" fill="url(#wl-fold)" />
                                <path d="M140 118 H212 L238 144" stroke="#ffffff" stroke-opacity=".35" stroke-width="1.5" />
                                <path
                                    class="wl-doc-line"
                                    d="M158 170 H226"
                                    stroke="#0b3a22"
                                    stroke-opacity=".32"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                />
                                <path
                                    class="wl-doc-line"
                                    d="M158 192 H226"
                                    stroke="#0b3a22"
                                    stroke-opacity=".32"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                />
                                <path
                                    class="wl-doc-line"
                                    d="M158 214 H198"
                                    stroke="#0b3a22"
                                    stroke-opacity=".32"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                />
                                <rect class="wl-doc-stamp" x="158" y="238" width="52" height="24" rx="4" fill="#0b3a22" opacity=".88" />
                                <rect class="wl-doc-stamp" x="166" y="248" width="36" height="4" rx="2" fill="#eafff1" />

                                <g class="wl-seal">
                                    <path
                                        class="wl-seal-layer"
                                        d="M258.00 210.00C261.57 210.00 264.47 218.37 268.72 220.13C272.96 221.89 280.93 218.02 283.46 220.54C285.98 223.07 282.11 231.04 283.87 235.28C285.63 239.53 294.00 242.43 294.00 246.00C294.00 249.57 285.63 252.47 283.87 256.72C282.11 260.96 285.98 268.93 283.46 271.46C280.93 273.98 272.96 270.11 268.72 271.87C264.47 273.63 261.57 282.00 258.00 282.00C254.43 282.00 251.53 273.63 247.28 271.87C243.04 270.11 235.07 273.98 232.54 271.46C230.02 268.93 233.89 260.96 232.13 256.72C230.37 252.47 222.00 249.57 222.00 246.00C222.00 242.43 230.37 239.53 232.13 235.28C233.89 231.04 230.02 223.07 232.54 220.54C235.07 218.02 243.04 221.89 247.28 220.13C251.53 218.37 254.43 210.00 258.00 210.00Z"
                                        fill="#5c3407"
                                        transform="translate(7 9)"
                                    />
                                    <path
                                        class="wl-seal-layer"
                                        d="M258.00 210.00C261.57 210.00 264.47 218.37 268.72 220.13C272.96 221.89 280.93 218.02 283.46 220.54C285.98 223.07 282.11 231.04 283.87 235.28C285.63 239.53 294.00 242.43 294.00 246.00C294.00 249.57 285.63 252.47 283.87 256.72C282.11 260.96 285.98 268.93 283.46 271.46C280.93 273.98 272.96 270.11 268.72 271.87C264.47 273.63 261.57 282.00 258.00 282.00C254.43 282.00 251.53 273.63 247.28 271.87C243.04 270.11 235.07 273.98 232.54 271.46C230.02 268.93 233.89 260.96 232.13 256.72C230.37 252.47 222.00 249.57 222.00 246.00C222.00 242.43 230.37 239.53 232.13 235.28C233.89 231.04 230.02 223.07 232.54 220.54C235.07 218.02 243.04 221.89 247.28 220.13C251.53 218.37 254.43 210.00 258.00 210.00Z"
                                        fill="#96600f"
                                        transform="translate(4 5)"
                                    />
                                    <path
                                        class="wl-seal-layer"
                                        d="M258.00 210.00C261.57 210.00 264.47 218.37 268.72 220.13C272.96 221.89 280.93 218.02 283.46 220.54C285.98 223.07 282.11 231.04 283.87 235.28C285.63 239.53 294.00 242.43 294.00 246.00C294.00 249.57 285.63 252.47 283.87 256.72C282.11 260.96 285.98 268.93 283.46 271.46C280.93 273.98 272.96 270.11 268.72 271.87C264.47 273.63 261.57 282.00 258.00 282.00C254.43 282.00 251.53 273.63 247.28 271.87C243.04 270.11 235.07 273.98 232.54 271.46C230.02 268.93 233.89 260.96 232.13 256.72C230.37 252.47 222.00 249.57 222.00 246.00C222.00 242.43 230.37 239.53 232.13 235.28C233.89 231.04 230.02 223.07 232.54 220.54C235.07 218.02 243.04 221.89 247.28 220.13C251.53 218.37 254.43 210.00 258.00 210.00Z"
                                        fill="url(#wl-seal-face)"
                                    />
                                    <circle cx="258" cy="246" r="21.5" stroke="#5c3407" stroke-opacity=".4" stroke-width="1.5" />
                                    <path
                                        class="wl-check"
                                        d="M245 247 L254 257 L273 233"
                                        stroke="#062b17"
                                        stroke-width="5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </g>
                            </g>
                        </svg>
                    </div>
                </section>
            </main>

            <footer class="text-muted-foreground flex flex-wrap items-center justify-between gap-2 text-xs">
                <span data-load>© {{ year }} e-Surat. Seluruh hak cipta dilindungi.</span>
                <span data-load>Dibuat untuk mempermudah tata kelola persuratan.</span>
            </footer>
        </div>
    </div>
</template>
