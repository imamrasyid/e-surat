import type { Disposition } from './disposition';
import type { LetterRecipient } from './letter-recipient';
import type { Memo } from './memo';
import type { User, UserPositionPivot, UserSpecialDispositionPivot } from './user';

export interface Position {
    id: number;
    name: string;
    parent_id: number | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;

    // Accessors
    parent_name?: string | null;
    parentName?: string | null;

    // Relationships
    parent?: Position | null;
    children?: Position[];
    users?: User[];
    recipients?: LetterRecipient[];
    dispositions?: Disposition[];
    memos?: Memo[];

    // Pivot data when loaded via relationships
    pivot?: UserPositionPivot | UserSpecialDispositionPivot;
}
