import type { DateValue } from '@internationalized/date';

/**
 * Membuat array tahun berdasarkan tahun sekarang dengan rentang waktu tertentu.
 * @param dateObj Objek tanggal
 * @param startIndex Rentang tahun awal
 * @param endIndex Rentang tahun akhir
 * @returns Array of decades
 */
export function createDecades({ dateObj, startIndex = -10, endIndex = 10 }: { dateObj: DateValue | Date; startIndex?: number; endIndex?: number }) {
    let year: number;
    if ('year' in dateObj) {
        year = dateObj.year;
    } else {
        year = (dateObj as Date).getFullYear();
    }

    const decades = [];
    for (let i = startIndex; i <= endIndex; i++) {
        decades.push({ year: (year + i).toString() });
    }
    return decades;
}
