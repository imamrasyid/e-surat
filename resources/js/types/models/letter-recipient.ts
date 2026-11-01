import type { Disposition } from './disposition';
import type { Letter } from './letter';
import type { Memo } from './memo';
import type { Position } from './position';
import type { User } from './user';

export interface LetterRecipient {
    id: number;
    letter_id: number;
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
    from_user?: User | null;
    fromUser?: User | null;
    from_position?: Position | null;
    fromPosition?: Position | null;
    to_position?: Position | null;
    toPosition?: Position | null;
    dispositions?: Disposition[];
    memos?: Memo[];
    activities?: RecipientActivity[];
}

export type RecipientActivityType = 'disposisi' | 'nota_dinas';

export interface RecipientActivity {
    type: RecipientActivityType;
    id: number;
    received_at: string | null;
    created_at: string | null;
}

export interface TimelineRecipient {
    letter_id: number;
    is_cc: boolean;
    from_position_id: number;
    to_position_id: number;
    instruction: string | null;
    followup: string | null;
    received_at: string | null;
    read_at: string | null;
    type: string;
    from_position: Position;
    to_position: Position;
}
