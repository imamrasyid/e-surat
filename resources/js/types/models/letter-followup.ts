import type { Letter } from './letter';
import type { Position } from './position';

/**
 * Shared row shape for letter follow-ups shown in inbox-style tables
 * (dispositions and internal memos).
 */
export interface LetterFollowUpItem {
    id: number;
    is_cc: boolean;
    instruction: string | null;
    followup: string | null;
    received_at: string | null;
    read_at: string | null;
    from_position?: Position | null;
    letter?: Letter | null;
}
