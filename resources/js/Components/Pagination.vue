<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import Icon from './Icon.vue';

const props = defineProps({
    links: { type: Array, required: true },
});

const pages = computed(() => props.links.filter((link) => /^\d+$/.test(link.label)));
const previous = computed(() => props.links[0]);
const next = computed(() => props.links[props.links.length - 1]);
</script>

<template>
    <nav v-if="links.length > 3" class="flex items-center justify-between gap-2">
        <Link
            v-if="previous.url"
            :href="previous.url"
            preserve-scroll
            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
        >
            <Icon name="chevronLeft" size="h-4 w-4" />
            Назад
        </Link>
        <span v-else class="px-3 py-1.5 text-sm text-slate-400">Назад</span>

        <div class="flex items-center gap-1">
            <Link
                v-for="page in pages"
                :key="page.label"
                :href="page.url ?? ''"
                preserve-scroll
                class="min-w-9 rounded-lg px-3 py-1.5 text-center text-sm font-medium transition"
                :class="
                    page.active
                        ? 'bg-emerald-600 text-white'
                        : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'
                "
            >
                {{ page.label }}
            </Link>
        </div>

        <Link
            v-if="next.url"
            :href="next.url"
            preserve-scroll
            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
        >
            Вперёд
            <Icon name="chevronRight" size="h-4 w-4" />
        </Link>
        <span v-else class="px-3 py-1.5 text-sm text-slate-400">Вперёд</span>
    </nav>
</template>
