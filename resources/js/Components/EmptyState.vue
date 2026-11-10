<script setup>
import Icon from './Icon.vue';

defineProps({
    icon: { type: String, default: 'inbox' },
    title: { type: String, required: true },
    description: { type: String, default: null },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center px-6 py-14 text-center">
        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
            <Icon :name="icon" size="h-6 w-6" />
        </span>

        <p class="mt-4 text-sm font-semibold text-slate-900 dark:text-white">{{ title }}</p>
        <p v-if="description" class="mt-1 max-w-sm text-sm text-slate-500 dark:text-slate-400">
            {{ description }}
        </p>

        <div v-if="$slots.default" class="mt-5">
            <slot />
        </div>
    </div>
</template>
