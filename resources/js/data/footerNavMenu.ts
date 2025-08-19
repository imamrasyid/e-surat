import { type NavItem } from '@/types';
import { Archive, HardDrive } from 'lucide-vue-next';

const footerNavItems: NavItem[] = [
    {
        title: 'Master Data',
        href: 'ref.master-data',
        icon: HardDrive,
        current: 'ref.*',
    },
    {
        title: 'Arsip Surat',
        href: 'dashboard',
        icon: Archive,
        current: '',
    },
];

export default footerNavItems;
