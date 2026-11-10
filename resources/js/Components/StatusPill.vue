<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: { type: Object, required: true },
});

const tones = {
    draft: 'bg-slate-100 text-slate-600 ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700',
    pending: 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/20',
    approved:
        'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/20',
    rejected: 'bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/20',
    cancelled: 'bg-slate-50 text-slate-500 ring-slate-200 dark:bg-slate-800/60 dark:text-slate-400 dark:ring-slate-700',
};

const dots = {
    draft: 'bg-slate-400',
    pending: 'bg-amber-500',
    approved: 'bg-emerald-500',
    rejected: 'bg-rose-500',
    cancelled: 'bg-slate-400',
};

const tone = computed(() => tones[props.status.value] ?? tones.draft);
const dot = computed(() => dots[props.status.value] ?? dots.draft);
</script>

<template>
    <span
        :class="tone"
        class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset"
    >
        <span :class="[dot, status.value === 'pending' ? 'animate-pulse' : '']" class="h-1.5 w-1.5 rounded-full" />
        {{ status.label }}
    </span>
</template>
