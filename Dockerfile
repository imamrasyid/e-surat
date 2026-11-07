# syntax=docker/dockerfile:1.7

# Image dibangun SEKALI per tag rilis lalu dipakai semua perusahaan.
# Base image di-pin by digest; perbarui digest lewat `docker buildx imagetools inspect`.
ARG NODE_IMAGE=node:26-alpine@sha256:8996bb5821d62a8fd9bb27c1cb07462f3fdce6e47d640c6a881ead0b064ce216
ARG COMPOSER_IMAGE=composer:2.11.1@sha256:3d1eb9437516b0ba88c46a0bb05826451a1ab178d29adacb5ded26dad0899ae4
ARG RUNTIME_IMAGE=dunglas/frankenphp:1.13.5-php8.4-alpine@sha256:2a0633266ba2c821ad3cabb88caa30792a81e2d6c3c3a0720906114c660ede08

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
