import { type NavItem } from '@/types';

const masterNavItems: NavItem[] = [
    {
        title: 'Kode Klasifikasi',
        href: 'ref.klasifikasi.index',
        current: 'ref.klasifikasi.*',
    },
    {
        title: 'Jabatan',
        href: 'ref.position.index',
        current: 'ref.position.*',
    },
    {
        title: 'Data Pengguna',
        href: 'ref.user.index',
        current: 'ref.user.*',
    },
];

export default masterNavItems;
