<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLogo from '../../Components/AppLogo.vue';
import Icon from '../../Components/Icon.vue';
import UserAvatar from '../../Components/UserAvatar.vue';

defineProps({
    accounts: { type: Array, required: true },
});

const form = useForm({ user_id: null });
const pendingId = ref(null);

const roleTones = {
    employee: 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
    manager: 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300',
    cfo: 'bg-violet-50 text-violet-700 dark:bg-violet-500/10 dark:text-violet-300',
};

function login(account) {
    pendingId.value = account.id;
    form.user_id = account.id;

    form.post('/login', {
        onFinish: () => {
            pendingId.value = null;
        },
    });
}
</script>

<template>
    <Head title="Вход" />

    <div class="grid min-h-screen lg:grid-cols-[1.05fr_1fr]">
        <div class="relative hidden overflow-hidden bg-slate-950 p-12 lg:flex lg:flex-col">
            <div class="absolute -left-32 top-1/4 h-96 w-96 rounded-full bg-emerald-500/20 blur-3xl" />
            <div class="absolute -right-24 bottom-0 h-80 w-80 rounded-full bg-teal-400/10 blur-3xl" />

            <div class="relative">
                <AppLogo inverted />
            </div>

            <div class="relative mt-auto max-w-lg">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-400">Approval workflow</p>

                <h1 class="mt-4 text-4xl font-semibold leading-tight tracking-tight text-white">
                    Заявки на закупку без переписок в чатах
                </h1>

                <p class="mt-4 text-base leading-7 text-slate-400">
                    Маршрут согласования собирается по сумме, решения фиксируются в истории, а автор видит,
                    у кого заявка лежит прямо сейчас.
                </p>

                <ul class="mt-8 space-y-3">
                    <li v-for="item in [
                        'Правило четырёх глаз: свою заявку согласовать нельзя',
                        'Крупные суммы автоматически уходят финдиру',
                        'Каждое действие попадает в аудит-лог',
                    ]" :key="item" class="flex items-start gap-3 text-sm text-slate-300">
                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-400">
                            <Icon name="check" size="h-3 w-3" />
                        </span>
                        {{ item }}
                    </li>
                </ul>
            </div>

            <div class="relative mt-12 flex gap-8 border-t border-white/10 pt-6 text-sm">
                <div>
                    <p class="text-2xl font-semibold text-white">16</p>
                    <p class="text-slate-500">заявок в демо</p>
                </div>
                <div>
                    <p class="text-2xl font-semibold text-white">3</p>
                    <p class="text-slate-500">роли</p>
                </div>
                <div>
                    <p class="text-2xl font-semibold text-white">100%</p>
                    <p class="text-slate-500">покрытие переходов тестами</p>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-center bg-slate-50 px-4 py-12 dark:bg-slate-950 sm:px-8">
            <div class="w-full max-w-md">
                <div class="mb-8 lg:hidden">
                    <AppLogo />
                </div>

                <h2 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">Демо-доступ</h2>
                <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">
                    Выберите, от чьего лица посмотреть систему. Пароль не нужен.
                </p>

                <div class="mt-6 space-y-2">
                    <button
                        v-for="account in accounts"
                        :key="account.id"
                        type="button"
                        class="group flex w-full items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left transition hover:border-emerald-300 hover:bg-emerald-50/40 disabled:opacity-60 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-emerald-500/40 dark:hover:bg-emerald-500/5"
                        :disabled="form.processing"
                        @click="login(account)"
                    >
                        <UserAvatar :person="account" size="h-10 w-10" text="text-sm" />

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-900 dark:text-white">{{ account.name }}</p>
                            <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ account.department }}</p>
                        </div>

                        <span
                            class="rounded-md px-2 py-1 text-[11px] font-medium"
                            :class="roleTones[account.role.value]"
                        >
                            {{ account.role.label }}
                        </span>

                        <span
                            class="flex h-7 w-7 items-center justify-center rounded-full text-slate-300 transition group-hover:bg-emerald-600 group-hover:text-white dark:text-slate-600"
                        >
                            <svg v-if="pendingId === account.id" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                            </svg>
                            <Icon v-else name="arrowRight" size="h-4 w-4" />
                        </span>
                    </button>
                </div>

                <p class="mt-6 flex items-start gap-2 text-xs leading-5 text-slate-400 dark:text-slate-500">
                    <Icon name="shield" size="h-4 w-4 shrink-0" />
                    Демо-стенд. Данные вымышленные и пересоздаются при перезапуске контейнера.
                </p>
            </div>
        </div>
    </div>
</template>
