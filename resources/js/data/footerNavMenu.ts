import { type NavItem } from '@/types';
import { Folder, HardDrive } from 'lucide-vue-next';

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
        icon: Folder,
        current: '',
    },
];

export default footerNavItems;
