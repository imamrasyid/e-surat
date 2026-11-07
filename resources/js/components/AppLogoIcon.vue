<script setup lang="ts">
import { cn } from 'cn';
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" :class="cn('size-8', className)" v-bind="$attrs">
        <path
            fill="currentColor"
            fill-rule="evenodd"
            d="M24 2a22 22 0 1 0 0 44 22 22 0 1 0 0-44ZM10.81 10.96Q10.19 10.63 10.89 10.63L18.55 10.63Q19.25 10.63 19.77 11.1L31.88 22.17Q32.4 22.64 31.78 22.31Z M13.36 14.38Q12.89 13.86 13.49 14.22L33.69 26.34Q34.29 26.7 33.69 27.06L30.29 29.1Q29.69 29.46 29.13 29.05L22.4 24.12Q21.84 23.7 21.37 23.18Z M20.88 26.95Q20.35 26.5 20.35 27.2L20.35 37.59Q20.35 38.29 20.9 37.85L26.88 33.06Q27.43 32.63 26.9 32.17Z M28.76 14.63Q28.18 14.24 28.72 13.8L33.05 10.18Q33.59 9.73 33.59 10.43L33.59 17.15Q33.59 17.85 33.01 17.46Z M36.01 14.53Q36.01 13.83 36.44 14.38L38.14 16.57Q38.57 17.12 37.87 17.12L36.71 17.12Q36.01 17.12 36.01 16.42Z"
        />
    </svg>
</template>
