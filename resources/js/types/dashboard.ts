import type { Component } from 'vue';

export interface ChartDataItem {
    date: string;
    surat_masuk: number;
}

export type DashboardRole = 'admin' | 'manager' | 'user';

export interface AdminDashboardStats {
    activeUsers: number;
    activePositions: number;
    totalLetters: number;
    openLetters: number;
}

export interface ManagerDashboardStats {
    totalIncomingLetters: number;
    openLetters: number;
    inProcessLetters: number;
    completedIncomingLetters: number;
}

export type DashboardPayload =
    | { role: 'admin'; stats: AdminDashboardStats }
    | { role: 'manager'; stats: ManagerDashboardStats }
    | { role: 'user'; stats: null };

export interface DashboardInboxCounts {
    primary: number;
    undangan: number;
    disposisi: number;
    memo: number;
}

export interface DashboardMetric {
    key: string;
    title: string;
    description: string;
    value: number;
    variant: 'primary' | 'warning' | 'success' | 'info';
    icon: Component;
    unit?: string;
}

export interface DashboardQuickAction {
    key: string;
    title: string;
    description: string;
    href: string;
    icon: Component;
}

export interface UserChartSeriesItem {
    date: string;
    primary: number;
    undangan: number;
    disposisi: number;
    memo: number;
}

export interface UserChartTotals {
    primary: number;
    undangan: number;
    disposisi: number;
    memo: number;
}

export interface UserChartPayload {
    series: UserChartSeriesItem[];
    totals: UserChartTotals;
    totalReceived: number;
}
