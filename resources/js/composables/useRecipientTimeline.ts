import type { TimelineRecipient } from '@/types';
import { useHttp } from '@inertiajs/vue3';
import { shallowRef } from 'vue';
import { toast } from 'vue-sonner';

export function useRecipientTimeline() {
    const http = useHttp();
    const data = shallowRef<TimelineRecipient[]>([]);
    const isOpen = shallowRef(false);
    const isLoading = shallowRef(false);

    async function open(recipientId: number): Promise<void> {
        if (isLoading.value) return;

        isLoading.value = true;

        try {
            data.value = (await http.get(route('api.timeline.recipient', recipientId))) as TimelineRecipient[];
            isOpen.value = true;
        } catch {
            toast.error('Gagal memuat timeline.');
        } finally {
            isLoading.value = false;
        }
    }

    return { data, isOpen, isLoading, open };
}
