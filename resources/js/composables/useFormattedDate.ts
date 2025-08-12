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

    return { formatCreate };
}
