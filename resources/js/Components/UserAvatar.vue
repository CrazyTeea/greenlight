<script setup>
import { computed } from 'vue';
import { avatarTone } from '../Utils/format';

const props = defineProps({
    person: { type: Object, default: null },
    size: { type: String, default: 'h-9 w-9' },
    text: { type: String, default: 'text-xs' },
});

const initials = computed(() => props.person?.initials || '?');
const tone = computed(() => avatarTone(props.person?.name ?? ''));
</script>

<template>
    <span
        :class="[size, text, tone]"
        class="inline-flex shrink-0 items-center justify-center rounded-full font-semibold ring-1 ring-black/5 dark:ring-white/10"
    >
        {{ initials }}
    </span>
</template>
