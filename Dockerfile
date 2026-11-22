# syntax=docker/dockerfile:1.7

# Image dibangun SEKALI per tag rilis lalu dipakai semua perusahaan.
# Base image di-pin by digest; perbarui digest lewat `docker buildx imagetools inspect`.
ARG NODE_IMAGE=node:26-alpine@sha256:6bd9ab43c0ec0190ca7b2d193906cb8b7edf6e7f4afa43ddeccd6452710bffb5
ARG COMPOSER_IMAGE=composer:2.11.2@sha256:50ea4d33fdcde1adb0f045547295faa1548b33872e5f789dae5b02ce2fb5de93
ARG RUNTIME_IMAGE=dunglas/frankenphp:1.14.0-php8.4.28-alpine@sha256:f81693fa548684eb12964eeab78d86325b75c5cd91289c09ffc4dd4404a641e5

# ---------------------------------------------------------------------------
# 1. Aset frontend (Node 26)
# ---------------------------------------------------------------------------
FROM ${NODE_IMAGE} AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build

# ---------------------------------------------------------------------------
# 2. Dependensi PHP (Composer 2.11.1)
# ---------------------------------------------------------------------------
FROM ${COMPOSER_IMAGE} AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --no-autoloader \
    --no-scripts

# Gagalkan build bila composer.lock memuat advisory keamanan untuk dependency produksi.
RUN composer audit --no-dev --format=plain

COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative

# ---------------------------------------------------------------------------
# 3. Runtime FrankenPHP (mode klasik, non-root, port 8080)
# ---------------------------------------------------------------------------
FROM ${RUNTIME_IMAGE} AS runtime

ENV APP_ENV=production \
    APP_DEBUG=false \
    XDG_DATA_HOME=/tmp/caddy/data \
    XDG_CONFIG_HOME=/tmp/caddy/config

RUN install-php-extensions pdo_mysql pdo_pgsql \
    && printf '%s\n' \
        'opcache.validate_timestamps=0' \
        'opcache.jit=off' \
        'upload_max_filesize=20M' \
        'post_max_size=25M' \
        'memory_limit=256M' \
        'max_execution_time=60' \
        > "$PHP_INI_DIR/conf.d/zz-esurat-production.ini"

WORKDIR /app

COPY --from=vendor --chown=1000:1000 /app /app
COPY --from=assets --chown=1000:1000 /app/public/build /app/public/build
COPY --chown=1000:1000 docker/Caddyfile /etc/frankenphp/Caddyfile
COPY --chown=1000:1000 docker/entrypoint.sh /usr/local/bin/esurat-entrypoint

RUN chmod +x /usr/local/bin/esurat-entrypoint \
    && php artisan package:discover --ansi \
    && mkdir -p \
        storage/app/private \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R 1000:1000 storage bootstrap/cache

# Storage selalu bind-mount dari host; container tidak menyimpan lampiran.
VOLUME ["/app/storage"]

USER 1000:1000

EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD php -r "exit(@file_get_contents('http://127.0.0.1:8080/up') === false ? 1 : 0);"

ENTRYPOINT ["esurat-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
