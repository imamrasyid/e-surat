<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import LetterMeta from '@/components/letter/LetterMeta.vue';
import LetterTimelineTable from '@/components/letter/LetterTimelineTable.vue';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { Button } from '@/components/ui/button';
import Label from '@/components/ui/label/Label.vue';
import Separator from '@/components/ui/separator/Separator.vue';
import { Textarea } from '@/components/ui/textarea';
import { useClearErrorsOnChange } from '@/composables/useClearErrorsOnChange';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Letter, Memo, TimelineRecipient } from '@/types';
import { Head, InertiaForm, useForm } from '@inertiajs/vue3';
import { Loader2, Send, Undo2 } from '@lucide/vue';
import { onMounted, ref } from 'vue';

const props = defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    letter: Letter;
    recipients: TimelineRecipient[];
    memo: Memo;
}>();

type FormInput = {
    letter_id_primary: number;
    letter_recipient_id: number;
    to_position_id: number;
    catatan: string;
};

const form: InertiaForm<FormInput> = useForm<FormInput>({
    letter_id_primary: Number(props.letter.id),
    letter_recipient_id: Number(props.memo.letter_recipient_id),
    to_position_id: Number(props.memo.from_position_id),
    catatan: '',
});
useClearErrorsOnChange(form, { debounceMs: 120 });

const onSubmit = () => {
    form.post(route('memo.store.close'), {
        onSuccess: () => {
            form.reset();
        },
    });
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
    <Head title="Buat Penutupan Surat Masuk" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="w-full max-w-7xl px-4 py-6 xl:px-12">
            <form class="mb-12" @submit.prevent="onSubmit">
                <Heading title="Penutupan" description="Buat penutupan surat masuk" />

                <Accordion type="single" collapsible class="mb-8">
                    <AccordionItem value="letter">
                        <AccordionTrigger class="cursor-pointer text-sm">Detail Surat:</AccordionTrigger>
                        <AccordionContent>
                            <LetterMeta :letter="letter" show-category />
                            <div class="mt-4">
                                <LetterTimelineTable :items="recipients" />
                            </div>
                        </AccordionContent>
                    </AccordionItem>
                </Accordion>

                <div class="mb-12 flex flex-col lg:flex-row lg:space-x-12">
                    <aside class="w-full max-w-xl lg:w-62.5 xl:w-87.5 2xl:w-md">
                        <HeadingSmall title="Ditujukan" description="Isi catatan penutupan surat masuk" />
                    </aside>

                    <Separator class="my-6 lg:hidden" />

                    <section class="flex-1 items-start space-y-6">
                        <div class="grid gap-2">
                            <Label for="catatan">Catatan</Label>
                            <Textarea
                                id="catatan"
                                :tabindex="1"
                                v-model="form.catatan"
                                placeholder="Masukkan catatan (Opsional)"
                                class="dark:bg-input/30"
                            />
                            <InputError :message="form.errors.catatan" />
                        </div>
                    </section>
                </div>

                <div class="flex flex-col gap-4 lg:flex-row lg:justify-end">
                    <Button v-if="hasHistory" type="button" variant="outline" class="w-full cursor-pointer lg:w-40" @click="goBack">
                        <Undo2 class="size-4" />
                        Batal
                    </Button>
                    <Button type="submit" :disabled="!form.isDirty || form.processing" class="w-full cursor-pointer lg:w-50">
                        <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                        <Send v-else class="size-4" />
                        Simpan dan Tutup
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
