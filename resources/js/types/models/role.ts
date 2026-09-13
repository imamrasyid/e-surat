import type { Permission } from './permission';
import type { User } from './user';

export interface Role {
    id: number;
    name: string;
    guard_name: string;
    created_at?: string;
    updated_at?: string;
    pivot?: {
        model_id?: number;
        model_type?: string;
        role_id?: number;
        [key: string]: unknown;
    };
    permissions?: Permission[];
    users?: User[];
}
