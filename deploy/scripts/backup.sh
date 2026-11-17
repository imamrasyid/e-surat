#!/bin/sh
#
# Backup rutin satu environment e-Surat (jalankan di HOST, bukan di container).
# Kredensial dibaca dari .env project compose (mode 600) supaya cron tidak
# berhenti di prompt password. Idempotent: menjalankan ulang hanya menambah
# direktori stamp baru.
#
# Output: BACKUP_ROOT/<stamp>/{db.sql.gz,db.sql.gz.sha256,env.backup,storage/,image.txt}
#
# Lihat docs/runbook-docker-frankenphp.md §11 untuk uji restore.

set -eu

PROJECT_DIR="${PROJECT_DIR:-/srv/esurat/app}"
BACKUP_ROOT="${BACKUP_ROOT:-/srv/esurat/data/backups}"

if [ ! -f "$PROJECT_DIR/.env" ]; then
    echo "FATAL: $PROJECT_DIR/.env tidak ditemukan." >&2
    exit 1
fi

STAMP="$(date +%Y%m%d-%H%M%S)"
OUT="$BACKUP_ROOT/$STAMP"
mkdir -p "$OUT"

log() {
    printf '[%s] %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$1"
}

log "Backup dimulai: $OUT"

# --- Kredensial dari .env (KEY=VALUE, tanpa ekspansi shell) ------------------
DB_HOST="127.0.0.1"
DB_PORT="3306"
DB_DATABASE=""
DB_USERNAME=""
DB_PASSWORD=""
ESURAT_IMAGE=""
DATA_PATH=""

while IFS='=' read -r key value; do
    key="$(printf '%s' "$key" | tr -d '[:space:]')"
    [ -z "$key" ] && continue
    case "$key" in '#'*) continue ;; esac
    case "$key" in
        # Lingkungan menang (mis. DB_HOST=localhost dari
        # /etc/esurat/<instance>.env unit systemd); .env hanya fallback,
        # karena nilai env aplikasi (host.docker.internal) tidak berarti
        # di host.
        DB_HOST)       DB_HOST="${DB_HOST:-$value}" ;;
        DB_PORT)       DB_PORT="${DB_PORT:-$value}" ;;
        DB_DATABASE)   DB_DATABASE="${DB_DATABASE:-$value}" ;;
        DB_USERNAME)   DB_USERNAME="${DB_USERNAME:-$value}" ;;
        DB_PASSWORD)   DB_PASSWORD="${DB_PASSWORD:-$value}" ;;
        ESURAT_IMAGE)  ESURAT_IMAGE="${ESURAT_IMAGE:-$value}" ;;
        DATA_PATH)     DATA_PATH="${DATA_PATH:-$value}" ;;
    esac
done < "$PROJECT_DIR/.env"

if [ -z "$DB_DATABASE" ] || [ -z "$DB_USERNAME" ]; then
    echo "FATAL: DB_DATABASE/DB_USERNAME kosong di $PROJECT_DIR/.env." >&2
    exit 1
fi

# --- 1. Database dump (non-interaktif; -p tidak boleh berdiri sendiri) ------
# --single-transaction: dump konsisten tanpa mengunci tabel MyISAM panjang.
# --routines --triggers: procedure/event dan trigger ikut terbawa.
# Dump ke file langsung (bukan pipe): tanpa `set -o pipefail`, kegagalan
# mysqldump dalam `mysqldump | gzip` tidak terdeteksi dan backup kosong
# terlihat "berhasil".
if ! mysqldump \
        --host="$DB_HOST" --port="$DB_PORT" \
        --user="$DB_USERNAME" --password="$DB_PASSWORD" \
        --single-transaction --routines --triggers \
        --result-file="$OUT/db.sql" \
        "$DB_DATABASE"; then
    echo "FATAL: mysqldump gagal." >&2
    rm -rf "$OUT"
    exit 1
fi

# Dump valid selalu memuat header mysqldump; tanpa ini, pesan error koneksi
# bisa lolos sebagai "dump".
if [ ! -s "$OUT/db.sql" ] || ! grep -q 'MySQL dump' "$OUT/db.sql"; then
    echo "FATAL: hasil dump kosong atau bukan dump MySQL; backup dibatalkan." >&2
    rm -rf "$OUT"
    exit 1
fi

gzip -f "$OUT/db.sql"

sha256sum "$OUT/db.sql.gz" > "$OUT/db.sql.gz.sha256"

log "Database: $(du -h "$OUT/db.sql.gz" | cut -f1)"

# --- 2. .env (berisi APP_KEY: tanpa ini data terenkripsi tak bisa dipulihkan)
install -m 600 "$PROJECT_DIR/.env" "$OUT/env.backup"

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
IMAGE_NAME="$(printf '%s' "${ESURAT_IMAGE:-}" | cut -d: -f1)"
if [ -n "$IMAGE_NAME" ] && docker image inspect "$ESURAT_IMAGE" > /dev/null 2>&1; then
    docker image inspect --format '{{index .RepoDigests 0}}' "$ESURAT_IMAGE" > "$OUT/image.txt" 2>/dev/null \
        || printf '%s\n' "$ESURAT_IMAGE" > "$OUT/image.txt"
else
    printf '%s\n' "${ESURAT_IMAGE:-tidak-diketahui}" > "$OUT/image.txt"
fi

log "Backup selesai: $OUT"

# --- 5. Retensi: buang stamp lebih lama dari RETAIN_DAILY (default 14) -------
RETAIN_DAILY="${RETAIN_DAILY:-14}"
ls -1dt "$BACKUP_ROOT"/2* 2>/dev/null | tail -n +$((RETAIN_DAILY + 1)) | while IFS= read -r old; do
    log "Retensi: hapus $old"
    rm -rf "$old"
done
