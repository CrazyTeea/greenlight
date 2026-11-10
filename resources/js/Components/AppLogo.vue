<script setup>
defineProps({
    compact: { type: Boolean, default: false },
    inverted: { type: Boolean, default: false },
});
</script>

<template>
    <div class="flex items-center gap-3">
        <span
            class="relative flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-sm shadow-emerald-600/20"
        >
            <svg viewBox="0 0 24 24" class="h-5 w-5 text-white" fill="none" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </span>

        <span v-if="!compact" class="leading-tight">
            <span
                class="block text-[15px] font-semibold tracking-tight"
                :class="inverted ? 'text-white' : 'text-slate-900 dark:text-white'"
            >
                Greenlight
            </span>
            <span
                class="block text-[11px] font-medium uppercase tracking-[0.14em]"
                :class="inverted ? 'text-slate-400' : 'text-slate-400 dark:text-slate-500'"
            >
                approvals
            </span>
        </span>
    </div>
</template>
