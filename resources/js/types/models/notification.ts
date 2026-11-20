import type { LetterReceivedVia, LetterSecurityClassification } from './letter';

export interface NotificationListItem {
    id: string;
    title: string;
    body: string;
    url: string;
    item_type: string;
    security_classification: LetterSecurityClassification;
    received_via: LetterReceivedVia;
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
