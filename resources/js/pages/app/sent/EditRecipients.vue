<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Combobox, ComboboxAnchor, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxList } from '@/components/ui/combobox';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { TagsInput, TagsInputInput, TagsInputItem, TagsInputItemDelete, TagsInputItemText } from '@/components/ui/tags-input';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from '@/components/ui/toast/use-toast';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Letter, Position } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ChevronLeft, Loader2, Save } from '@lucide/vue';
import { useDateFormat } from '@vueuse/core';
import { computed, ref } from 'vue';

const { toast } = useToast();

const props = defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    letter: Letter;
    positions: Position[];
}>();

type RecipientRow = {
    id: number | null;
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

const openPenerima = ref(false);
const searchPenerima = ref('');

const penerimaSelect = (item: any) => {
    form.recipients.push({
        id: null,
        to_position_id: item.value.id,
        name: item.value.name,
        instruction: '',
    });
    searchPenerima.value = '';
    if (filteredPenerima.value.length === 0) {
        openPenerima.value = false;
    }
};

const filteredPenerima = computed(() => {
    const selectedIds = form.recipients.map((r) => r.to_position_id);
    const options = props.positions.filter((p) => !selectedIds.includes(p.id));
    if (searchPenerima.value) {
        const keyword = searchPenerima.value.toLowerCase();
        return options.filter((p) => p.name.toLowerCase().includes(keyword));
    }
    return options;
});

const onSubmit = () => {
    form.put(route('sent.updaterecipients', props.letter.letter_id), {
        onSuccess: () => {
            toast({
                title: 'Berhasil!',
                description: 'Data penerima surat berhasil diperbarui.',
                variant: 'success',
            });
        },
        onError: (errors: any) => {
            if (errors.confirm) {
                toast({
                    title: 'Uh oh! Something went wrong.',
                    description: errors.confirm,
                    variant: 'destructive',
                });
            } else {
                toast({
                    title: 'Uh oh! Something went wrong.',
                    description: 'Sepertinya data belum lengkap.',
                    variant: 'warning',
                });
            }
        },
    });
};
</script>

<template>
    <Head title="Perbarui Penerima Surat" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6 xl:px-12">
            <div class="flex flex-col gap-4 sm:flex-row sm:justify-between">
                <HeadingSmall title="Perbarui Penerima Surat" description="Perbarui data penerima surat yang telah terkirim" />
                <div class="sm:text-end">
                    <p class="text-xs text-gray-500">
                        {{ useDateFormat(letter.created_at, 'dddd, DD MMM YYYY, HH.mm', { locales: 'id-ID' }) }}
                    </p>
                </div>
            </div>

            <Separator class="my-6" />

            <div class="flex items-center justify-between">
                <TooltipProvider>
                    <Tooltip>
                        <TooltipTrigger>
                            <Button
                                variant="outline"
                                size="icon"
                                class="cursor-pointer rounded-full"
                                @click="$inertia.get(route('sent.show', letter.letter_id))"
                            >
                                <ChevronLeft class="size-5" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>Kembali</p>
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider>
            </div>

            <!-- Info Surat (Read-Only) -->
            <div class="mt-6 space-y-2">
                <div class="flex space-x-4 lg:space-x-12">
                    <aside class="w-full max-w-[150px] sm:max-w-[150px] lg:w-[150px]">
                        <h6 class="text-sm font-medium">Nomor Agenda</h6>
                    </aside>
                    <div class="flex-1">
                        <h6 class="text-sm dark:text-gray-300">{{ letter.agenda }}</h6>
                    </div>
                </div>

                <div class="flex space-x-4 lg:space-x-12">
                    <aside class="w-full max-w-[150px] sm:max-w-[150px] lg:w-[150px]">
                        <h6 class="text-sm font-medium">Pengirim</h6>
                    </aside>
                    <div class="flex-1">
                        <h6 class="text-sm dark:text-gray-300">{{ letter.sender }}</h6>
                    </div>
                </div>

                <div class="flex space-x-4 lg:space-x-12">
                    <aside class="w-full max-w-[150px] sm:max-w-[150px] lg:w-[150px]">
                        <h6 class="text-sm font-medium">Perihal</h6>
                    </aside>
                    <div class="flex-1 sm:max-w-md">
                        <h6 class="text-sm dark:text-gray-300">{{ letter.subject }}</h6>
                    </div>
                </div>
            </div>

            <Separator class="my-6" />

            <!-- Form Penerima -->
            <form @submit.prevent="onSubmit">
                <div class="space-y-8">
                    <div class="flex flex-col lg:flex-row lg:space-x-12">
                        <aside class="w-full max-w-xl lg:w-[250px] xl:w-[350px] 2xl:w-md">
                            <HeadingSmall title="Penerima Surat" description="Pilih penerima dan isi catatan surat" />
                        </aside>

                        <Separator class="my-6 lg:hidden" />

                        <div class="flex-1 md:max-w-2xl">
                            <section class="max-w-2xl space-y-6">
                                <div class="grid gap-2">
                                    <Label for="penerima">Penerima</Label>
                                    <Combobox v-model="form.recipients" v-model:open="openPenerima" :ignore-filter="true">
                                        <ComboboxAnchor as-child>
                                            <TagsInput id="penerima" v-model="form.recipients" class="w-full gap-2 px-2 dark:bg-input/30">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <TagsInputItem
                                                        v-for="(item, index) in form.recipients"
                                                        :key="index"
                                                        :value="item.name ? item.name : ''"
                                                    >
                                                        <TagsInputItemText />
                                                        <TagsInputItemDelete />
                                                    </TagsInputItem>
                                                </div>

                                                <ComboboxInput v-model="searchPenerima" as-child>
                                                    <TagsInputInput
                                                        placeholder="Cari penerima..."
                                                        class="h-auto w-full min-w-[200px] border-none p-0 shadow-none focus-visible:ring-0"
                                                        @keydown.enter.prevent
                                                    />
                                                </ComboboxInput>
                                            </TagsInput>

                                            <ComboboxList class="w-[--reka-popper-anchor-width]">
                                                <ComboboxEmpty />
                                                <ComboboxGroup>
                                                    <ComboboxItem
                                                        v-for="item in filteredPenerima"
                                                        :key="item.id"
                                                        :value="item"
                                                        @select.prevent="
                                                            (ev) => {
                                                                penerimaSelect(ev.detail);
                                                            }
                                                        "
                                                    >
                                                        {{ item.name }}
                                                    </ComboboxItem>
                                                </ComboboxGroup>
                                            </ComboboxList>
                                        </ComboboxAnchor>
                                    </Combobox>
                                    <InputError :message="form.errors.recipients" />
                                </div>

                                <div v-for="(item, index) in form.recipients" :key="index" class="grid gap-2">
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
                        <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                        <Save v-else class="h-4 w-4" />
                        Simpan Perubahan
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
