<script setup>
import { onMounted, onUnmounted } from 'vue';
import Icon from './Icon.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: null },
    description: { type: String, default: null },
});

const emit = defineEmits(['close']);

function onKeydown(event) {
    if (event.key === 'Escape' && props.show) {
        emit('close');
    }
}

onMounted(() => document.addEventListener('keydown', onKeydown));
onUnmounted(() => document.removeEventListener('keydown', onKeydown));
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center">
                <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm" @click="emit('close')" />

                <div
                    class="relative w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 v-if="title" class="text-base font-semibold text-slate-900 dark:text-white">
                                {{ title }}
                            </h2>
                            <p v-if="description" class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                {{ description }}
                            </p>
                        </div>

                        <button
                            type="button"
                            class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                            @click="emit('close')"
                        >
                            <Icon name="close" size="h-4 w-4" />
                        </button>
                    </div>

                    <div class="mt-5">
                        <slot />
                    </div>

                    <div v-if="$slots.footer" class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <slot name="footer" />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
