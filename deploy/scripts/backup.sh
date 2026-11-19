#!/bin/sh
#
# Backup rutin satu environment e-Surat (jalankan di HOST, bukan di container).
# Kredensial dibaca dari .env project (mode 600) supaya cron tidak
# berhenti di prompt password. Idempotent: menjalankan ulang hanya menambah
# direktori stamp baru.
#
# Output: BACKUP_ROOT/<stamp>/{db.sql.gz,db.sql.gz.sha256,env.backup,storage/,image.txt}
#
# Lihat docs/runbook-docker-frankenphp.md §11 untuk uji restore.

set -eu

# Backup memuat seluruh database dan salinan .env (APP_KEY). Tanpa ini host
# memakai umask 022: direktori 755 dan db.sql.gz 644, terbaca semua user.
umask 077

PROJECT_DIR="${PROJECT_DIR:-/srv/esurat/app}"
BACKUP_ROOT="${BACKUP_ROOT:-/srv/esurat/data/backups}"

ENV_FILE="$PROJECT_DIR/.env"

if [ ! -f "$ENV_FILE" ]; then
    echo "FATAL: $ENV_FILE tidak ditemukan." >&2
    exit 1
fi

STAMP="$(date +%Y%m%d-%H%M%S)"
OUT="$BACKUP_ROOT/$STAMP"

# Kegagalan apa pun setelah titik ini membuang direktori setengah jadi, supaya
# retensi dan restore tidak pernah melihat backup yang tampak lengkap.
trap 'rm -rf "$OUT"' EXIT

mkdir -p "$OUT"

log() {
    printf '[%s] %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$1"
}

log "Backup dimulai: $OUT"

# --- Kredensial dari .env (KEY=VALUE, tanpa eksekusi shell) ------------------
# Mengikuti semantik dotenv Laravel untuk bentuk yang dipakai file ini: spasi
# di sekitar `=` diabaikan, awalan `export ` dibuang, kutip pembungkus dilepas,
# komentar inline setelah spasi dibuang, CRLF dan BOM dimaafkan, dan baris
# terakhir tanpa newline tetap terbaca.
# Prioritas: lingkungan (mis. /etc/esurat/<instance>.env) > .env > default.
# shortcut: nilai multi-baris atau escape (\" \n) tidak didukung; kunci DB_*
# tidak memakainya. Naikkan ke parser dotenv penuh bila suatu saat perlu.
# Nilai yang sudah ada di lingkungan dipertahankan (`${VAR-}`, bukan `""`),
# supaya `/etc/esurat/<instance>.env` benar-benar menang atas .env.
DB_HOST="${DB_HOST-}"
DB_PORT="${DB_PORT-}"
DB_DATABASE="${DB_DATABASE-}"
DB_USERNAME="${DB_USERNAME-}"
DB_PASSWORD="${DB_PASSWORD-}"
ESURAT_IMAGE="${ESURAT_IMAGE-}"
DATA_PATH="${DATA_PATH-}"

BOM=$(printf '\357\273\277')
TAB=$(printf '\t')

trim() {
    printf '%s\n' "$1" | sed -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//'
}

while IFS= read -r line || [ -n "$line" ]; do
    case "$line" in *=*) ;; *) continue ;; esac

    key=$(trim "${line%%=*}")
    value=$(trim "${line#*=}")

    key=${key#"$BOM"}
    case "$key" in
        export[[:space:]]*) key=$(trim "${key#export}") ;;
    esac
    [ -z "$key" ] && continue
    case "$key" in '#'*) continue ;; esac

    case "$value" in
        \"*) quote='"' ;;
        \'*) quote="'" ;;
        *) quote="" ;;
    esac
    if [ -n "$quote" ]; then
        value=${value#?}
        value=${value%%"$quote"*}
    else
        case "$value" in
            *' #'*) value=$(trim "${value%%' #'*}") ;;
            *"$TAB#"*) value=$(trim "${value%%"$TAB"#*}") ;;
        esac
    fi

    case "$key" in
        DB_HOST)      DB_HOST="${DB_HOST:-$value}" ;;
        DB_PORT)      DB_PORT="${DB_PORT:-$value}" ;;
        DB_DATABASE)  DB_DATABASE="${DB_DATABASE:-$value}" ;;
        DB_USERNAME)  DB_USERNAME="${DB_USERNAME:-$value}" ;;
        DB_PASSWORD)  DB_PASSWORD="${DB_PASSWORD:-$value}" ;;
        ESURAT_IMAGE) ESURAT_IMAGE="${ESURAT_IMAGE:-$value}" ;;
        DATA_PATH)    DATA_PATH="${DATA_PATH:-$value}" ;;
    esac
done < "$ENV_FILE"

# MySQL produksi biasanya hanya membuka socket (`localhost`), bukan TCP
# 127.0.0.1. host.docker.internal bermakna "host" dari dalam container;
# padanannya di host adalah localhost (socket).
DB_HOST="${DB_HOST:-localhost}"
DB_PORT="${DB_PORT:-3306}"
case "$DB_HOST" in
    host.docker.internal|gateway.docker.internal) DB_HOST=localhost ;;
esac

if [ -z "$DB_DATABASE" ] || [ -z "$DB_USERNAME" ]; then
    echo "FATAL: DB_DATABASE/DB_USERNAME kosong di $ENV_FILE." >&2
    exit 1
fi

# --- 1. Database dump (non-interaktif; -p tidak boleh berdiri sendiri) ------
# Password lewat MYSQL_PWD, bukan argv: argumen proses terbaca user lain di
# host lewat `ps`. `-p` tanpa nilai berarti prompt, MYSQL_PWD tidak.
# --single-transaction: dump konsisten tanpa mengunci tabel MyISAM panjang.
# --routines --triggers: procedure/event dan trigger ikut terbawa.
# Dump ke file langsung (bukan pipe): tanpa `set -o pipefail`, kegagalan
# mysqldump dalam `mysqldump | gzip` tidak terdeteksi dan backup kosong
# terlihat "berhasil".
if ! MYSQL_PWD="$DB_PASSWORD" mysqldump \
        --host="$DB_HOST" --port="$DB_PORT" \
        --user="$DB_USERNAME" \
        --single-transaction --routines --triggers \
        --result-file="$OUT/db.sql" \
        "$DB_DATABASE"; then
    echo "FATAL: mysqldump gagal." >&2
    exit 1
fi

# Dump valid selalu memuat header mysqldump; tanpa ini, pesan error koneksi
# bisa lolos sebagai "dump". Header mengikuti versi KLIEN: "MySQL dump" untuk
# Oracle MySQL, "MariaDB dump" untuk klien MariaDB (header MariaDB >= 11 juga
# didahului baris /*M!999999 ... */).
if [ ! -s "$OUT/db.sql" ] || ! grep -qE '(MySQL|MariaDB) dump' "$OUT/db.sql"; then
    echo "FATAL: hasil dump kosong atau bukan dump MySQL; backup dibatalkan." >&2
    exit 1
fi

gzip -f "$OUT/db.sql"

sha256sum "$OUT/db.sql.gz" > "$OUT/db.sql.gz.sha256"

log "Database: $(du -h "$OUT/db.sql.gz" | cut -f1)"

# --- 2. .env (berisi APP_KEY: tanpa ini data terenkripsi tak bisa dipulihkan)
install -m 600 "$ENV_FILE" "$OUT/env.backup"

# --- 3. Storage (lampiran privat + log) -------------------------------------
STORAGE_DIR="${DATA_PATH:-/srv/esurat/data}/storage"
if [ -d "$STORAGE_DIR" ]; then
    if command -v rsync > /dev/null 2>&1; then
        rsync -a "$STORAGE_DIR/" "$OUT/storage/"
    else
        # Host minimalis tanpa rsync: cp tetap benar (backup penuh).
        mkdir -p "$OUT/storage"
        cp -a "$STORAGE_DIR/." "$OUT/storage/"
    fi
    log "Storage: $(du -sh "$OUT/storage/" | cut -f1)"
else
    log "PERINGATAN: $STORAGE_DIR tidak ada; storage tidak dibackup."
fi

# --- 4. Digest image yang sedang dipakai ------------------------------------
if [ -n "$ESURAT_IMAGE" ] && docker image inspect "$ESURAT_IMAGE" > /dev/null 2>&1; then
    docker image inspect --format '{{index .RepoDigests 0}}' "$ESURAT_IMAGE" > "$OUT/image.txt" 2>/dev/null \
        || printf '%s\n' "$ESURAT_IMAGE" > "$OUT/image.txt"
else
    printf '%s\n' "${ESURAT_IMAGE:-tidak-diketahui}" > "$OUT/image.txt"
fi

# Backup lengkap: jangan biarkan trap menghapusnya.
trap - EXIT

log "Backup selesai: $OUT"

# --- 5. Retensi: buang stamp lebih lama dari RETAIN_DAILY (default 14) -------
RETAIN_DAILY="${RETAIN_DAILY:-14}"
case "$RETAIN_DAILY" in
    ''|*[!0-9]*) RETAIN_DAILY=14 ;;
esac

ls -1dt "$BACKUP_ROOT"/2* 2>/dev/null | tail -n +$((RETAIN_DAILY + 1)) | while IFS= read -r old; do
    # Jangan pernah menghapus stamp yang baru saja dibuat (RETAIN_DAILY=0).
    if [ "$old" = "$OUT" ]; then
        continue
    fi
    log "Retensi: hapus $old"
    rm -rf "$old"
done
