/**
 * Single anchor contract for the about page: the in-flow header, the dock menu,
 * and the section ids all read this list instead of keeping their own copy.
 */
export const aboutSections = [
    { href: '#alur', label: 'Alur' },
    { href: '#catatan', label: 'Catatan' },
    { href: '#notifikasi', label: 'Notifikasi' },
    { href: '#peran', label: 'Peran' },
    { href: '#tanya', label: 'Tanya' },
] as const;

export type AboutSection = (typeof aboutSections)[number];
