import type { LetterReceivedVia, LetterSecurityClassification } from './letter';

export type NotificationItemType = 'primary' | 'invitation' | 'disposition' | 'memo';

export interface NotificationListItem {
    id: string;
    title: string;
    body: string;
    url: string;
    item_type: NotificationItemType;
    security_classification: LetterSecurityClassification;
    received_via: LetterReceivedVia;
    instruction: string | null;
    followup: string | null;
    read_at: string | null;
    created_at: string;
}

export interface NotificationCenterProps {
    unread_count: number;
    latest: NotificationListItem[];
    web_push: {
        enabled: boolean;
        public_key: string | null;
    };
}
