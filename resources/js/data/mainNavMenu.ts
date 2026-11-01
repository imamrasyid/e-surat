import { type NavItem } from '@/types';
import { Inbox, LayoutGrid, Mails, Radio, SendHorizonal, SquarePen, StickyNote } from '@lucide/vue';

const platformItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: 'dashboard',
        icon: LayoutGrid,
        current: 'dashboard',
        permission: '*',
    },
    {
        title: 'Compose',
        href: 'app.compose.create',
        icon: SquarePen,
        current: 'app.compose.*',
        permission: 'compose.create',
    },
    {
        title: 'Terkirim',
        href: 'sent.index',
        icon: SendHorizonal,
        current: 'sent.*',
        permission: 'sent.index',
    },
];

const inboxItems: NavItem[] = [
    {
        title: 'Utama',
        href: 'inbox.primary.index',
        icon: Inbox,
        current: 'inbox.primary.*',
        notif: 1,
        permission: 'primary.index',
    },
    {
        title: 'Undangan',
        href: 'inbox.undangan.index',
        icon: Radio,
        current: 'inbox.undangan.*',
        notif: 2,
        permission: 'undangan.index',
    },
    {
        title: 'Disposisi',
        href: 'dispo.index',
        icon: Mails,
        current: 'dispo.*',
        notif: 8,
        permission: 'disposition.index',
    },
    {
        title: 'Nota Dinas',
        href: 'memo.index',
        icon: StickyNote,
        current: 'memo.*',
        notif: 9,
        permission: 'memo.index',
    },
];

export { inboxItems, platformItems };
