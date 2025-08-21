import { type NavItem } from '@/types';

const masterNavItems: NavItem[] = [
    {
        title: 'Data Followup',
        href: 'ref.followup.index',
        current: 'ref.followup.*',
    },
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
    {
        title: 'Roles',
        href: 'ref.role.index',
        current: 'ref.role.*',
    },
];

export default masterNavItems;
