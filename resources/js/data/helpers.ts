const categoryInbox = [
    {
        value: '1',
        name: 'Utama',
    },
    {
        value: '2',
        name: 'Undangan',
    },
];

// Sifat/klasifikasi keamanan TNDE; B berarti Biasa/Terbuka.
const letterSecurityClassification = [
    {
        value: 'SR',
        name: 'Sangat Rahasia',
    },
    {
        value: 'R',
        name: 'Rahasia',
    },
    {
        value: 'T',
        name: 'Terbatas',
    },
    {
        value: 'B',
        name: 'Biasa/Terbuka',
    },
];

const letterReceivedVia = [
    {
        value: 'email',
        name: 'Email',
    },
    {
        value: 'physical',
        name: 'Fisik',
    },
    {
        value: 'internal',
        name: 'Internal',
    },
    {
        value: 'other',
        name: 'Lainnya',
    },
];

const isActiveHelper = [
    {
        value: 1,
        name: 'Aktif',
    },
    {
        value: 0,
        name: 'Non Aktif',
    },
];

const statusHelper = [
    {
        value: 'all',
        name: 'Semua',
    },
    {
        value: 'unread',
        name: 'Belum Dibaca',
    },
    {
        value: 'read',
        name: 'Dibaca',
    },
];

export { categoryInbox, isActiveHelper, letterReceivedVia, letterSecurityClassification, statusHelper };
