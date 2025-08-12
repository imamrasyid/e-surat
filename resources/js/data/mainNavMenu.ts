import { type NavItem } from '@/types';
import { Award, Inbox, LayoutGrid, SendHorizonal, SquarePen } from 'lucide-vue-next';

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
        href: 'dashboard',
        icon: SendHorizonal,
        current: '',
    },
];

const inboxItems: NavItem[] = [
    {
        title: 'Primary',
        href: 'dashboard',
        icon: Inbox,
        current: '',
    },
    {
        title: 'Undangan',
        href: 'dashboard',
        icon: Award,
        current: '',
    },
];

export { inboxItems, platformItems };
