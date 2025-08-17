<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import SmartPagination from '@/components/SmartPagination.vue';
import { HoverCard, HoverCardContent, HoverCardTrigger } from '@/components/ui/hover-card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useToast } from '@/components/ui/toast';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { useFormattedDate } from '@/composables/useFormattedDate';
import AppLayout from '@/layouts/AppLayout.vue';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { toRef, watchDebounced } from '@vueuse/core';
import { BadgeInfo, Filter, Search } from 'lucide-vue-next';
import { ref } from 'vue';

const { toast } = useToast();
const { formatCreate } = useFormattedDate();

defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    memo: any;
}>();

const status = [
    {
        value: 'all',
        name: 'Semua',
    },
    {
        value: 'unread',
        name: 'Belum Dibaca',
    },
    {
        value: 'read',
        name: 'Dibaca',
    },
];

const search = ref<string>('');
const filter = ref<string>();
watchDebounced(
    [toRef(search, 'value'), toRef(filter, 'value')],
    () => {
        router.get(
            route('memo.index'),
            { search: search.value, filter: filter.value },
            {
                preserveState: true,
            },
        );
    },
    {
        debounce: 500,
        maxWait: 1000,
    },
);

function goToDetail(recipient: number, letter_id: string) {
    router.get(
        route('memo.show', { recipient, letter_id }),
        {},
        {
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
                        description: 'Sepertinya terjadi kesalahan, silahkan coba lagi beberapa saat!',
                        variant: 'info',
                    });
                }
            },
            preserveState: true,
        },
    );
}

function goToDetailPatch(memo: number, letter_id: string) {
    router.patch(
        route('memo.update', { memo, letter_id }),
        {},
        {
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
                        description: 'Sepertinya terjadi kesalahan, silahkan coba lagi beberapa saat!',
                        variant: 'info',
                    });
                }
            },
            preserveState: true,
        },
    );
}
</script>

<template>
    <Head title="Surat Masuk | Nota Dinas" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6">
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <Heading class="!mb-0" title="Nota Dinas" description="Daftar nota dinas surat" />
                <div class="flex gap-2">
                    <div class="relative w-full items-center sm:max-w-[250px]">
                        <Input
                            id="search"
                            aria-label="cari"
                            type="text"
                            v-model="search"
                            placeholder="Agenda surat..."
                            class="h-10 pl-8"
                            autocomplete="off"
                        />
                        <span class="absolute inset-y-0 start-0 flex items-center justify-center px-2">
                            <Search class="size-5 text-muted-foreground" />
                        </span>
                    </div>
                    <Select v-model="filter">
                        <SelectTrigger
                            aria-label="filter baca"
                            :class="cn('w-[45px]', 'border-0 focus:ring-0 focus:ring-transparent [&>svg]:hidden')"
                        >
                            <SelectValue>
                                <Filter class="size-5 text-gray-800 dark:text-gray-300" />
                            </SelectValue>
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem v-for="(item, index) in status" :key="index" :value="item.value">{{ item.name }}</SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-md border">
                    <Table>
                        <TableHeader class="bg-muted/30">
                            <TableRow class="hover:bg-transparent">
                                <TableHead class="flex min-w-[175px] items-center gap-1">
                                    Agenda
                                    <TooltipProvider>
                                        <Tooltip>
                                            <TooltipTrigger><BadgeInfo class="size-4" /></TooltipTrigger>
                                            <TooltipContent>
                                                <p>Silahkan hover nomor agenda untuk informasi surat</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                </TableHead>
                                <TableHead class="min-w-[175px]">Dari</TableHead>
                                <TableHead class="min-w-[260px]">Catatan</TableHead>
                                <TableHead class="min-w-[180px]">Followup</TableHead>
                                <TableHead class="w-[100px] text-right"></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="(item, index) in memo.data"
                                :key="index"
                                class="cursor-pointer"
                                @click="item.read_at ? goToDetail(item.id, item.letter.letter_id) : goToDetailPatch(item.id, item.letter.letter_id)"
                                :class="{ 'font-semibold': !item.read_at }"
                            >
                                <TableCell class="align-top">
                                    <HoverCard :open-delay="0">
                                        <HoverCardTrigger as-child>
                                            <button type="button" class="cursor-pointer">
                                                {{ item.letter.agenda }}
                                            </button>
                                        </HoverCardTrigger>
                                        <HoverCardContent class="w-80">
                                            <div class="space-y-1">
                                                <span class="text-xs text-muted-foreground">Asal Surat</span>
                                                <h4 class="text-sm font-semibold">{{ item.letter.sender }}</h4>
                                                <p class="text-sm">{{ item.letter.number }}</p>
                                                <p class="text-sm">{{ item.letter.subject }}</p>
                                            </div>
                                        </HoverCardContent>
                                    </HoverCard>
                                </TableCell>
                                <TableCell class="align-top">
                                    <p v-if="item.is_cc === 1" class="text-xs text-muted-foreground">CC Dari:</p>
                                    {{ item.from_position.name }}
                                </TableCell>
                                <TableCell class="align-top">{{ item.instruction ?? '-' }}</TableCell>
                                <TableCell class="align-top">{{ item.followup ?? '-' }}</TableCell>
                                <TableCell class="text-right align-top">
                                    {{ formatCreate(item.received_at) }}
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="memo.data.length === 0">
                                <TableCell colspan="5" class="text-center text-muted-foreground">Data tidak ditemukan.</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <SmartPagination :links="memo.links" :total="memo.total" :items-per-page="5" class="justify-end" />
            </div>
        </div>
    </AppLayout>
</template>
