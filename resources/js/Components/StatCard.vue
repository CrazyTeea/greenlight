<script setup>
import Icon from './Icon.vue';

defineProps({
    label: { type: String, required: true },
    value: { type: String, required: true },
    hint: { type: String, default: null },
    icon: { type: String, default: 'chart' },
    tone: { type: String, default: 'emerald' },
});

const tones = {
    emerald: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
    amber: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
    sky: 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400',
    slate: 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
};
</script>

<template>
    <div
        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-[0_1px_2px_rgba(15,23,42,0.04)] dark:border-slate-800 dark:bg-slate-900"
    >
        <div class="flex items-start justify-between gap-3">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ label }}</p>
            <span :class="tones[tone]" class="flex h-9 w-9 items-center justify-center rounded-xl">
                <Icon :name="icon" size="h-5 w-5" />
            </span>
        </div>

        <p class="mt-3 text-2xl font-semibold tracking-tight text-slate-900 tabular-nums dark:text-white">
            {{ value }}
        </p>

        <p v-if="hint" class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ hint }}</p>
    </div>
</template>
