import type { DateValue } from '@internationalized/date';

export function createDecadae({ dateObj, startIndex = -10, endIndex = 10 }: { dateObj: DateValue | Date; startIndex?: number; endIndex?: number }) {
    // Mendapatkan tahun dari dateObj
    let year: number;
    if ('year' in dateObj) {
        year = dateObj.year;
    } else {
        year = (dateObj as Date).getFullYear();
    }

    const years = [];
    for (let i = startIndex; i <= endIndex; i++) {
        years.push({ year: year + i });
    }
    return years;
}
