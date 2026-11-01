<script setup lang="ts">
import { usePermissions } from '@/composables/usePermissions';
import type {
    ChartDataItem,
    DashboardInboxCounts,
    DashboardMetric,
    DashboardPayload,
    DashboardQuickAction,
    DashboardRole,
    UserChartPayload,
} from '@/types/dashboard';
import {
    Building2,
    CheckCircle2,
    Clock,
    Database,
    FolderOpen,
    Inbox,
    Mails,
    Radio,
    Send,
    ShieldCheck,
    SquarePen,
    StickyNote,
    Users,
} from '@lucide/vue';
import { computed, type Component } from 'vue';
import DashboardQuickActions from './DashboardQuickActions.vue';
import DashboardStats from './DashboardStats.vue';
import DashboardTrendChart from './DashboardTrendChart.vue';
import DashboardUserDistributionChart from './DashboardUserDistributionChart.vue';
import DashboardUserTrendChart from './DashboardUserTrendChart.vue';

const props = defineProps<{
    dashboard: DashboardPayload;
    chartData: ChartDataItem[];
    userChartData: UserChartPayload | null;
    inbox: DashboardInboxCounts;
}>();

const { can } = usePermissions();

const metrics = computed<DashboardMetric[]>(() => {
    const dashboard = props.dashboard;

    if (dashboard.role === 'admin') {
        const stats = dashboard.stats;

        return [
            {
                key: 'active-users',
                title: 'Pengguna Aktif',
                description: 'Jumlah akun aktif terdaftar',
                value: stats.activeUsers,
                variant: 'primary',
                icon: Users,
                unit: 'Pengguna',
            },
            {
                key: 'active-positions',
                title: 'Jabatan Aktif',
                description: 'Jabatan yang sedang berlaku',
                value: stats.activePositions,
                variant: 'info',
                icon: Building2,
                unit: 'Jabatan',
            },
            {
                key: 'total-letters',
                title: 'Total Surat',
                description: 'Seluruh surat yang tersimpan',
                value: stats.totalLetters,
                variant: 'success',
                icon: Inbox,
                unit: 'Surat',
            },
            {
                key: 'open-letters',
                title: 'Surat Terbuka',
                description: 'Surat yang belum selesai',
                value: stats.openLetters,
                variant: 'warning',
                icon: FolderOpen,
                unit: 'Surat',
            },
        ];
    }

    if (dashboard.role === 'manager') {
        const stats = dashboard.stats;

        return [
            {
                key: 'open-letters',
                title: 'Surat Terbuka',
                description: 'Surat yang masih berstatus terbuka',
                value: stats.openLetters,
                variant: 'info',
                icon: FolderOpen,
                unit: 'Surat',
            },
            {
                key: 'in-process-letters',
                title: 'Dalam Proses',
                description: 'Belum ada tindak lanjut surat',
                value: stats.inProcessLetters,
                variant: 'warning',
                icon: Clock,
                unit: 'Surat',
            },
            {
                key: 'completed-letters',
                title: 'Surat Selesai',
                description: 'Surat masuk yang selesai diproses',
                value: stats.completedIncomingLetters,
                variant: 'success',
                icon: CheckCircle2,
                unit: 'Surat',
            },
        ];
    }

    return [
        {
            key: 'inbox-primary',
            title: 'Surat Utama',
            description: 'Surat belum dibaca',
            value: props.inbox.primary,
            variant: 'primary',
            icon: Inbox,
            unit: 'Surat',
        },
        {
            key: 'inbox-undangan',
            title: 'Surat Undangan',
            description: 'Surat belum dibaca',
            value: props.inbox.undangan,
            variant: 'info',
            icon: Radio,
            unit: 'Surat',
        },
        {
            key: 'inbox-disposisi',
            title: 'Disposisi',
            description: 'Disposisi belum dibaca',
            value: props.inbox.disposisi,
            variant: 'warning',
            icon: Mails,
            unit: 'Disposisi',
        },
        {
            key: 'inbox-memo',
            title: 'Nota Dinas',
            description: 'Memo belum dibaca',
            value: props.inbox.memo,
            variant: 'success',
            icon: StickyNote,
            unit: 'Nota Dinas',
        },
    ];
});

interface QuickActionCandidate {
    key: string;
    title: string;
    description: string;
    routeName: string;
    permission: string;
    icon: Component;
}

const candidates: Record<DashboardRole, QuickActionCandidate[]> = {
    admin: [
        {
            key: 'manage-users',
            title: 'Kelola Pengguna',
            description: 'Tambah dan perbarui user',
            routeName: 'ref.user.index',
            permission: 'master.pengguna',
            icon: Users,
        },
        {
            key: 'manage-positions',
            title: 'Kelola Jabatan',
            description: 'Referensi struktur jabatan',
            routeName: 'ref.position.index',
            permission: 'master.jabatan',
            icon: Building2,
        },
        {
            key: 'manage-roles',
            title: 'Peran & Hak Akses',
            description: 'Tinjau peran dan izin user',
            routeName: 'ref.role.index',
            permission: 'master.pengguna',
            icon: ShieldCheck,
        },
        {
            key: 'master-data',
            title: 'Master Data',
            description: 'Kelola data referensi sistem',
            routeName: 'ref.master-data',
            permission: 'master.*',
            icon: Database,
        },
    ],
    manager: [
        {
            key: 'compose-letter',
            title: 'Buat Surat',
            description: 'Susun surat masuk baru',
            routeName: 'app.compose.create',
            permission: 'compose.create',
            icon: SquarePen,
        },
        {
            key: 'sent-letters',
            title: 'Surat Terkirim',
            description: 'Pantau surat masuk terkirim',
            routeName: 'sent.index',
            permission: 'sent.index',
            icon: Send,
        },
        {
            key: 'dispositions',
            title: 'Disposisi',
            description: 'Tindak lanjuti disposisi aktif',
            routeName: 'dispo.index',
            permission: 'disposition.index',
            icon: Mails,
        },
        {
            key: 'memos',
            title: 'Nota Dinas',
            description: 'Kelola nota dinas internal',
            routeName: 'memo.index',
            permission: 'memo.index',
            icon: StickyNote,
        },
        {
            key: 'archive',
            title: 'Arsip Surat',
            description: 'Telusuri arsip surat organisasi',
            routeName: 'arsip.index',
            permission: 'arsip.index',
            icon: FolderOpen,
        },
    ],
    user: [
        {
            key: 'inbox-primary',
            title: 'Surat Masuk',
            description: 'Buka kotak surat utama',
            routeName: 'inbox.primary.index',
            permission: 'primary.index',
            icon: Inbox,
        },
        {
            key: 'inbox-undangan',
            title: 'Undangan',
            description: 'Buka kotak surat undangan',
            routeName: 'inbox.undangan.index',
            permission: 'undangan.index',
            icon: Radio,
        },
        {
            key: 'dispositions',
            title: 'Disposisi',
            description: 'Tindak lanjuti disposisi aktif',
            routeName: 'dispo.index',
            permission: 'disposition.index',
            icon: Mails,
        },
        {
            key: 'memos',
            title: 'Nota Dinas',
            description: 'Buka nota dinas internal',
            routeName: 'memo.index',
            permission: 'memo.index',
            icon: StickyNote,
        },
    ],
};

const incomingTotal = computed<number | null>(() => (props.dashboard.role === 'manager' ? props.dashboard.stats.totalIncomingLetters : null));

const quickActions = computed<DashboardQuickAction[]>(() =>
    candidates[props.dashboard.role]
        .filter((item) => can(item.permission))
        .map((item) => ({
            key: item.key,
            title: item.title,
            description: item.description,
            href: route(item.routeName),
            icon: item.icon,
        })),
);
</script>

<template>
    <section class="mt-8 space-y-6" aria-labelledby="dashboard-role-heading">
        <DashboardStats :metrics="metrics" />

        <div v-if="props.userChartData" class="grid gap-6 sm:grid-cols-4">
            <DashboardUserTrendChart class="sm:col-span-3" :initial-data="props.userChartData.series" :total="props.userChartData.totalReceived" />
            <DashboardUserDistributionChart :totals="props.userChartData.totals" />
        </div>

        <DashboardTrendChart v-if="incomingTotal !== null" :initial-data="props.chartData" :total="incomingTotal" />
    </section>

    <section v-if="quickActions.length > 0" class="mt-8 space-y-4" aria-labelledby="dashboard-actions-heading">
        <h2 id="dashboard-actions-heading" class="text-lg font-semibold tracking-tight">Aksi Cepat</h2>

        <DashboardQuickActions :items="quickActions" />
    </section>
</template>
