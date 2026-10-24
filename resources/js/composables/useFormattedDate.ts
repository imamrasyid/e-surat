const longDateFormatter = new Intl.DateTimeFormat('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
});

const shortDateTimeFormatter = new Intl.DateTimeFormat('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
});

const fullDateTimeFormatter = new Intl.DateTimeFormat('id-ID', {
    weekday: 'long',
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
});

// en-GB keeps the dd/mm/yy order and the 24-hour colon time (id-ID renders 19.19).
const numericDateTimeFormatter = new Intl.DateTimeFormat('en-GB', {
    day: '2-digit',
    month: '2-digit',
    year: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
});

function toDate(value: string | null | undefined): Date | null {
    if (!value) return null;

    const date = new Date(value);

    return Number.isNaN(date.getTime()) ? null : date;
}

export function useFormattedDate() {
    function formatCreate(dateString: string): string {
        const date = new Date(dateString);
        const now = new Date();

        // Hari ini
        if (date.getDate() === now.getDate() && date.getMonth() === now.getMonth() && date.getFullYear() === now.getFullYear()) {
            return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        }

        // Tahun ini
        if (date.getFullYear() === now.getFullYear()) {
            return `${date.getDate()} ${date.toLocaleString('id-ID', { month: 'short' })}`;
        }

        // Tahun lain
        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: '2-digit',
            year: '2-digit',
        });
    }

    function formatDate(value: string | null | undefined): string {
        const date = toDate(value);

        return date ? longDateFormatter.format(date) : '-';
    }

    function formatDateTime(value: string | null | undefined): string {
        const date = toDate(value);

        return date ? shortDateTimeFormatter.format(date) : '-';
    }

    function formatFullDateTime(value: string | null | undefined): string {
        const date = toDate(value);

        return date ? fullDateTimeFormatter.format(date) : '-';
    }

    function formatNumericDateTime(value: string | null | undefined): string {
        const date = toDate(value);

        return date ? numericDateTimeFormatter.format(date) : '-';
    }

    return { formatCreate, formatDate, formatDateTime, formatFullDateTime, formatNumericDateTime };
}
