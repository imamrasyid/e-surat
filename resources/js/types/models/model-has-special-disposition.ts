import type { Position } from './position';
import type { User } from './user';

export interface ModelHasSpecialDisposition {
    id: number;
    model_type: string;
    model_id: number;
    to_position_id: number;
    is_active: boolean;
    granted_at: string | null;
    revoked_at: string | null;
    created_at: string;
    updated_at: string;

    // Relationships
    position?: Position | null;
    model?: User | null;
}
