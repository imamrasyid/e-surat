import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';
import type { User } from './models/user';

export * from './models';

export interface Auth {
    [x: string]: any;
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
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
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export type BreadcrumbItemType = BreadcrumbItem;
