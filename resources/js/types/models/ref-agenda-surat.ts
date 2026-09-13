import type { RefKdKlasifikasi } from './ref-kd-klasifikasi';

export interface RefAgendaSurat {
    id: number;
    klasifikasi_id: number;
    nomor_urut: number;
    tahun: number | string;
    nomor_agenda: string;
    created_at: string;
    updated_at: string;

    // Relationships
    klasifikasi?: RefKdKlasifikasi | null;
}
