import { type NavItem } from '@/types';
import { Award, Inbox, LayoutGrid, Mails, SendHorizonal, SquarePen, StickyNote } from 'lucide-vue-next';

const platformItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: 'dashboard',
        icon: LayoutGrid,
        current: 'dashboard',
    },
    {
        title: 'Compose',
        href: 'app.compose.create',
        icon: SquarePen,
        current: 'app.compose.*',
    },
    {
        title: 'Terkirim',
        href: 'sent.index',
        icon: SendHorizonal,
        current: 'sent.*',
    },
];

const inboxItems: NavItem[] = [
    {
        title: 'Primary',
        href: 'inbox.primary.index',
        icon: Inbox,
        current: 'inbox.primary.*',
        notif: 1,
    },
    {
        title: 'Undangan',
        href: 'inbox.undangan.index',
        icon: Award,
        current: 'inbox.undangan.*',
        notif: 2,
    },
    {
        title: 'Disposisi',
        href: 'dispo.index',
        icon: Mails,
        current: 'dispo.*',
        notif: 8,
    },
    {
        title: 'Nota Dinas',
        href: 'memo.index',
        icon: StickyNote,
        current: 'memo.*',
        notif: 9,
    },
];

export { inboxItems, platformItems };
