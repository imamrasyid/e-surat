import type { LucideIcon } from '@lucide/vue';
import type { Config } from 'ziggy-js';
import type { NotificationCenterProps } from './models/notification';
import type { User } from './models/user';

export type Appearance = 'light' | 'dark' | 'system';
export type ResolvedAppearance = 'light' | 'dark';

export type FlashToast = {
    type: 'success' | 'info' | 'warning' | 'error';
    message: string;
};

export * from './models';

export interface Auth {
    [x: string]: any;
    user: User;
}

export type Passkey = {
    id: number;
    name: string;
    authenticator: string | null;
    created_at_diff: string;
    last_used_at_diff: string | null;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};

export interface BreadcrumbItem {
    title: string;
    route?: string;
    href?: string;
}

export interface NavItem {
    title: string;
    href: string;
    current?: string;
    icon?: LucideIcon;
    isActive?: boolean;
    notif?: number;
    permission?: string;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    inbox: { primary: number; undangan: number; disposisi: number; memo: number };
    features: Record<string, boolean>;
    notificationCenter: NotificationCenterProps;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export type BreadcrumbItemType = BreadcrumbItem;
