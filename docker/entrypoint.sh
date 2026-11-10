#!/bin/sh
set -e

cd /app

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

if [ ! -f .env ]; then
    cp .env.example .env
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

if [ "$APP_ENV" != "testing" ] && [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
    php artisan migrate --seed --force
fi

php artisan optimize:clear >/dev/null 2>&1 || true

exec "$@"
