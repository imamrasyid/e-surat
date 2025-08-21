import { usePage } from '@inertiajs/vue3';

export function usePermissions() {
    const page = usePage();

    const permissions: string[] = page.props.auth?.can ?? [];

    /**
     * Cek apakah user punya permission tertentu
     * Bisa exact match atau wildcard (misalnya: "master.*")
     */
    function can(check: string): boolean {
        if (check.endsWith('.*')) {
            const prefix = check.replace('.*', '');
            return permissions.some((p) => p.startsWith(prefix + '.'));
        }
        return permissions.includes(check);
    }

    return { can, permissions };
}
