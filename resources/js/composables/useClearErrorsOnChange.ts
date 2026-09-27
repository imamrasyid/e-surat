import {
    getCurrentScope,
    onScopeDispose,
    readonly,
    ref,
    toRaw,
    toValue,
    watch,
    type DeepReadonly,
    type MaybeRefOrGetter,
    type Ref,
    type WatchStopHandle,
} from 'vue';

/**
 * Minimal interface representing a form object compatible with Inertia.js or Vue reactive forms.
 */
export interface FormLike<T = any> {
    errors: Record<string, any>;
    data?: () => T;
    clearErrors?: any;
    [key: string]: any;
}

export interface ClearErrorsOptions {
    /**
     * Debounce duration in milliseconds before clearing the error once a field change is detected.
     * Set to 0 or leave undefined for immediate clearing.
     * Can be a number, ref, or getter.
     * @default 0
     */
    debounceMs?: MaybeRefOrGetter<number | undefined>;

    /**
     * Force all field watchers to use deep watching.
     * If not specified, deep watching is automatically enabled for objects and arrays.
     */
    forceDeep?: MaybeRefOrGetter<boolean | undefined>;

    /**
     * Optional filter predicate to determine if an error key should be watched.
     * Returning false will skip watching and auto-clearing that error key.
     */
    filter?: (key: string) => boolean;

    /**
     * Specific error keys to include. Only keys matching will be watched.
     * Accepts strings or Regular Expressions.
     */
    include?: MaybeRefOrGetter<(string | RegExp)[] | undefined>;

    /**
     * Specific error keys to exclude/ignore.
     * Accepts strings or Regular Expressions.
     */
    exclude?: MaybeRefOrGetter<(string | RegExp)[] | undefined>;

    /**
     * Optional callback invoked whenever an error key is cleared.
     */
    onClear?: (key: string) => void;

    /**
     * Custom equality comparator to determine if the field value has changed from its initial value.
     */
    isEqual?: (newValue: unknown, oldValue: unknown) => boolean;
}

export interface UseClearErrorsOnChangeReturn {
    /** Stop all active field watchers and clear all pending debounce timers */
    stop: () => void;
    /** Manually clear an error for a specific key and stop its watcher */
    clearError: (key: string) => void;
    /** Clear all errors currently in the form and stop all watchers */
    clearAllErrors: () => void;
    /** Check whether a specific error key is currently being watched */
    isWatching: (key: string) => boolean;
    /** Readonly ref containing the list of error keys currently being watched */
    activeKeys: DeepReadonly<Ref<string[]>>;
}

/**
 * Resolves a field value from the form using direct access or dot/bracket notation.
 * Avoids calling `form.data()` unnecessarily so reactivity is tracked only for the target property.
 */
function getFieldValue(target: any, path: string): unknown {
    if (target == null) return undefined;

    // 1. Direct key match (e.g. 'sender', or literal dot-containing property name)
    if (typeof target === 'object' && path in target) {
        return target[path];
    }

    // 2. Normalize path (e.g. 'recipients[0].name' -> 'recipients.0.name')
    const normalizedPath = path.replace(/\[(\w+)\]/g, '.$1');
    const segments = normalizedPath.split('.');

    // Check if the root segment is directly on target, or fallback to target.data()
    let current: any = target;
    const firstSegment = segments[0];

    if (typeof target.data === 'function' && typeof target === 'object' && !(firstSegment in target)) {
        try {
            current = target.data();
        } catch {
            current = target;
        }
    }

    for (const segment of segments) {
        if (current == null || typeof current !== 'object') {
            return undefined;
        }
        current = current[segment];
    }

    return current;
}

function isObjectOrArray(v: unknown): v is Record<string, any> | any[] {
    return typeof v === 'object' && v !== null;
}

/**
 * Clear Laravel/Inertia validation errors only when the corresponding field changes.
 *
 * Supports plain values, Vue refs, and getters for both form and reactive options.
 *
 * @example
 * ```ts
 * // Standard Inertia form usage
 * const form = useForm({ email: '', password: '' });
 * useClearErrorsOnChange(form, { debounceMs: 120 });
 *
 * // With Ref or custom options
 * const formRef = ref(form);
 * const { clearError, activeKeys } = useClearErrorsOnChange(formRef, {
 *   debounceMs: 150,
 *   exclude: ['server_error'],
 *   onClear: (key) => console.log(`Cleared: ${key}`),
 * });
 * ```
 */
export function useClearErrorsOnChange<TForm extends FormLike>(
    form: MaybeRefOrGetter<TForm | null | undefined>,
    options: ClearErrorsOptions = {},
): UseClearErrorsOnChangeReturn {
    const watchers = new Map<string, WatchStopHandle>();
    const timers = new Map<string, ReturnType<typeof setTimeout>>();
    const initialValues = new Map<string, unknown>();
    const activeKeys = ref<string[]>([]);

    const updateActiveKeys = () => {
        activeKeys.value = Array.from(watchers.keys());
    };

    const shouldWatchKey = (key: string): boolean => {
        if (options.filter && !options.filter(key)) {
            return false;
        }

        const includes = toValue(options.include);
        if (includes && includes.length > 0) {
            const matchesInclude = includes.some((pattern) => (typeof pattern === 'string' ? pattern === key : pattern.test(key)));
            if (!matchesInclude) return false;
        }

        const excludes = toValue(options.exclude);
        if (excludes && excludes.length > 0) {
            const matchesExclude = excludes.some((pattern) => (typeof pattern === 'string' ? pattern === key : pattern.test(key)));
            if (matchesExclude) return false;
        }

        return true;
    };

    const stopFieldWatcher = (key: string) => {
        const stopHandle = watchers.get(key);
        if (stopHandle) {
            stopHandle();
            watchers.delete(key);
        }
        const timer = timers.get(key);
        if (timer) {
            clearTimeout(timer);
            timers.delete(key);
        }
        initialValues.delete(key);
        updateActiveKeys();
    };

    const executeClear = (key: string) => {
        const resolvedForm = toValue(form);
        if (!resolvedForm) return;

        // 1. If form has clearErrors method (InertiaForm)
        if (typeof resolvedForm.clearErrors === 'function') {
            resolvedForm.clearErrors(key);
        }

        // 2. Also ensure errors object property is removed if still present
        if (resolvedForm.errors && Object.prototype.hasOwnProperty.call(resolvedForm.errors, key)) {
            delete resolvedForm.errors[key];
        }

        stopFieldWatcher(key);
        options.onClear?.(key);
    };

    const createWatcher = (key: string) => {
        if (watchers.has(key) || !shouldWatchKey(key)) return;

        const getter = () => {
            const currentForm = toValue(form);
            return getFieldValue(currentForm, key);
        };

        const initial = toRaw(getter());
        initialValues.set(key, initial);

        const forcedDeep = toValue(options.forceDeep);
        const deepOpt = forcedDeep ?? isObjectOrArray(initial);

        const stop = watch(
            getter,
            (newVal) => {
                // If a custom isEqual comparator is provided, verify value has changed from initial
                if (options.isEqual) {
                    const initVal = initialValues.get(key);
                    if (options.isEqual(newVal, initVal)) {
                        return;
                    }
                }

                const debounce = toValue(options.debounceMs) ?? 0;
                if (debounce > 0) {
                    const prevTimer = timers.get(key);
                    if (prevTimer) clearTimeout(prevTimer);

                    const timer = setTimeout(() => {
                        executeClear(key);
                    }, debounce);

                    timers.set(key, timer);
                } else {
                    executeClear(key);
                }
            },
            { deep: deepOpt },
        );

        watchers.set(key, stop);
        updateActiveKeys();
    };

    // Watch keys of form.errors so we can dynamically add/remove watchers as errors appear or are cleared.
    const stopKeysWatcher = watch(
        () => {
            const resolvedForm = toValue(form);
            const errors = resolvedForm?.errors;
            return errors && typeof errors === 'object' ? Object.keys(errors) : [];
        },
        (newKeys, oldKeys = []) => {
            const prevKeys = oldKeys ?? [];
            const added = newKeys.filter((k) => !prevKeys.includes(k));
            const removed = prevKeys.filter((k) => !newKeys.includes(k));

            for (const k of added) {
                createWatcher(k);
            }

            for (const k of removed) {
                stopFieldWatcher(k);
            }
        },
        { immediate: true },
    );

    const stopAll = () => {
        stopKeysWatcher();
        for (const stopHandle of watchers.values()) {
            stopHandle();
        }
        watchers.clear();

        for (const timer of timers.values()) {
            clearTimeout(timer);
        }
        timers.clear();
        initialValues.clear();
        updateActiveKeys();
    };

    const clearError = (key: string) => {
        executeClear(key);
    };

    const clearAllErrors = () => {
        const resolvedForm = toValue(form);
        if (!resolvedForm) return;

        if (typeof resolvedForm.clearErrors === 'function') {
            resolvedForm.clearErrors();
        }
        if (resolvedForm.errors) {
            for (const key of Object.keys(resolvedForm.errors)) {
                delete resolvedForm.errors[key];
            }
        }
        stopAll();
    };

    const isWatching = (key: string): boolean => {
        return watchers.has(key);
    };

    // Clean up when current component or reactive effect scope is disposed
    if (getCurrentScope()) {
        onScopeDispose(stopAll);
    }

    return {
        stop: stopAll,
        clearError,
        clearAllErrors,
        isWatching,
        activeKeys: readonly(activeKeys),
    };
}
