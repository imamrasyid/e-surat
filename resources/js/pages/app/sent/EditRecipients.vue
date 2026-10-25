<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Combobox,
    ComboboxAnchor,
    ComboboxEmpty,
    ComboboxInput,
    ComboboxItem,
    ComboboxList,
    ComboboxTrigger,
    ComboboxViewport,
} from '@/components/ui/combobox';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import AppLayout from '@/layouts/AppLayout.vue';
import { cn } from '@/lib/utils';
import type { BreadcrumbItem, Letter, Position } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { CheckIcon, ChevronDownIcon, ChevronLeft, Loader2, Save } from '@lucide/vue';
import { onMounted, ref, watch } from 'vue';

const props = defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    letter: Letter;
    positions: Position[];
}>();

type RecipientRow = {
    id: number;
    to_position_id: number;
    name: string;
    instruction: string;
};

const existingRecipients: RecipientRow[] =
    props.letter.recipients?.map((r) => ({
        id: r.id,
        to_position_id: r.to_position_id,
        name: r.to_position?.name ?? '',
        instruction: r.instruction ?? '',
    })) ?? [];

const form = useForm({
    recipients: [...existingRecipients] as RecipientRow[],
});

const selectedPenerimas = ref<RecipientRow[]>([...existingRecipients]);
watch(
    selectedPenerimas,
    (items) => {
        form.recipients = items.map((p) => ({
            id: p.id,
            to_position_id: p.id,
            name: p.name,
            instruction: p.instruction,
        }));
    },
    { deep: true },
);

const onSubmit = () => {
    form.put(route('sent.updaterecipients', props.letter.letter_id));
};

const hasHistory = ref(false);
const goBack = () => {
    window.history.back();
};

onMounted(() => {
    hasHistory.value = window.history.length > 1;
});
</script>

<template>
    <Head title="Perbarui Penerima Surat" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6 xl:px-12">
            <div class="flex items-center gap-4">
                <TooltipProvider v-if="hasHistory">
                    <Tooltip>
                        <TooltipTrigger>
                            <Button variant="outline" size="icon" class="cursor-pointer rounded-full" @click="goBack()">
                                <ChevronLeft class="size-5" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>Kembali</p>
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider>
                <Heading class="mb-0!" title="Perbarui Penerima Surat" description="Perbarui data penerima surat yang telah terkirim" />
            </div>

            <dl class="mt-8 space-y-2">
                <div class="flex items-start space-x-4 text-sm lg:space-x-12">
                    <dt class="w-37 shrink-0 font-medium">Nomor Agenda</dt>
                    <dd class="max-w-md wrap-break-word">{{ letter.agenda }}</dd>
                </div>
                <div class="flex items-start space-x-4 text-sm lg:space-x-12">
                    <dt class="w-37 shrink-0 font-medium">Pengirim</dt>
                    <dd class="max-w-md wrap-break-word">{{ letter.sender }}</dd>
                </div>
                <div class="flex items-start space-x-4 text-sm lg:space-x-12">
                    <dt class="w-37 shrink-0 font-medium">Nomor Surat</dt>
                    <dd class="max-w-md wrap-break-word">{{ letter.number ?? '-' }}</dd>
                </div>
                <div class="flex items-start space-x-4 text-sm lg:space-x-12">
                    <dt class="w-37 shrink-0 font-medium">Perihal</dt>
                    <dd class="max-w-md wrap-break-word">{{ letter.subject }}</dd>
                </div>
            </dl>

            <Separator class="my-6" />

            <!-- Form Penerima -->
            <form @submit.prevent="onSubmit">
                <div class="space-y-8">
                    <div class="flex flex-col lg:flex-row lg:space-x-12">
                        <aside class="w-full max-w-xl lg:w-62.5 xl:w-87.5 2xl:w-md">
                            <HeadingSmall title="Penerima Surat" description="Pilih penerima dan isi catatan surat" />
                        </aside>

                        <Separator class="my-6 lg:hidden" />

                        <div class="flex-1 md:max-w-2xl">
                            <section class="max-w-2xl space-y-6">
                                <div class="grid gap-2">
                                    <Label for="penerima">Penerima</Label>
                                    <Combobox v-model="selectedPenerimas" multiple by="id">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <Button
                                                    id="penerima"
                                                    type="button"
                                                    variant="outline"
                                                    :tabindex="9"
                                                    :class="
                                                        cn(
                                                            'w-full justify-between font-normal whitespace-normal',
                                                            selectedPenerimas.length === 0 && 'text-muted-foreground',
                                                        )
                                                    "
                                                >
                                                    <span class="line-clamp-1 text-left">
                                                        {{
                                                            selectedPenerimas.length > 0
                                                                ? selectedPenerimas.map((p) => p.name).join(', ')
                                                                : 'Pilih penerima'
                                                        }}
                                                    </span>
                                                    <ChevronDownIcon class="text-muted-foreground" />
                                                </Button>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList align="start" class="w-(--reka-combobox-trigger-width) min-w-(--reka-combobox-trigger-width)">
                                            <ComboboxInput id="search-position" placeholder="Cari..." />
                                            <ComboboxViewport class="max-h-65 w-full min-w-(--reka-combobox-trigger-width) p-1">
                                                <ComboboxEmpty>Tidak ada data.</ComboboxEmpty>
                                                <ComboboxItem v-for="item in positions" :key="item.id" :value="item">
                                                    <div
                                                        class="border-input data-[selected=true]:border-primary data-[selected=true]:bg-primary data-[selected=true]:text-primary-foreground pointer-events-none size-4 shrink-0 rounded-lg border transition-all select-none *:[svg]:opacity-0 data-[selected=true]:*:[svg]:opacity-100"
                                                        :data-selected="selectedPenerimas.some((p) => p.id === item.id)"
                                                    >
                                                        <CheckIcon class="size-3.5 text-current" />
                                                    </div>
                                                    {{ item.name }}
                                                </ComboboxItem>
                                            </ComboboxViewport>
                                        </ComboboxList>
                                    </Combobox>
                                    <InputError :message="form.errors.recipients" />
                                </div>

                                <div v-for="(item, index) in form.recipients" :key="item.to_position_id" class="grid gap-2">
                                    <Label :for="`catatan-${index}`">Catatan untuk {{ item.name }}</Label>
                                    <Textarea
                                        :id="`catatan-${index}`"
                                        v-model="item.instruction"
                                        placeholder="Masukkan catatan surat (Opsional)"
                                        class="dark:bg-input/30"
                                    />
                                    <InputError :message="(form.errors as any)[`recipients.${index}.instruction`]" />
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

                <Separator class="my-6" />

                <div class="flex justify-end">
                    <Button type="submit" :disabled="form.processing || !form.isDirty" class="cursor-pointer">
                        <Loader2 v-if="form.processing" class="animate-spin" />
                        <Save v-else />
                        Simpan Perubahan
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
