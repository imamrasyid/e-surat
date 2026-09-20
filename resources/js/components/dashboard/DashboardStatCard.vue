<script setup lang="ts">
import { Card, CardAction, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { computed, type Component } from 'vue';
import { formatNumber } from './utils';

interface Props {
    title: string;
    description: string;
    value: number;
    variant?: 'primary' | 'warning' | 'success';
    icon: Component;
    iconAnimation?: 'pulse' | 'spin';
    animationDuration?: string;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'primary',
    iconAnimation: undefined,
    animationDuration: undefined,
});

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'warning':
            return 'border-warning/10 bg-gradient-to-br from-warning/10 to-card text-warning';
        case 'success':
            return 'border-success/10 bg-gradient-to-br from-success/10 to-card text-success';
        case 'primary':
        default:
            return 'border-primary/10 bg-gradient-to-br from-primary/10 to-card text-primary';
    }
});

const iconClasses = computed(() => [
    'h-7.5 w-7.5',
    props.iconAnimation === 'pulse' && 'animate-pulse',
    props.iconAnimation === 'spin' && 'animate-spin',
]);

const iconStyle = computed(() => {
    if (props.animationDuration) {
        return { animationDuration: props.animationDuration };
    }
    return undefined;
});

const formattedValue = computed(() => formatNumber(props.value));
</script>

<template>
    <Card class="shadow-none transition-all duration-300 hover:-translate-y-1 hover:shadow-sm" :class="variantClasses">
        <CardHeader>
            <CardTitle>{{ props.title }}</CardTitle>
            <CardDescription>{{ props.description }}</CardDescription>
            <CardAction>
                <component :is="props.icon" :class="iconClasses" :style="iconStyle" />
            </CardAction>
        </CardHeader>
        <CardContent>
            <div class="text-3xl font-bold">{{ formattedValue }} <span class="text-sm font-normal">Surat</span></div>
        </CardContent>
    </Card>
</template>
