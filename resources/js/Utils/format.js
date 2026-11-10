const money = new Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency: 'RUB',
    maximumFractionDigits: 0,
});

const date = new Intl.DateTimeFormat('ru-RU', { day: 'numeric', month: 'short' });
const dateTime = new Intl.DateTimeFormat('ru-RU', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
});
const relative = new Intl.RelativeTimeFormat('ru-RU', { numeric: 'auto' });

export function formatMoney(value) {
    return money.format(Number(value ?? 0));
}

export function formatDate(value) {
    return value ? date.format(new Date(value)) : '-';
}

export function formatDateTime(value) {
    return value ? dateTime.format(new Date(value)) : '-';
}

export function formatRelative(value) {
    if (!value) {
        return '-';
    }

    const seconds = (new Date(value).getTime() - Date.now()) / 1000;

    const steps = [
        ['year', 31536000],
        ['month', 2592000],
        ['day', 86400],
        ['hour', 3600],
        ['minute', 60],
    ];

    for (const [unit, size] of steps) {
        if (Math.abs(seconds) >= size) {
            return relative.format(Math.round(seconds / size), unit);
        }
    }

    return 'только что';
}

export function plural(count, one, few, many) {
    const mod10 = count % 10;
    const mod100 = count % 100;

    if (mod10 === 1 && mod100 !== 11) {
        return one;
    }

    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) {
        return few;
    }

    return many;
}

const avatarTones = [
    'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
    'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
    'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
    'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300',
    'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
];

export function avatarTone(name = '') {
    let hash = 0;

    for (const char of name) {
        hash = (hash * 31 + char.charCodeAt(0)) >>> 0;
    }

    return avatarTones[hash % avatarTones.length];
}
