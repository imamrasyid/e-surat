import type { Role } from './role';

export interface Permission {
    id: number;
    name: string;
    guard_name: string;
    created_at?: string;
    updated_at?: string;
    pivot?: {
        model_id?: number;
        model_type?: string;
        permission_id?: number;
        role_id?: number;
        [key: string]: unknown;
    };
    roles?: Role[];
}
