<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import Icon from '../../Components/Icon.vue';
import { formatMoney } from '../../Utils/format';

const props = defineProps({
    categories: { type: Array, required: true },
    cfoThreshold: { type: Number, required: true },
});

const form = useForm({
    title: '',
    description: '',
    amount: '',
    category: 'it',
    send_now: false,
});

const categoryIcons = {
    office: 'building',
    it: 'grid',
    services: 'inbox',
    marketing: 'chart',
    hr: 'user',
};

const amount = computed(() => Number(form.amount) || 0);

const steps = computed(() => {
    const chain = [{ role: 'Руководитель отдела', note: 'Проверяет необходимость и смету' }];

    if (amount.value >= props.cfoThreshold) {
        chain.push({
            role: 'Финансовый директор',
            note: `Подключается к суммам от ${formatMoney(props.cfoThreshold)}`,
        });
    }

    return chain;
});

function save(shouldSendNow) {
    form.send_now = shouldSendNow;
    form.post('/requests');
}
</script>

<template>
    <Head title="Новая заявка" />

    <AppLayout>
        <Link
            href="/requests"
            class="inline-flex items-center gap-1.5 text-sm text-slate-500 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white"
        >
            <Icon name="chevronLeft" size="h-4 w-4" />
            К списку заявок
        </Link>

        <div class="mt-4">
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">Новая заявка</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Опишите покупку и сумму, маршрут согласования соберётся сам.
            </p>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_340px] lg:items-start">
            <form
                class="rounded-2xl border border-slate-200/80 bg-white shadow-[0_1px_2px_rgba(15,23,42,0.04)] dark:border-slate-800 dark:bg-slate-900"
                @submit.prevent="save(true)"
            >
                <div class="space-y-6 p-5 sm:p-6">
                    <div>
                        <label for="title" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">
                            Название
                        </label>
                        <input
                            id="title"
                            v-model="form.title"
                            type="text"
                            placeholder="Например, замена ноутбуков в отделе продаж"
                            class="w-full rounded-xl border bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/15 dark:bg-slate-950 dark:text-white"
                            :class="
                                form.errors.title
                                    ? 'border-rose-300 focus:border-rose-400 dark:border-rose-500/40'
                                    : 'border-slate-200 focus:border-emerald-400 dark:border-slate-800'
                            "
                        />
                        <p v-if="form.errors.title" class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">
                            {{ form.errors.title }}
                        </p>
                    </div>

                    <div class="sm:max-w-xs">
                        <label for="amount" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">
                            Сумма
                        </label>
                        <div class="relative">
                            <input
                                id="amount"
                                v-model="form.amount"
                                type="number"
                                min="1"
                                step="any"
                                placeholder="0"
                                class="w-full appearance-none rounded-xl border bg-white py-2.5 pl-3.5 pr-9 text-sm tabular-nums text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/15 dark:bg-slate-950 dark:text-white"
                                :class="
                                    form.errors.amount
                                        ? 'border-rose-300 focus:border-rose-400 dark:border-rose-500/40'
                                        : 'border-slate-200 focus:border-emerald-400 dark:border-slate-800'
                                "
                            />
                            <span class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400">
                                ₽
                            </span>
                        </div>
                        <p v-if="form.errors.amount" class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">
                            {{ form.errors.amount }}
                        </p>
                    </div>

                    <div>
                        <span class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Категория</span>

                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            <button
                                v-for="category in categories"
                                :key="category.value"
                                type="button"
                                class="flex items-center gap-2 rounded-xl border px-3 py-2.5 text-left text-xs font-medium transition"
                                :class="
                                    form.category === category.value
                                        ? 'border-emerald-300 bg-emerald-50/60 text-emerald-800 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-300'
                                        : 'border-slate-200 text-slate-600 hover:border-slate-300 dark:border-slate-800 dark:text-slate-400 dark:hover:border-slate-700'
                                "
                                @click="form.category = category.value"
                            >
                                <Icon :name="categoryIcons[category.value] ?? 'list'" size="h-4 w-4" />
                                {{ category.label }}
                            </button>
                        </div>

                        <p v-if="form.errors.category" class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">
                            {{ form.errors.category }}
                        </p>
                    </div>

                    <div>
                        <label for="description" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">
                            Описание
                            <span class="font-normal text-slate-400">(необязательно)</span>
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="5"
                            placeholder="Зачем нужна покупка, что уже смотрели, есть ли альтернативы"
                            class="w-full resize-none rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/15 dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                        />
                        <p v-if="form.errors.description" class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">
                            {{ form.errors.description }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-2 border-t border-slate-100 px-5 py-4 sm:flex-row sm:justify-end dark:border-slate-800">
                    <button
                        type="button"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:opacity-60 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                        :disabled="form.processing"
                        @click="save(false)"
                    >
                        Сохранить черновик
                    </button>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm shadow-emerald-600/20 transition hover:bg-emerald-700 disabled:opacity-60"
                        :disabled="form.processing"
                    >
                        <svg v-if="form.processing" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                        </svg>
                        <Icon v-else name="check" size="h-4 w-4" />
                        Отправить на согласование
                    </button>
                </div>
            </form>

            <aside class="space-y-4 lg:sticky lg:top-24">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-[0_1px_2px_rgba(15,23,42,0.04)] dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Маршрут согласования</h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        {{ amount ? formatMoney(amount) : 'Сумма не указана' }}
                    </p>

                    <ol class="mt-4 space-y-4">
                        <li v-for="(step, index) in steps" :key="step.role" class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <span
                                    class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-50 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/20"
                                >
                                    {{ index + 1 }}
                                </span>
                                <span
                                    v-if="index < steps.length - 1"
                                    class="mt-1 h-8 w-px bg-slate-200 dark:bg-slate-700"
                                />
                            </div>

                            <div class="pb-1">
                                <p class="text-sm font-medium text-slate-900 dark:text-white">{{ step.role }}</p>
                                <p class="mt-0.5 text-xs leading-5 text-slate-500 dark:text-slate-400">{{ step.note }}</p>
                            </div>
                        </li>
                    </ol>
                </div>

                <div class="rounded-2xl border border-amber-200/70 bg-amber-50/60 p-4 dark:border-amber-500/20 dark:bg-amber-500/5">
                    <div class="flex items-start gap-2.5">
                        <Icon name="alert" size="h-4 w-4 shrink-0 text-amber-600 dark:text-amber-400" />
                        <p class="text-xs leading-5 text-amber-800 dark:text-amber-200">
                            Согласовать собственную заявку нельзя. Если вы руководитель, заявка уйдёт
                            следующему в цепочке.
                        </p>
                    </div>
                </div>
            </aside>
        </div>
    </AppLayout>
</template>
