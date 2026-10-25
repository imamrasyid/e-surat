import { type NavItem } from '@/types';
import { BriefcaseBusiness, ClipboardList, FolderOpen, ShieldLock, UserGroup } from '@lucide/vue';

const masterNavItems: NavItem[] = [
    {
        title: 'Tindak Lanjut',
        href: 'ref.followup.index',
        icon: ClipboardList,
        current: 'ref.followup.*',
        permission: 'master.followup',
    },
    {
        title: 'Klasifikasi Arsip',
        href: 'ref.klasifikasi.index',
        icon: FolderOpen,
        current: 'ref.klasifikasi.*',
        permission: 'master.klasifikasi',
    },
    {
        title: 'Jabatan',
        href: 'ref.position.index',
        icon: BriefcaseBusiness,
        current: 'ref.position.*',
        permission: 'master.jabatan',
    },
    {
        title: 'Pengguna',
        href: 'ref.user.index',
        icon: UserGroup,
        current: 'ref.user.*',
        permission: 'master.pengguna',
    },
    {
        title: 'Hak Akses',
        href: 'ref.role.index',
        icon: ShieldLock,
        current: 'ref.role.*',
        permission: 'master.pengguna',
    },
];

export default masterNavItems;
