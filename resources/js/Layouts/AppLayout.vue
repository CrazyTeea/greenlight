<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AppLogo from '../Components/AppLogo.vue';
import Icon from '../Components/Icon.vue';
import ToastStack from '../Components/ToastStack.vue';
import UserAvatar from '../Components/UserAvatar.vue';
import { useTheme } from '../Composables/useTheme';

defineProps({
    title: { type: String, default: null },
});

const page = usePage();
const { isDark, toggle } = useTheme();

const user = computed(() => page.props.auth.user);
const currentUrl = computed(() => page.url.split('?')[0]);
const mobileOpen = ref(false);

const navigation = [
    { label: 'Обзор', href: '/', icon: 'grid' },
    { label: 'Заявки', href: '/requests', icon: 'list' },
    { label: 'Новая заявка', href: '/requests/create', icon: 'plus' },
];

const section = computed(() => {
    if (currentUrl.value === '/') {
        return 'Обзор';
    }

    if (currentUrl.value.startsWith('/requests/create')) {
        return 'Новая заявка';
    }

    return 'Заявки';
});

function isActive(href) {
    if (href === '/') {
        return currentUrl.value === '/';
    }

    if (href === '/requests') {
        return currentUrl.value.startsWith('/requests') && !currentUrl.value.startsWith('/requests/create');
    }

    return currentUrl.value.startsWith(href);
}

function logout() {
    router.post('/logout');
}

watch(currentUrl, () => {
    mobileOpen.value = false;
});
</script>

<template>
    <div class="min-h-full">
        <aside
            class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-slate-200/80 bg-white lg:flex dark:border-slate-800 dark:bg-slate-900"
        >
            <div class="flex h-16 items-center justify-between px-5">
                <Link href="/">
                    <AppLogo />
                </Link>

                <span
                    class="rounded-md bg-amber-50 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-700 ring-1 ring-inset ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/20"
                >
                    demo
                </span>
            </div>

            <nav class="flex-1 space-y-1 px-3 py-4">
                <Link
                    v-for="item in navigation"
                    :key="item.href"
                    :href="item.href"
                    class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition"
                    :class="
                        isActive(item.href)
                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white'
                    "
                >
                    <Icon
                        :name="item.icon"
                        size="h-5 w-5"
                        :class="isActive(item.href) ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400'"
                    />
                    {{ item.label }}
                </Link>
            </nav>

            <div class="px-3 pb-3">
                <div class="rounded-xl border border-slate-200/80 bg-slate-50/80 p-3.5 dark:border-slate-800 dark:bg-slate-800/40">
                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-200">
                        <Icon name="shield" size="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                        Правила согласования
                    </div>
                    <p class="mt-1.5 text-xs leading-5 text-slate-500 dark:text-slate-400">
                        До 100 000 ₽ решает руководитель, крупные суммы уходят финдиру. Свою заявку согласовать нельзя.
                    </p>
                </div>
            </div>

            <div class="border-t border-slate-200/80 p-3 dark:border-slate-800">
                <div class="flex items-center gap-3 rounded-xl px-2 py-2">
                    <UserAvatar :person="user" />

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-slate-900 dark:text-white">{{ user.name }}</p>
                        <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ user.role.label }}</p>
                    </div>

                    <button
                        type="button"
                        class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                        title="Выйти"
                        @click="logout"
                    >
                        <Icon name="logout" size="h-4 w-4" />
                    </button>
                </div>
            </div>
        </aside>

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="-translate-x-full"
            leave-active-class="transition duration-150 ease-in"
            leave-to-class="-translate-x-full"
        >
            <div v-if="mobileOpen" class="fixed inset-0 z-50 lg:hidden">
                <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm" @click="mobileOpen = false" />

                <aside class="relative flex h-full w-72 flex-col border-r border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex h-16 items-center justify-between px-5">
                        <AppLogo />
                        <button
                            type="button"
                            class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800"
                            @click="mobileOpen = false"
                        >
                            <Icon name="close" size="h-5 w-5" />
                        </button>
                    </div>

                    <nav class="flex-1 space-y-1 px-3 py-4">
                        <Link
                            v-for="item in navigation"
                            :key="item.href"
                            :href="item.href"
                            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium"
                            :class="
                                isActive(item.href)
                                    ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'
                                    : 'text-slate-600 dark:text-slate-400'
                            "
                        >
                            <Icon :name="item.icon" size="h-5 w-5" />
                            {{ item.label }}
                        </Link>
                    </nav>

                    <div class="border-t border-slate-200 p-4 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <UserAvatar :person="user" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-900 dark:text-white">{{ user.name }}</p>
                                <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ user.role.label }}</p>
                            </div>
                            <button type="button" class="p-1.5 text-slate-400" @click="logout">
                                <Icon name="logout" size="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </aside>
            </div>
        </Transition>

        <div class="lg:pl-64">
            <header
                class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-slate-200/80 bg-slate-50/80 px-4 backdrop-blur sm:px-6 lg:px-8 dark:border-slate-800 dark:bg-slate-950/80"
            >
                <button
                    type="button"
                    class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden dark:text-slate-400 dark:hover:bg-slate-800"
                    @click="mobileOpen = true"
                >
                    <Icon name="menu" size="h-5 w-5" />
                </button>

                <div class="flex items-center gap-2 text-sm">
                    <span class="text-slate-400 dark:text-slate-500">Рабочее пространство</span>
                    <Icon name="chevronRight" size="h-3.5 w-3.5 text-slate-300 dark:text-slate-600" />
                    <span class="font-medium text-slate-700 dark:text-slate-200">{{ section }}</span>
                </div>

                <div class="ml-auto flex items-center gap-2">
                    <span class="hidden text-xs text-slate-400 sm:block dark:text-slate-500">
                        {{ user.department }}
                    </span>

                    <button
                        type="button"
                        class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white"
                        :title="isDark ? 'Светлая тема' : 'Тёмная тема'"
                        @click="toggle"
                    >
                        <Icon :name="isDark ? 'sun' : 'moon'" size="h-5 w-5" />
                    </button>
                </div>
            </header>

            <main class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
                <slot />
            </main>
        </div>

        <ToastStack />
    </div>
</template>
