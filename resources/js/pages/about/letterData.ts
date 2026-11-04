import type { Tone } from './tone';

export type RecipientStep = {
    from: string;
    to: string;
    instruction: string;
    readAt: string;
};

export type LetterSample = {
    agenda: string;
    sender: string;
    senderShort: string;
    subject: string;
    number: string;
    date: string;
    receiptDate: string;
    receivedAt: string;
    doneAt: string;
    status: 'Open' | 'Close';
    instruction: string;
    attachment: string;
    recipients: RecipientStep[];
};

export type InboxSample = {
    agenda: string;
    sender: string;
    subject: string;
    marks: Array<'D' | 'ND'>;
    time: string;
    unread?: boolean;
};

export type MailboxSample = {
    label: 'Utama' | 'Undangan' | 'Disposisi' | 'Nota Dinas' | 'Terkirim';
    agenda: string;
    sender: string;
    subject: string;
    marks: Array<'D' | 'ND'>;
    time: string;
    tone: Tone;
};

export const primaryLetter: LetterSample = {
    agenda: '142/005/2026',
    sender: 'Sekretariat Daerah Provinsi Jawa Barat',
    senderShort: 'Sekretariat Daerah',
    subject: 'Undangan Rapat Koordinasi Kearsipan',
    number: '005/1287/SETDA/2026',
    date: '12 Okt 2026',
    receiptDate: '13 Okt 2026',
    receivedAt: '13 Okt, 08.42',
    doneAt: '13 Okt, 10.15',
    status: 'Open',
    instruction: 'Hadir dan sampaikan bahan koordinasi.',
    attachment: '005142_Undangan-Rakor.pdf',
    recipients: [
        {
            from: 'Sekretaris',
            to: 'Kabag Umum',
            instruction: 'Teruskan ke bidang terkait',
            readAt: '13 Okt, 09.14',
        },
        {
            from: 'Kabag Umum',
            to: 'Kasubbag Perencanaan',
            instruction: 'Siapkan bahan rapat',
            readAt: '13 Okt, 10.02',
        },
    ],
};

export const inboxLetters: InboxSample[] = [
    {
        agenda: primaryLetter.agenda,
        sender: primaryLetter.senderShort,
        subject: primaryLetter.subject,
        marks: ['D', 'ND'],
        time: '13 Okt',
        unread: true,
    },
    {
        agenda: '067/090/2026',
        sender: 'Dinas Pendidikan',
        subject: 'Permohonan Data Sarana Prasarana',
        marks: ['D'],
        time: '13 Okt',
    },
    {
        agenda: '289/411.1/2026',
        sender: 'Badan Kepegawaian Daerah',
        subject: 'Usulan Kenaikan Pangkat Periode Oktober',
        marks: ['ND'],
        time: '12 Okt',
    },
    {
        agenda: '301/005/2026',
        sender: 'Kementerian Dalam Negeri',
        subject: 'Undangan Rapat Fasilitasi Kelembagaan',
        marks: ['D'],
        time: '11 Okt',
    },
    {
        agenda: '033/090/2026',
        sender: 'Sekretariat DPRD',
        subject: 'Penyampaian Jadwal Rapat Paripurna',
        marks: ['ND'],
        time: '10 Okt',
    },
];

const utama = inboxLetters[0];
const terkirim = inboxLetters[1];
const notaDinas = inboxLetters[2];
const undangan = inboxLetters[3];

/**
 * One sample per workspace so the strip shows real categories, not invented
 * folders: Primary, Undangan, Disposisi, Nota Dinas, and Terkirim.
 */
export const mailboxStrip: MailboxSample[] = [
    {
        label: 'Utama',
        agenda: utama.agenda,
        sender: utama.sender,
        subject: utama.subject,
        marks: utama.marks,
        time: utama.time,
        tone: 'primary',
    },
    {
        label: 'Undangan',
        agenda: undangan.agenda,
        sender: undangan.sender,
        subject: undangan.subject,
        marks: undangan.marks,
        time: undangan.time,
        tone: 'info',
    },
    {
        label: 'Disposisi',
        agenda: primaryLetter.agenda,
        sender: primaryLetter.recipients[0].from,
        subject: primaryLetter.subject,
        marks: ['D'],
        time: primaryLetter.recipients[0].readAt,
        tone: 'warning',
    },
    {
        label: 'Nota Dinas',
        agenda: notaDinas.agenda,
        sender: notaDinas.sender,
        subject: notaDinas.subject,
        marks: notaDinas.marks,
        time: notaDinas.time,
        tone: 'success',
    },
    {
        label: 'Terkirim',
        agenda: terkirim.agenda,
        sender: terkirim.sender,
        subject: terkirim.subject,
        marks: terkirim.marks,
        time: terkirim.time,
        tone: 'primary',
    },
];
