#!/bin/sh
#
# Entrypoint runtime e-Surat. Gagal cepat sebelum melayani trafik bila
# konfigurasi company tidak valid atau storage host tidak bisa ditulis.
set -eu

if [ "${APP_ENV:-production}" = "production" ] && [ "${APP_DEBUG:-false}" = "true" ]; then
    echo "FATAL: APP_DEBUG=true tidak boleh dipakai dengan APP_ENV=production." >&2
    exit 1
fi

: "${APP_KEY:?APP_KEY wajib diisi (php artisan key:generate --show)}"
: "${COMPANY_KEY:?COMPANY_KEY wajib diisi}"

mkdir -p \
    storage/app/private \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

for dir in storage bootstrap/cache; do
    if [ ! -w "$dir" ]; then
        echo "FATAL: $dir tidak writable oleh UID $(id -u):$(id -g)." >&2
        echo "Jalankan di host: sudo chown -R 1000:1000 <DATA_PATH>/storage" >&2
        exit 1
    fi
done

php artisan company:validate-config
php artisan config:cache

exec "$@"
