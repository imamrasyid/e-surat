import type { Letter } from './letter';

export interface LetterDoc {
    id: number;
    agenda_id: number | null;
    letter_id: number;
    name: string;
    download_url: string;
    preview_url: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;

    // Relationships
    letter?: Letter | null;
}
