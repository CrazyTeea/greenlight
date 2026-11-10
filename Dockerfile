# --- зависимости -----------------------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY composer.json composer.lock ./
RUN composer install \
    --no-scripts \
    --no-autoloader \
    --no-interaction \
    --prefer-dist \
    --no-progress

COPY . .

RUN composer dump-autoload --optimize --no-scripts


# --- рантайм ----------------------------------------------------------------------
FROM php:8.4-cli

WORKDIR /app

COPY --from=vendor /app /app
COPY docker/entrypoint.sh /usr/local/bin/entrypoint

RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 8000

ENTRYPOINT ["entrypoint"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
