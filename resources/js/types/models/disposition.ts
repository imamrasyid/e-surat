import type { Letter } from './letter';
import type { LetterRecipient } from './letter-recipient';
import type { Position } from './position';
import type { User } from './user';

export interface Disposition {
    id: number;
    letter_id: number;
    letter_recipient_id: number;
    is_cc: boolean;
    from_user_id: number;
    from_position_id: number;
    to_position_id: number;
    instruction: string | null;
    followup: string | null;
    received_at: string | null;
    read_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at?: string | null;

    // Relationships
    letter?: Letter | null;
    recipient?: LetterRecipient | null;
    from_position?: Position | null;
    fromPosition?: Position | null;
    to_position?: Position | null;
    toPosition?: Position | null;
    sender_user?: User | null;
    senderUser?: User | null;
}
