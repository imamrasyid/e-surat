// useClearLaravelErrors.ts
import { toRaw, watch, type WatchStopHandle } from 'vue';

type FormLike<T> = {
    data: () => T;
    errors: Record<string, any>;
};

type Options = {
    /** debounce per-field in ms (default 0 = no debounce) */
    debounceMs?: number;
    /** force all watchers to be deep (optional) */
    forceDeep?: boolean;
};

/**
 * Clear Laravel validation errors only when the corresponding field changes.
 * Works correctly for primitives, objects and arrays.
 */
export function useClearErrorsOnChange<T extends object>(
    form: FormLike<T>,
    options: Options = {},
): {
    stop: () => void;
    clearError: (key: string) => void;
} {
    const watchers = new Map<string, WatchStopHandle>();
    const timers = new Map<string, ReturnType<typeof setTimeout> | null>();

    const isObjectOrArray = (v: unknown): v is Record<string, any> | any[] => typeof v === 'object' && v !== null;

    const createWatcher = (key: string) => {
        if (watchers.has(key)) return;

        const pathParts = key.split('.');
        const getter = () => {
            let cur: any = form.data();
            for (const p of pathParts) {
                if (cur && Object.prototype.hasOwnProperty.call(cur, p)) {
                    cur = cur[p];
                } else {
                    return undefined;
                }
            }
            return cur;
        };

        const initial = toRaw(getter());
        const deepOpt = options.forceDeep ?? isObjectOrArray(initial);

        const stop = watch(
            getter,
            (_newVal, _oldVal) => {
                if (options.debounceMs && options.debounceMs > 0) {
                    const prev = timers.get(key);
                    if (prev) clearTimeout(prev);
                    const t = setTimeout(() => {
                        // hapus error dan hentikan watcher
                        delete form.errors[key];
                        const s = watchers.get(key);
                        if (s) {
                            s();
                            watchers.delete(key);
                        }
                        timers.delete(key);
                    }, options.debounceMs);
                    timers.set(key, t);
                } else {
                    delete form.errors[key];
                    const s = watchers.get(key);
                    if (s) {
                        s();
                        watchers.delete(key);
                    }
                    timers.delete(key);
                }
            },
            { deep: deepOpt },
        );

        watchers.set(key, stop);
        timers.set(key, null);
    };

    // Watch keys of form.errors so we can add/remove watchers as errors appear/disappear.
    const stopKeysWatcher = watch(
        () => Object.keys(form.errors),
        (newKeys = [], oldKeys = []) => {
            const added = newKeys.filter((k) => !oldKeys.includes(k));
            const removed = oldKeys.filter((k) => !newKeys.includes(k));

            for (const k of added) createWatcher(k);

            for (const k of removed) {
                const s = watchers.get(k);
                if (s) s();
                watchers.delete(k);
                const t = timers.get(k);
                if (t) clearTimeout(t);
                timers.delete(k);
            }
        },
        { immediate: true },
    );

    const stopAll = () => {
        stopKeysWatcher();
        for (const s of watchers.values()) s();
        watchers.clear();
        for (const t of timers.values()) if (t) clearTimeout(t);
        timers.clear();
    };

    const clearError = (key: string) => {
        delete form.errors[key];
        const s = watchers.get(key);
        if (s) {
            s();
            watchers.delete(key);
        }
        const t = timers.get(key);
        if (t) clearTimeout(t);
        timers.delete(key);
    };

    return { stop: stopAll, clearError };
}
