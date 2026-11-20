import type { Disposition } from './disposition';
import type { LetterActivity } from './letter-activity';
import type { LetterDoc } from './letter-doc';
import type { LetterRecipient } from './letter-recipient';
import type { Memo } from './memo';
import type { User } from './user';

export type LetterType = 'in' | 'out';
export type LetterStatus = 'open' | 'close';
export type LetterSecurityClassification = 'SR' | 'R' | 'T' | 'B';
export type LetterReceivedVia = 'email' | 'physical' | 'internal' | 'other';

export interface Letter {
    id: number;
    letter_id: string; // ULID
    sender: string;
    number: string | null;
    date: string; // YYYY-MM-DD
    receipt_date: string; // ISO datetime string
    subject: string;
    category: string;
    security_classification: LetterSecurityClassification;
    received_via: LetterReceivedVia;
    agenda: string;
    type: LetterType;
    status: LetterStatus;
    created_by: number;
    created_at: string;
    updated_at: string;
    deleted_at?: string | null;

    // Relationships
    recipients?: LetterRecipient[];
    dispositions?: Disposition[];
    memos?: Memo[];
    docs?: LetterDoc[];
    sender_user?: User | null;
    senderUser?: User | null;
    activities?: LetterActivity[];
}
