import type { Letter } from './letter';
import type { User } from './user';

export interface LetterActivity {
    id: number;
    letter_id: number;
    user_id: number;
    action: string;
    description: string;
    changes: Record<string, unknown> | null;
    created_at: string;

    // Relationships
    letter?: Letter | null;
    user?: User | null;
}
