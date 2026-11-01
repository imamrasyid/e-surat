import type { UserChartTotals } from '@/types/dashboard';

export interface UserChartSeriesConfig {
    key: keyof UserChartTotals;
    label: string;
    color: string;
}

/**
 * Shared colour and label mapping so the queue line chart and the distribution
 * donut always agree on what each category looks like.
 */
export const userChartSeries: UserChartSeriesConfig[] = [
    { key: 'primary', label: 'Utama', color: 'var(--chart-1)' },
    { key: 'undangan', label: 'Undangan', color: 'var(--chart-2)' },
    { key: 'disposisi', label: 'Disposisi', color: 'var(--chart-3)' },
    { key: 'memo', label: 'Nota Dinas', color: 'var(--chart-4)' },
];
