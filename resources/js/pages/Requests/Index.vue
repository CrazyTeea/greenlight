<script setup>
import { onUnmounted, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import EmptyState from '../../Components/EmptyState.vue';
import Icon from '../../Components/Icon.vue';
import Pagination from '../../Components/Pagination.vue';
import StatusPill from '../../Components/StatusPill.vue';
import UserAvatar from '../../Components/UserAvatar.vue';
import { formatDate, formatMoney, plural } from '../../Utils/format';

const props = defineProps({
    requests: { type: Object, required: true },
    filters: { type: Object, required: true },
    counts: { type: Object, required: true },
    categories: { type: Array, required: true },
});

const statuses = [
    { value: null, label: 'Все' },
    { value: 'draft', label: 'Черновики' },
    { value: 'pending', label: 'На согласовании' },
    { value: 'approved', label: 'Согласованы' },
    { value: 'rejected', label: 'Отклонены' },
    { value: 'cancelled', label: 'Отменены' },
];

const search = ref(props.filters.q ?? '');
let timer = null;

function apply(overrides = {}) {
    const params = {
        status: overrides.status !== undefined ? overrides.status : props.filters.status,
        category: overrides.category !== undefined ? overrides.category : props.filters.category,
        q: overrides.q !== undefined ? overrides.q : search.value,
    };

    Object.keys(params).forEach((key) => {
        if (params[key] === null || params[key] === '' || params[key] === undefined) {
            delete params[key];
        }
    });

    router.get('/requests', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

watch(search, (value) => {
    window.clearTimeout(timer);
    timer = window.setTimeout(() => apply({ q: value }), 320);
});

onUnmounted(() => window.clearTimeout(timer));

const countFor = (value) => (value === null ? props.counts.all : props.counts[value] ?? 0);

function open(request) {
    router.visit(`/requests/${request.id}`);
}
</script>

<template>
    <Head title="Заявки" />

    <AppLayout>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">Заявки</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ counts.all }} {{ plural(counts.all, 'заявка', 'заявки', 'заявок') }}, доступных вашей роли
                </p>
            </div>

            <Link
                href="/requests/create"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm shadow-emerald-600/20 transition hover:bg-emerald-700"
            >
                <Icon name="plus" size="h-4 w-4" />
                Новая заявка
            </Link>
        </div>

        <div
            class="mt-6 rounded-2xl border border-slate-200/80 bg-white shadow-[0_1px_2px_rgba(15,23,42,0.04)] dark:border-slate-800 dark:bg-slate-900"
        >
            <div class="flex flex-col gap-3 border-b border-slate-100 p-4 sm:flex-row sm:items-center dark:border-slate-800">
                <div class="relative flex-1">
                    <Icon
                        name="search"
                        size="h-4 w-4"
                        class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                    />
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Название, код или описание"
                        class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                    />
                </div>

                <select
                    :value="filters.category ?? ''"
                    class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200"
                    @change="apply({ category: $event.target.value || null })"
                >
                    <option value="">Все категории</option>
                    <option v-for="category in categories" :key="category.value" :value="category.value">
                        {{ category.label }}
                    </option>
                </select>
            </div>

            <div class="flex gap-1 overflow-x-auto border-b border-slate-100 px-4 py-2 dark:border-slate-800">
                <button
                    v-for="status in statuses"
                    :key="String(status.value)"
                    type="button"
                    class="whitespace-nowrap rounded-lg px-3 py-1.5 text-sm font-medium transition"
                    :class="
                        filters.status === status.value
                            ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900'
                            : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white'
                    "
                    @click="apply({ status: status.value })"
                >
                    {{ status.label }}
                    <span
                        class="ml-1.5 rounded-md px-1.5 py-0.5 text-[11px]"
                        :class="
                            filters.status === status.value
                                ? 'bg-white/15 text-white dark:bg-slate-900/10 dark:text-slate-700'
                                : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'
                        "
                    >
                        {{ countFor(status.value) }}
                    </span>
                </button>
            </div>

            <div v-if="requests.data.length" class="hidden md:block">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-wide text-slate-400 dark:text-slate-500">
                        <tr class="border-b border-slate-100 dark:border-slate-800">
                            <th class="px-5 py-3 font-medium">Заявка</th>
                            <th class="px-3 py-3 font-medium">Автор</th>
                            <th class="px-3 py-3 font-medium">Категория</th>
                            <th class="px-3 py-3 text-right font-medium">Сумма</th>
                            <th class="px-3 py-3 font-medium">Статус</th>
                            <th class="px-5 py-3 text-right font-medium">Создана</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr
                            v-for="item in requests.data"
                            :key="item.id"
                            class="cursor-pointer transition hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
                            @click="open(item)"
                        >
                            <td class="px-5 py-4">
                                <p class="max-w-xs truncate font-medium text-slate-900 dark:text-white">{{ item.title }}</p>
                                <p class="mt-0.5 text-xs text-slate-400">{{ item.code }}</p>
                            </td>
                            <td class="px-3 py-4">
                                <div class="flex items-center gap-2">
                                    <UserAvatar :person="item.requester" size="h-7 w-7" text="text-[10px]" />
                                    <span class="text-slate-600 dark:text-slate-300">{{ item.requester.name }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-4 text-slate-500 dark:text-slate-400">{{ item.category.label }}</td>
                            <td class="px-3 py-4 text-right font-medium tabular-nums text-slate-900 dark:text-white">
                                {{ formatMoney(item.amount) }}
                            </td>
                            <td class="px-3 py-4"><StatusPill :status="item.status" /></td>
                            <td class="px-5 py-4 text-right text-xs text-slate-400">{{ formatDate(item.created_at) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="requests.data.length" class="divide-y divide-slate-100 md:hidden dark:divide-slate-800">
                <Link
                    v-for="item in requests.data"
                    :key="item.id"
                    :href="`/requests/${item.id}`"
                    class="block p-4 transition active:bg-slate-50 dark:active:bg-slate-800/40"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate font-medium text-slate-900 dark:text-white">{{ item.title }}</p>
                            <p class="mt-0.5 text-xs text-slate-400">{{ item.code }} · {{ item.category.label }}</p>
                        </div>
                        <div class="shrink-0">
                            <StatusPill :status="item.status" />
                        </div>
                    </div>

                    <div class="mt-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <UserAvatar :person="item.requester" size="h-6 w-6" text="text-[9px]" />
                            <span class="text-xs text-slate-500 dark:text-slate-400">{{ item.requester.name }}</span>
                        </div>
                        <span class="text-sm font-semibold tabular-nums text-slate-900 dark:text-white">
                            {{ formatMoney(item.amount) }}
                        </span>
                    </div>
                </Link>
            </div>

            <EmptyState
                v-if="!requests.data.length"
                icon="search"
                title="Ничего не найдено"
                description="Попробуйте изменить фильтры или создайте новую заявку."
            >
                <Link
                    href="/requests/create"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700"
                >
                    <Icon name="plus" size="h-4 w-4" />
                    Создать заявку
                </Link>
            </EmptyState>

            <div v-if="requests.data.length" class="border-t border-slate-100 px-4 py-3 dark:border-slate-800">
                <Pagination :links="requests.links" />
            </div>
        </div>
    </AppLayout>
</template>
