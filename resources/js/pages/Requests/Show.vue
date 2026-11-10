<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import Icon from '../../Components/Icon.vue';
import Modal from '../../Components/Modal.vue';
import StatusPill from '../../Components/StatusPill.vue';
import UserAvatar from '../../Components/UserAvatar.vue';
import { formatDateTime, formatMoney, formatRelative } from '../../Utils/format';

const props = defineProps({
    request: { type: Object, required: true },
});

const page = usePage();
const decideForm = useForm({
    approve: true,
    comment: '',
    version: props.request.version,
});

const decisionOpen = ref(false);
const decisionMode = ref('approve');
const cancelOpen = ref(false);
const submitting = ref(false);

const isReject = computed(() => decisionMode.value === 'reject');

const decidedSteps = computed(
    () => props.request.approvals.filter((approval) => approval.decision.value !== 'pending').length,
);

const progressText = computed(() => {
    const total = props.request.approvals.length;

    if (!total) {
        return null;
    }

    const pending = props.request.approvals.find((approval) => approval.decision.value === 'pending');

    if (pending) {
        return `Шаг ${pending.step} из ${total}`;
    }

    return decidedSteps.value === total ? `Пройдено ${total} из ${total}` : null;
});

const orderedAudit = computed(() => [...props.request.audit].sort((a, b) => b.id - a.id));

watch(
    () => props.request.version,
    (version) => {
        decideForm.version = version;
    },
);

function openDecision(approve) {
    decisionMode.value = approve ? 'approve' : 'reject';
    decideForm.approve = approve;
    decideForm.comment = '';
    decideForm.clearErrors();
    decisionOpen.value = true;
}

function submitDecision() {
    if (isReject.value && !decideForm.comment.trim()) {
        decideForm.setError('comment', 'Опишите причину отказа, автор увидит комментарий.');

        return;
    }

    decideForm.post(`/requests/${props.request.id}/decide`, {
        preserveScroll: true,
        onSuccess: () => {
            decisionOpen.value = false;
        },
    });
}

function submitRequest() {
    router.post(`/requests/${props.request.id}/submit`, {}, { preserveScroll: true });
}

function cancelRequest() {
    submitting.value = true;

    router.post(
        `/requests/${props.request.id}/cancel`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                submitting.value = false;
                cancelOpen.value = false;
            },
        },
    );
}

const decisionTone = {
    approved: 'bg-emerald-500 text-white ring-emerald-500',
    rejected: 'bg-rose-500 text-white ring-rose-500',
    pending: 'bg-white text-amber-600 ring-amber-300 dark:bg-slate-900 dark:text-amber-400 dark:ring-amber-500/40',
};
</script>

<template>
    <Head :title="request.code" />

    <AppLayout>
        <Link
            href="/requests"
            class="inline-flex items-center gap-1.5 text-sm text-slate-500 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white"
        >
            <Icon name="chevronLeft" size="h-4 w-4" />
            Все заявки
        </Link>

        <div class="mt-4 flex flex-wrap items-center gap-3">
            <span class="font-mono text-xs text-slate-400 dark:text-slate-500">{{ request.code }}</span>
            <StatusPill :status="request.status" />
            <span v-if="progressText" class="text-xs text-slate-400">
                {{ progressText }}
            </span>
        </div>

        <div class="mt-2 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <h1 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">
                    {{ request.title }}
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ request.category.label }} · создана {{ formatRelative(request.created_at) }}
                </p>
            </div>

            <p class="text-2xl font-semibold tabular-nums tracking-tight text-slate-900 dark:text-white">
                {{ formatMoney(request.amount) }}
            </p>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_320px] lg:items-start">
            <div class="space-y-6">
                <section
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-[0_1px_2px_rgba(15,23,42,0.04)] dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Описание</h2>
                    <p v-if="request.description" class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300">
                        {{ request.description }}
                    </p>
                    <p v-else class="mt-2 text-sm text-slate-400">Автор не добавил описание.</p>

                    <dl class="mt-5 grid gap-4 border-t border-slate-100 pt-5 text-sm sm:grid-cols-2 dark:border-slate-800">
                        <div class="flex items-start gap-3">
                            <UserAvatar :person="request.requester" size="h-9 w-9" />
                            <div>
                                <dt class="text-xs text-slate-400">Автор</dt>
                                <dd class="font-medium text-slate-900 dark:text-white">{{ request.requester.name }}</dd>
                                <dd class="text-xs text-slate-500 dark:text-slate-400">{{ request.requester.department }}</dd>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-xs text-slate-400">Создана</dt>
                                <dd class="text-slate-700 dark:text-slate-200">{{ formatDateTime(request.created_at) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-xs text-slate-400">Отправлена</dt>
                                <dd class="text-slate-700 dark:text-slate-200">{{ formatDateTime(request.submitted_at) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-xs text-slate-400">Решение</dt>
                                <dd class="text-slate-700 dark:text-slate-200">{{ formatDateTime(request.decided_at) }}</dd>
                            </div>
                        </div>
                    </dl>
                </section>

                <section
                    class="rounded-2xl border border-slate-200/80 bg-white shadow-[0_1px_2px_rgba(15,23,42,0.04)] dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Маршрут согласования</h2>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                            Цепочка собрана по сумме заявки
                        </p>
                    </div>

                    <ol v-if="request.approvals.length" class="space-y-0 px-5 py-5">
                        <li v-for="(approval, index) in request.approvals" :key="approval.id" class="flex gap-4">
                            <div class="flex flex-col items-center">
                                <span
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-semibold ring-1 ring-inset"
                                    :class="decisionTone[approval.decision.value]"
                                >
                                    <Icon v-if="approval.decision.value === 'approved'" name="check" size="h-4 w-4" />
                                    <Icon v-else-if="approval.decision.value === 'rejected'" name="close" size="h-4 w-4" />
                                    <template v-else>{{ approval.step }}</template>
                                </span>

                                <span
                                    v-if="index < request.approvals.length - 1"
                                    class="my-1 w-px flex-1"
                                    :class="approval.decision.value === 'approved' ? 'bg-emerald-200 dark:bg-emerald-500/30' : 'bg-slate-200 dark:bg-slate-700'"
                                />
                            </div>

                            <div class="min-w-0 flex-1 pb-6">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">
                                        Шаг {{ approval.step }} · {{ approval.approver.name }}
                                    </p>
                                    <span
                                        class="rounded-md px-1.5 py-0.5 text-[11px] font-medium"
                                        :class="{
                                            'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300':
                                                approval.decision.value === 'approved',
                                            'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300':
                                                approval.decision.value === 'rejected',
                                            'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300':
                                                approval.decision.value === 'pending',
                                        }"
                                    >
                                        {{ approval.decision.label }}
                                    </span>
                                </div>

                                <p class="mt-0.5 text-xs text-slate-400">
                                    {{ approval.approver.department }}
                                    <template v-if="approval.decided_at"> · {{ formatDateTime(approval.decided_at) }}</template>
                                </p>

                                <p
                                    v-if="approval.comment"
                                    class="mt-2 rounded-lg border-l-2 border-slate-200 bg-slate-50 px-3 py-2 text-sm leading-6 text-slate-600 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-300"
                                >
                                    {{ approval.comment }}
                                </p>
                            </div>
                        </li>
                    </ol>

                    <p v-else class="px-5 py-6 text-sm text-slate-400">
                        Заявка ещё не отправлена. Маршрут появится после отправки.
                    </p>
                </section>

                <section
                    class="rounded-2xl border border-slate-200/80 bg-white shadow-[0_1px_2px_rgba(15,23,42,0.04)] dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">История действий</h2>
                    </div>

                    <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                        <li v-for="event in orderedAudit" :key="event.id" class="flex items-start gap-3 px-5 py-3.5">
                            <span
                                class="mt-1.5 h-2 w-2 shrink-0 rounded-full"
                                :class="{
                                    'bg-emerald-500': event.action.value === 'approved',
                                    'bg-rose-500': event.action.value === 'rejected',
                                    'bg-sky-400': event.action.value === 'submitted',
                                    'bg-slate-300 dark:bg-slate-600': ['created', 'cancelled'].includes(event.action.value),
                                }"
                            />

                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-slate-700 dark:text-slate-200">
                                    <span class="font-medium">{{ event.action.label }}</span>
                                    <span class="text-slate-400"> · {{ event.actor?.name ?? 'Система' }}</span>
                                </p>
                                <p v-if="event.comment" class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                    {{ event.comment }}
                                </p>
                            </div>

                            <span class="shrink-0 text-xs text-slate-400">{{ formatRelative(event.created_at) }}</span>
                        </li>
                    </ul>
                </section>
            </div>

            <aside class="order-first space-y-4 lg:order-none lg:sticky lg:top-24">
                <div
                    v-if="request.can.decide"
                    class="rounded-2xl border border-emerald-200 bg-white p-5 shadow-[0_1px_2px_rgba(15,23,42,0.04)] dark:border-emerald-500/30 dark:bg-slate-900"
                >
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Нужно ваше решение</h2>
                    <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                        {{ page.props.auth.user.name }}, заявка ждёт ответа. Комментарий увидят автор и остальные согласующие.
                    </p>

                    <button
                        type="button"
                        class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm shadow-emerald-600/20 transition hover:bg-emerald-700"
                        @click="openDecision(true)"
                    >
                        <Icon name="check" size="h-4 w-4" />
                        Согласовать
                    </button>

                    <button
                        type="button"
                        class="mt-2 flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                        @click="openDecision(false)"
                    >
                        <Icon name="close" size="h-4 w-4" />
                        Отклонить
                    </button>
                </div>

                <div
                    v-else-if="request.can.submit"
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-[0_1px_2px_rgba(15,23,42,0.04)] dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Черновик</h2>
                    <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                        Заявка ещё не отправлена. После отправки редактирование недоступно, можно только отменить.
                    </p>

                    <button
                        type="button"
                        class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700"
                        @click="submitRequest"
                    >
                        <Icon name="arrowRight" size="h-4 w-4" />
                        Отправить на согласование
                    </button>
                </div>

                <div
                    v-else
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-[0_1px_2px_rgba(15,23,42,0.04)] dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Текущий статус</h2>
                    <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                        {{
                            request.status.value === 'approved'
                                ? 'Заявка согласована всеми участниками маршрута.'
                                : request.status.value === 'rejected'
                                  ? 'Заявка отклонена. Причину смотрите в комментарии согласующего.'
                                  : request.status.value === 'cancelled'
                                    ? 'Заявка отменена автором.'
                                    : 'Ожидает решения согласующего.'
                        }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-[0_1px_2px_rgba(15,23,42,0.04)] dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Согласующие</h2>

                    <div v-if="request.current_approver" class="mt-3 flex items-center gap-3">
                        <UserAvatar :person="request.current_approver" size="h-8 w-8" />
                        <div>
                            <p class="text-sm font-medium text-slate-900 dark:text-white">
                                {{ request.current_approver.name }}
                            </p>
                            <p class="text-xs text-slate-400">Ждёт решения</p>
                        </div>
                    </div>

                    <p v-else class="mt-2 text-xs text-slate-400">Маршрут завершён.</p>

                    <div v-if="request.can.cancel" class="mt-4 border-t border-slate-100 pt-4 dark:border-slate-800">
                        <button
                            type="button"
                            class="text-xs font-medium text-rose-600 transition hover:text-rose-700 dark:text-rose-400"
                            @click="cancelOpen = true"
                        >
                            Отменить заявку
                        </button>
                    </div>
                </div>
            </aside>
        </div>

        <Modal
            :show="decisionOpen"
            :title="isReject ? 'Отклонить заявку' : 'Согласовать заявку'"
            :description="isReject ? 'Автор увидит комментарий и сможет доработать заявку.' : 'Решение попадёт в историю и станет доступно участникам.'"
            @close="decisionOpen = false"
        >
            <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">
                Комментарий
                <span v-if="isReject" class="text-rose-500">*</span>
                <span v-else class="font-normal text-slate-400">(необязательно)</span>
            </label>

            <textarea
                v-model="decideForm.comment"
                rows="4"
                :placeholder="isReject ? 'Что не так и что нужно изменить' : 'Пара слов для автора'"
                class="w-full resize-none rounded-xl border bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/15 dark:bg-slate-950 dark:text-white"
                :class="
                    decideForm.errors.comment
                        ? 'border-rose-300 dark:border-rose-500/40'
                        : 'border-slate-200 focus:border-emerald-400 dark:border-slate-800'
                "
            />

            <p v-if="decideForm.errors.comment" class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">
                {{ decideForm.errors.comment }}
            </p>

            <template #footer>
                <button
                    type="button"
                    class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                    @click="decisionOpen = false"
                >
                    Отмена
                </button>

                <button
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium text-white transition disabled:opacity-60"
                    :class="isReject ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-600 hover:bg-emerald-700'"
                    :disabled="decideForm.processing"
                    @click="submitDecision"
                >
                    <svg v-if="decideForm.processing" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                    </svg>
                    {{ isReject ? 'Отклонить' : 'Согласовать' }}
                </button>
            </template>
        </Modal>

        <Modal
            :show="cancelOpen"
            title="Отменить заявку?"
            description="Отмена необратима: заявка останется в истории со статусом «Отменена»."
            @close="cancelOpen = false"
        >
            <p class="text-sm text-slate-600 dark:text-slate-300">
                {{ request.code }} · {{ request.title }}
            </p>

            <template #footer>
                <button
                    type="button"
                    class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                    @click="cancelOpen = false"
                >
                    Оставить
                </button>

                <button
                    type="button"
                    class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-rose-700 disabled:opacity-60"
                    :disabled="submitting"
                    @click="cancelRequest"
                >
                    Отменить заявку
                </button>
            </template>
        </Modal>
    </AppLayout>
</template>
