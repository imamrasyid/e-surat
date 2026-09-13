import type { RefAgendaSurat } from './ref-agenda-surat';

export interface RefKdKlasifikasi {
    id: number;
    kode: string;
    nama: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;

    // Relationships
    agenda_surats?: RefAgendaSurat[];
    agendaSurats?: RefAgendaSurat[];
}
