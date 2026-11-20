import { type NavItem } from '@/types';
import { Archive, HardDrive } from '@lucide/vue';

const footerNavItems: NavItem[] = [
    {
        title: 'Master Data',
        href: 'ref.master-data',
        icon: HardDrive,
        current: 'ref.*',
        permission: 'master.*',
    },
    {
        title: 'Arsip Surat',
        href: 'arsip.index',
        icon: Archive,
        current: 'arsip.*',
        permission: 'arsip.index',
    },
];

export default footerNavItems;
