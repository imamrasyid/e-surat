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
    category: string;
    status: string;
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

export type ArchiveSample = {
    agenda: string;
    sender: string;
    subject: string;
    category: string;
    status: 'Terbuka' | 'Selesai';
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
    category: 'Undangan',
    status: 'Terbuka',
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

export const archiveLetters: ArchiveSample[] = [
    {
        agenda: '142/005/2026',
        sender: 'Sekretariat Daerah',
        subject: 'Undangan Rapat Koordinasi Kearsipan',
        category: 'Undangan',
        status: 'Terbuka',
    },
    {
        agenda: '097/090/2026',
        sender: 'Inspektorat Daerah',
        subject: 'Notula Rapat Evaluasi Kinerja Triwulan III',
        category: 'Primary',
        status: 'Selesai',
    },
    {
        agenda: '081/090/2026',
        sender: 'Badan Perencanaan Pembangunan Daerah',
        subject: 'Bahan Rapat Koordinasi Wilayah',
        category: 'Primary',
        status: 'Selesai',
    },
    {
        agenda: '073/090/2026',
        sender: 'Dinas Kesehatan',
        subject: 'Rapat Koordinasi Program Imunisasi',
        category: 'Primary',
        status: 'Selesai',
    },
    {
        agenda: '052/005/2026',
        sender: 'Sekretariat Daerah',
        subject: 'Undangan Rapat Pimpinan Bulanan',
        category: 'Undangan',
        status: 'Terbuka',
    },
];
