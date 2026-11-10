<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import EmptyState from '../Components/EmptyState.vue';
import Icon from '../Components/Icon.vue';
import StatCard from '../Components/StatCard.vue';
import StatusPill from '../Components/StatusPill.vue';
import UserAvatar from '../Components/UserAvatar.vue';
import { formatMoney, formatRelative, plural } from '../Utils/format';

const props = defineProps({
    stats: { type: Object, required: true },
    awaiting: { type: Object, required: true },
    recent: { type: Array, required: true },
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const greeting = computed(() => {
    const hour = new Date().getHours();

    if (hour < 6) return 'Доброй ночи';
    if (hour < 12) return 'Доброе утро';
    if (hour < 18) return 'Добрый день';

    return 'Добрый вечер';
});

const firstName = computed(() => user.value.name.split(' ')[0]);

const eventTones = {
    created: 'bg-slate-300 dark:bg-slate-600',
    submitted: 'bg-sky-400',
    approved: 'bg-emerald-500',
    rejected: 'bg-rose-500',
    cancelled: 'bg-slate-400',
};
</script>

<template>
    <Head title="Обзор" />

    <AppLayout>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ greeting }}, {{ firstName }}</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">
                    Что происходит с заявками
                </h1>
            </div>

            <Link
                href="/requests/create"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm shadow-emerald-600/20 transition hover:bg-emerald-700"
            >
                <Icon name="plus" size="h-4 w-4" />
                Новая заявка
            </Link>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <StatCard
                label="Ждут вашего решения"
                :value="String(stats.awaiting)"
                :hint="stats.awaiting ? 'Лучше не задерживать' : 'Очередь пуста'"
                icon="inbox"
                tone="amber"
            />
            <StatCard
                label="На согласовании"
                :value="String(stats.in_review)"
                :hint="`${stats.in_review} ${plural(stats.in_review, 'заявка', 'заявки', 'заявок')} в работе`"
                icon="clock"
                tone="sky"
            />
            <StatCard
                label="Согласовано за месяц"
                :value="formatMoney(stats.approved_this_month)"
                hint="Сумма по вашим заявкам"
                icon="check"
                tone="emerald"
            />
            <StatCard
                label="Мои черновики"
                :value="String(stats.my_drafts)"
                :hint="stats.my_drafts ? 'Не отправлены на согласование' : 'Все отправлено'"
                icon="list"
                tone="slate"
            />
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-[1.45fr_1fr]">
            <section
                class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-[0_1px_2px_rgba(15,23,42,0.04)] dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Ждут вашего решения</h2>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                            Заявки, где вы текущий согласующий
                        </p>
                    </div>

                    <Link
                        href="/requests?status=pending"
                        class="text-xs font-medium text-emerald-600 transition hover:text-emerald-700 dark:text-emerald-400"
                    >
                        Все заявки
                    </Link>
                </div>

                <div v-if="awaiting.data.length" class="divide-y divide-slate-100 dark:divide-slate-800">
                    <Link
                        v-for="item in awaiting.data"
                        :key="item.id"
                        :href="`/requests/${item.id}`"
                        class="group flex items-center gap-4 px-5 py-4 transition hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
                    >
                        <UserAvatar :person="item.requester" />

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-900 group-hover:text-emerald-700 dark:text-white dark:group-hover:text-emerald-400">
                                {{ item.title }}
                            </p>
                            <p class="mt-0.5 flex flex-wrap items-center gap-x-2 text-xs text-slate-500 dark:text-slate-400">
                                <span>{{ item.code }}</span>
                                <span class="text-slate-300 dark:text-slate-600">·</span>
                                <span>{{ item.requester.name }}</span>
                                <span class="text-slate-300 dark:text-slate-600">·</span>
                                <span>{{ formatRelative(item.submitted_at) }}</span>
                            </p>
                        </div>

                        <div class="hidden text-right sm:block">
                            <p class="text-sm font-semibold tabular-nums text-slate-900 dark:text-white">
                                {{ formatMoney(item.amount) }}
                            </p>
                            <p class="mt-0.5 text-[11px] text-slate-400">{{ item.category.label }}</p>
                        </div>

                        <Icon
                            name="chevronRight"
                            size="h-4 w-4"
                            class="text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-emerald-500 dark:text-slate-600"
                        />
                    </Link>
                </div>

                <EmptyState
                    v-else
                    icon="check"
                    title="Очередь пуста"
                    description="Все заявки, где вы согласующий, уже обработаны."
                />
            </section>

            <section
                class="rounded-2xl border border-slate-200/80 bg-white shadow-[0_1px_2px_rgba(15,23,42,0.04)] dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Последние события</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Аудит по доступным вам заявкам</p>
                </div>

                <ol class="space-y-4 px-5 py-4">
                    <li v-for="event in recent" :key="event.id" class="flex gap-3">
                        <span class="relative mt-1.5 flex h-2 w-2 shrink-0">
                            <span :class="eventTones[event.action.value] ?? eventTones.created" class="h-2 w-2 rounded-full" />
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-slate-700 dark:text-slate-200">
                                <span class="font-medium">{{ event.action.label }}</span>
                                <span class="mx-1 text-slate-300 dark:text-slate-600">·</span>
                                <Link
                                    :href="`/requests/${event.request.id}`"
                                    class="font-medium text-emerald-700 hover:underline dark:text-emerald-400"
                                >
                                    {{ event.request.code }}
                                </Link>
                            </p>
                            <p class="mt-0.5 truncate text-xs text-slate-500 dark:text-slate-400">
                                {{ event.actor ?? 'Система' }} · {{ formatRelative(event.created_at) }}
                            </p>
                        </div>
                    </li>

                    <li v-if="!recent.length" class="py-6 text-center text-sm text-slate-400">
                        Пока нет событий
                    </li>
                </ol>
            </section>
        </div>
    </AppLayout>
</template>
