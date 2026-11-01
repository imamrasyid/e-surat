<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { useAppearance } from '@/composables/useAppearance';
import { router, useHttp } from '@inertiajs/vue3';
import { CheckCircle2, ExternalLink, LoaderCircle, Send } from '@lucide/vue';
import { computed, onUnmounted, ref, watch } from 'vue';

type PairingPayload = {
    url: string;
    svg: string;
};

type StatusPayload = {
    connected: boolean;
};

const isOpen = defineModel<boolean>('isOpen', { default: false });

const { resolvedAppearance } = useAppearance();

const http = useHttp<{ telegram?: string }, PairingPayload>({});

const pairing = ref<PairingPayload | null>(null);
const error = ref<string | null>(null);
const connected = ref(false);
const loading = ref(false);

let pollTimer: ReturnType<typeof setInterval> | null = null;

const qrFilter = computed(() => (resolvedAppearance.value === 'dark' ? 'invert(1) brightness(1.5)' : undefined));

const stopPolling = (): void => {
    if (pollTimer !== null) {
        clearInterval(pollTimer);
        pollTimer = null;
    }
};

const pollStatus = async (): Promise<void> => {
    try {
        const response = await fetch(route('telegram.status'), {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            return;
        }

        const status = (await response.json()) as StatusPayload;

        if (status.connected) {
            connected.value = true;
            stopPolling();
        }
    } catch {
        // Gangguan jaringan sesaat: biarkan percobaan berikutnya menyusul.
    }
};

const loadPairing = async (): Promise<void> => {
    pairing.value = null;
    error.value = null;
    connected.value = false;
    loading.value = true;

    try {
        pairing.value = await http.post(route('telegram.link'));
        pollTimer = setInterval(() => void pollStatus(), 3000);
    } catch {
        error.value = http.errors.telegram ?? 'Gagal membuat tautan Telegram. Coba lagi.';
    } finally {
        loading.value = false;
    }
};

const close = (): void => {
    isOpen.value = false;
};

watch(isOpen, (open) => {
    stopPolling();

    if (open) {
        void loadPairing();

        return;
    }

    pairing.value = null;
    error.value = null;
    connected.value = false;
});

watch(connected, (isConnected) => {
    if (!isConnected) {
        return;
    }

    router.reload({ only: ['telegramConnected'] });
    setTimeout(close, 1500);
});

onUnmounted(stopPolling);
</script>

<template>
    <Dialog :open="isOpen" @update:open="isOpen = $event">
        <DialogContent class="sm:max-w-md" data-testid="telegram-modal">
            <DialogHeader class="flex flex-col items-center justify-center text-center">
                <div class="border-border bg-card mb-3 w-auto rounded-full border p-0.5 shadow-sm">
                    <div class="border-border bg-muted relative overflow-hidden rounded-full border p-2.5">
                        <Send class="text-foreground relative z-20 size-6" />
                    </div>
                </div>
                <DialogTitle>Hubungkan Telegram</DialogTitle>
                <DialogDescription class="text-center">
                    Pindai QR di bawah dengan kamera untuk menautkan akun, atau buka tautannya langsung di tab baru.
                </DialogDescription>
            </DialogHeader>

            <div class="flex w-full flex-col items-center justify-center space-y-5">
                <div v-if="error" class="bg-destructive/10 text-destructive w-full rounded-md p-3 text-sm" data-testid="telegram-error">
                    {{ error }}
                </div>

                <div v-else-if="connected" class="flex flex-col items-center space-y-2 py-6 text-center" data-testid="telegram-connected">
                    <CheckCircle2 class="size-10 text-emerald-500" />
                    <p class="text-sm font-medium">Akun Telegram tersambung.</p>
                    <p class="text-muted-foreground text-xs">Dialog akan tertutup otomatis.</p>
                </div>

                <template v-else>
                    <div class="border-border relative mx-auto aspect-square w-64 overflow-hidden rounded-lg border">
                        <div
                            v-if="loading || !pairing"
                            class="bg-background absolute inset-0 z-10 flex items-center justify-center"
                            data-testid="telegram-qr-loading"
                        >
                            <LoaderCircle class="size-6 animate-spin" />
                        </div>
                        <div v-else class="relative z-10 overflow-hidden p-3">
                            <div
                                v-html="pairing.svg"
                                class="flex aspect-square size-full items-center justify-center"
                                :style="{ filter: qrFilter }"
                                data-testid="telegram-qr"
                            />
                        </div>
                    </div>

                    <p class="text-muted-foreground flex items-center gap-2 text-xs">
                        <LoaderCircle class="size-3 animate-spin" />
                        Menunggu konfirmasi tombol Start di Telegram…
                    </p>

                    <div class="flex w-full items-center gap-3">
                        <Button v-if="pairing" as-child class="flex-1">
                            <a :href="pairing.url" target="_blank" rel="noopener noreferrer" data-testid="telegram-open-link">
                                <ExternalLink class="size-4" />
                                Buka di tab baru
                            </a>
                        </Button>
                        <Button v-else type="button" class="flex-1" disabled>
                            <ExternalLink class="size-4" />
                            Buka di tab baru
                        </Button>
                        <Button type="button" variant="outline" class="flex-1" @click="close">Tutup</Button>
                    </div>

                    <p class="text-muted-foreground text-center text-xs">QR dan tautan berlaku 10 menit serta hanya bisa dipakai sekali.</p>
                </template>
            </div>
        </DialogContent>
    </Dialog>
</template>
