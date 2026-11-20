<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormattedDate } from '@/composables/useFormattedDate';
import type { LetterActivity } from '@/types';

defineProps<{
    activities: LetterActivity[];
}>();

const { formatDateTime } = useFormattedDate();
</script>

<template>
    <div class="mt-6">
        <h2 class="text-sm font-medium">Riwayat Surat</h2>
        <div class="mt-2 rounded-lg border">
            <Table>
                <TableHeader class="bg-muted/30">
                    <TableRow class="hover:bg-transparent">
                        <TableHead class="min-w-50">Aktivitas</TableHead>
                        <TableHead class="min-w-38">Oleh</TableHead>
                        <TableHead class="min-w-38">Waktu</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="activity in activities" :key="activity.id">
                        <TableCell class="align-top">{{ activity.description }}</TableCell>
                        <TableCell class="align-top">{{ activity.user?.name ?? '-' }}</TableCell>
                        <TableCell class="align-top">{{ formatDateTime(activity.created_at) }}</TableCell>
                    </TableRow>
                    <TableRow v-if="activities.length === 0">
                        <TableCell colspan="3" class="text-muted-foreground text-center">Belum ada riwayat surat.</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
