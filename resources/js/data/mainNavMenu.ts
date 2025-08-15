import { type NavItem } from '@/types';
import { Award, ClipboardList, Inbox, LayoutGrid, Mails, SendHorizonal, SquarePen } from 'lucide-vue-next';

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
        href: 'dashboard',
        icon: ClipboardList,
        current: '',
        notif: 9,
    },
];

export { inboxItems, platformItems };
