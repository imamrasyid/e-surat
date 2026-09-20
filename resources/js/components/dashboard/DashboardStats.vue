<script setup lang="ts">
import { CheckCircle2, Clock, Inbox } from '@lucide/vue';
import DashboardStatCard from './DashboardStatCard.vue';

interface Props {
    totalLetters: number;
    inProcessLetters: number;
    completedLetters: number;
}

const props = defineProps<Props>();
</script>

<template>
    <div class="grid auto-rows-min gap-4 md:grid-cols-3 lg:gap-8">
        <DashboardStatCard
            title="Surat Masuk"
            description="Jumlah seluruh surat masuk"
            :value="props.totalLetters"
            variant="primary"
            :icon="Inbox"
            icon-animation="pulse"
        />

        <DashboardStatCard
            title="Surat Dalam Proses"
            description="Belum ada tindak lanjut surat"
            :value="props.inProcessLetters"
            variant="warning"
            :icon="Clock"
            icon-animation="spin"
            animation-duration="3s"
        />

        <DashboardStatCard
            title="Surat Selesai"
            description="Surat yang telah selesai diproses"
            :value="props.completedLetters"
            variant="success"
            :icon="CheckCircle2"
            icon-animation="pulse"
        />
    </div>
</template>
