<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Icon from './Icon.vue';

const page = usePage();
const toasts = ref([]);
let sequence = 0;

function push(type, message) {
    if (!message) {
        return;
    }

    const id = ++sequence;

    toasts.value.push({ id, type, message });

    window.setTimeout(() => {
        toasts.value = toasts.value.filter((toast) => toast.id !== id);
    }, 4500);
}

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            push('success', flash.success);
        }

        if (flash?.error) {
            push('error', flash.error);
        }
    },
    { immediate: true },
);
</script>

<template>
    <div class="pointer-events-none fixed inset-x-0 top-4 z-[60] flex flex-col items-center gap-2 px-4 sm:items-end sm:px-6">
        <TransitionGroup
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="-translate-y-2 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-xl border bg-white p-3.5 shadow-lg dark:bg-slate-900"
                :class="
                    toast.type === 'success'
                        ? 'border-emerald-200 dark:border-emerald-500/30'
                        : 'border-rose-200 dark:border-rose-500/30'
                "
            >
                <span
                    class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full"
                    :class="
                        toast.type === 'success'
                            ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400'
                            : 'bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400'
                    "
                >
                    <Icon :name="toast.type === 'success' ? 'check' : 'alert'" size="h-3.5 w-3.5" />
                </span>

                <p class="text-sm leading-5 text-slate-700 dark:text-slate-200">{{ toast.message }}</p>

                <button
                    type="button"
                    class="ml-auto rounded p-1 text-slate-400 transition hover:text-slate-600 dark:hover:text-slate-200"
                    @click="toasts = toasts.filter((item) => item.id !== toast.id)"
                >
                    <Icon name="close" size="h-3.5 w-3.5" />
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>
