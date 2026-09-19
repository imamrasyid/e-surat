import type { Permission } from './permission';
import type { Position } from './position';
import type { Role } from './role';

export interface UserPositionPivot {
    model_id: number;
    model_type: string;
    position_id: number;
    start_date: string | null;
    end_date: string | null;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
}

export interface UserSpecialDispositionPivot {
    model_id: number;
    model_type: string;
    to_position_id: number;
    is_active: boolean;
    granted_at: string | null;
    revoked_at: string | null;
    created_at?: string;
    updated_at?: string;
}

export interface User {
    id: number;
    name: string;
    username: string;
    email: string;
    is_active: boolean;
    email_verified_at: string | null;
    password?: string;
    two_factor_secret?: string | null;
    two_factor_recovery_codes?: string | null;
    two_factor_confirmed_at?: string | null;
    remember_token?: string | null;
    avatar?: string;
    created_at: string;
    updated_at: string;

    // Relationships
    positions?: Position[];
    current_position?: Position[];
    currentPosition?: Position[];
    special_dispositions?: Position[];
    specialDispositions?: Position[];
    all_special_dispositions?: Position[];
    allSpecialDispositions?: Position[];
    roles?: Role[];
    permissions?: Permission[];
    pivot?: UserPositionPivot;
}
