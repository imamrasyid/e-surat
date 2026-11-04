<script setup lang="ts">
import { computed, ref } from 'vue';

const mockActions = ['Disposisi', 'Nota Dinas baru', 'Edit surat'];

const phase = ref<'open' | 'form' | 'closed'>('open');
const note = ref('');
const announcement = ref('');

const isClosed = computed(() => phase.value === 'closed');

function startClose() {
    phase.value = 'form';
}

function cancelClose() {
    phase.value = 'open';
    note.value = '';
}

function submitClose() {
    phase.value = 'closed';
    note.value = '';
    announcement.value = 'Status surat sekarang Close.';
}

function resetDemo() {
    phase.value = 'open';
    note.value = '';
    announcement.value = 'Demo dikembalikan ke status Open.';
}
</script>

<template>
    <div class="bg-card dark:bg-input/30 space-y-3 rounded-xl border p-4 text-xs">
        <div class="flex items-center justify-between gap-2">
            <span class="text-muted-foreground text-[10px] font-medium tracking-[0.16em] uppercase">Contoh alur</span>
            <span
                class="rounded-full px-2 py-0.5 text-[11px] font-medium"
                :class="isClosed ? 'bg-success/10 text-success' : 'bg-primary/10 text-primary'"
            >
                {{ isClosed ? 'Close' : 'Open' }}
            </span>
        </div>

        <p v-if="isClosed" class="text-sm font-medium">Nota Dinas penutup terkirim</p>
        <p v-else class="text-muted-foreground text-[11px]">Simulasi lokal di halaman ini — tidak ada permintaan ke server.</p>

        <button
            v-if="phase === 'open'"
            type="button"
            class="focus-visible:ring-ring focus-visible:ring-offset-background bg-primary text-primary-foreground hover:bg-primary/90 inline-flex min-h-8 items-center rounded-md px-2.5 text-[11px] font-medium transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none"
            @click="startClose"
        >
            Tandai sebagai selesai
        </button>

        <form v-else-if="phase === 'form'" class="space-y-2" @submit.prevent="submitClose">
            <label for="about-close-note" class="text-muted-foreground block text-[11px]">Catatan penutup</label>
            <textarea
                id="about-close-note"
                v-model="note"
                rows="2"
                class="border-input bg-background focus-visible:ring-ring w-full resize-none rounded-md border px-2 py-1.5 text-xs focus-visible:ring-2 focus-visible:outline-none"
            ></textarea>
            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="submit"
                    class="focus-visible:ring-ring focus-visible:ring-offset-background bg-primary text-primary-foreground hover:bg-primary/90 inline-flex min-h-8 items-center rounded-md px-2.5 text-[11px] font-medium transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none"
                >
                    Kirim Nota Dinas
                </button>
                <button
                    type="button"
                    class="focus-visible:ring-ring focus-visible:ring-offset-background hover:bg-accent inline-flex min-h-8 items-center rounded-md border px-2.5 text-[11px] font-medium transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none"
                    @click="cancelClose"
                >
                    Batal
                </button>
            </div>
        </form>

        <button
            v-else
            type="button"
            class="focus-visible:ring-ring focus-visible:ring-offset-background hover:bg-accent inline-flex min-h-8 items-center rounded-md border px-2.5 text-[11px] font-medium transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none"
            @click="resetDemo"
        >
            Ulangi demo
        </button>

        <ul class="flex flex-wrap gap-1.5 border-t pt-3" :class="isClosed ? 'opacity-45' : ''" :aria-disabled="isClosed">
            <li v-for="action in mockActions" :key="action" class="bg-muted/40 text-muted-foreground rounded-md border px-2 py-1 text-[11px]">
                {{ action }}
            </li>
        </ul>

        <p class="sr-only" aria-live="polite">{{ announcement }}</p>
    </div>
</template>
