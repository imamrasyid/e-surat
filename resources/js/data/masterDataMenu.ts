import { type NavItem } from '@/types';

const masterNavItems: NavItem[] = [
    {
        title: 'Data Followup',
        href: 'ref.followup.index',
        current: 'ref.followup.*',
        permission: 'master.followup',
    },
    {
        title: 'Kode Klasifikasi',
        href: 'ref.klasifikasi.index',
        current: 'ref.klasifikasi.*',
        permission: 'master.klasifikasi',
    },
    {
        title: 'Jabatan',
        href: 'ref.position.index',
        current: 'ref.position.*',
        permission: 'master.jabatan',
    },
    {
        title: 'Data Pengguna',
        href: 'ref.user.index',
        current: 'ref.user.*',
        permission: 'master.pengguna',
    },
    {
        title: 'Roles',
        href: 'ref.role.index',
        current: 'ref.role.*',
        permission: 'master.pengguna',
    },
];

export default masterNavItems;
