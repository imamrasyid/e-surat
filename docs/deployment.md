# Deployment multi-perusahaan e-Surat

Satu codebase, satu release train, satu environment produksi terisolasi per
perusahaan. Setiap perusahaan memakai image/tag yang sama, tetapi database,
cache/session, queue, storage, mail, Telegram, `APP_KEY`, dan domain milik
sendiri. Aplikasi belum punya boundary `company_id`, global scope, atau policy
tenant pada query surat/lampiran, jadi isolasi dilakukan oleh environment.

Runbook operasional lengkap (Docker + FrankenPHP): [`runbook-docker-frankenphp.md`](runbook-docker-frankenphp.md).

## 1. Invariant

| Aspek                   | Aturan                                                                      |
| ----------------------- | --------------------------------------------------------------------------- |
| Source of truth kode    | `main` + tag immutable `v<major>.<minor>.<patch>`                           |
| Artifact                | Image Docker dibangun sekali per tag oleh `.github/workflows/release.yml`   |
| Immutability            | Server menarik image per tag/digest; tidak build dari branch                |
| Environment             | Satu environment/VM/host per perusahaan                                     |
| Database                | Satu database per perusahaan; tidak ada shared table                        |
| Cache/session/queue     | Backend persisten milik environment tersebut                                |
| Lampiran                | Storage privat milik environment tersebut (volume host atau object storage) |
| Secret                  | Secret manager/file `.env` dengan izin ketat, bukan repository              |
| Perbedaan fitur         | `config/company.php` + `CompanyFeature`                                     |
| Perbedaan aturan bisnis | `COMPANY_RULESET` (strategy domain, bukan `if` perusahaan)                  |

Catatan rilis dan digest image disimpan pada audit deploy (tiket/ringkasan
workflow release), bukan di repository.

Deploy tidak pernah memakai branch atau cherry-pick khusus perusahaan. Jika
kebutuhan mengubah kontrak domain secara permanen dan tidak bisa dinyatakan
sebagai config/feature/ruleset, perlakukan sebagai product edition
dengan artifact dan lifecycle terpisah.

## 2. Kontrak environment produksi

Wajib untuk setiap environment perusahaan:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://esurat.company-a.example.com
APP_KEY=            # unik per perusahaan: php artisan key:generate --show
COMPANY_KEY=company-a
COMPANY_FEATURES=   # mis. email,telegram,webpush
COMPANY_RULESET=default

DB_CONNECTION=mysql # database milik perusahaan tersebut
SESSION_DRIVER=database
SESSION_DOMAIN=null # cookie host-only; jangan lintas subdomain
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Storage lampiran punya dua mode yang sah:

| Mode                          | Konfigurasi                                                               | Syarat                                                           |
| ----------------------------- | ------------------------------------------------------------------------- | ---------------------------------------------------------------- |
| Volume host (VM/host tunggal) | `FILESYSTEM_DISK=local`, bind-mount `DATA_PATH/storage` ke `/app/storage` | disk host persisten + backup wajib; hanya satu host yang menulis |
| Object storage                | `FILESYSTEM_DISK=s3` + kredensial bucket privat                           | platform ephemeral atau aplikasi multi-replica                   |

Jangan memakai disk `public`, `public/build` yang di-commit, atau
`php artisan storage:link` untuk lampiran. Lampiran hanya disajikan endpoint
`letter.docs.download` (unduhan) dan `letter.docs.preview` (pratinjau inline)
setelah pemeriksaan akses.

Tambahan:

- `SESSION_COOKIE` boleh diberi prefiks company key agar tidak bentrok saat
  operator membuka beberapa environment di satu browser.
- Kredensial `DB_*`/`MAIL_*`/`TELEGRAM_*`/`AWS_*` berasal dari secret manager
  atau `.env` dengan owner deploy dan mode `600`.
- Jangan commit file `.env` produksi dan jangan memakai nilai `.env.example`
  (SQLite/mail log/disk lokal) sebagai template production.
- Domain atau IP boleh berbeda per server; port host diatur di compose
  (`APP_BIND_IP`, `APP_HTTP_PORT`), bukan di kode.
- Pada Docker, service di host dijangkau lewat `host.docker.internal`
  (compose menambahkan `host-gateway`), bukan `127.0.0.1` di dalam container.
- `MAIL_MAILER=log` adalah default: email hanya ditulis ke log. Feature `email`
  baru boleh aktif bila mailer nyata (bukan `log`/`array`) dan
  `MAIL_FROM_ADDRESS` sudah diisi.

## 3. Bootstrap perusahaan baru

Jalur Docker (detail di runbook):

```bash
# 1. Preflight konfigurasi (gagal = container berhenti, bukan melayani trafik)
docker compose run --rm app php artisan company:validate-config

# 2. Schema + katalog permission + master data + administrator default
docker compose run --rm app php artisan migrate --force
docker compose run --rm app php artisan db:seed --class=DatabaseSeeder

# 3. Cache konfigurasi (juga dijalankan entrypoint setiap start)
docker compose run --rm app php artisan config:cache

# 4. Webhook Telegram hanya bila feature aktif
docker compose run --rm app php artisan telegram:webhook:set
```

`DatabaseSeeder` membuat administrator default yang sama di semua server:

| Field    | Nilai                            |
| -------- | -------------------------------- |
| Username | `superadmin`                     |
| Name     | `Super Administrator`            |
| Password | `password` (ganti setelah login) |
| Email    | kosong (login memakai username)  |

`DemoDataSeeder` hanya untuk lingkungan lokal. Master data awal (role, jabatan,
tindak lanjut, klasifikasi) berasal dari satu `MasterDataSeeder` yang sama untuk
semua perusahaan; penyesuaian dilakukan lewat UI Master Data. Seeder idempotent
dan tidak mereset password administrator yang sudah diganti.

## 4. Feature dan ruleset

- **Nilai/limit/branding/integrasi** → `config/company.php` atau master data.
- **Menu/alur opsional** → `CompanyFeature::enabled()` + route middleware
  `feature:<nama>` + enforcement di controller/Action/notification.
- **Keputusan bisnis** → ruleset/strategy domain; implementation `Default...`
  wajib mempertahankan perilaku lama.

Feature nonaktif menghasilkan `404`, termasuk bila URL dipanggil langsung.
Status feature di props Inertia hanya untuk UX, bukan enforcement.

Menambah feature/ruleset berarti:

1. menambah implementasi (gate/strategy) pada artifact yang sama;
2. mendaftarkan nama stabilnya di `config/company.php`;
3. menambah Feature test untuk perilaku default dan variasi baru;
4. menjalankan `company:validate-config` pada CI/deploy.

Feature aktif wajib punya seluruh konfigurasi pada `company.feature_requirements`.
Saat ini:

- `email`: `MAIL_MAILER` nyata (bukan `log`/`array`) dan `MAIL_FROM_ADDRESS`.
- `telegram`: `services.telegram.bot_token`, `bot_username`, dan `webhook_secret`.
- `webpush`: `VAPID_SUBJECT`, `VAPID_PUBLIC_KEY`, dan `VAPID_PRIVATE_KEY`.
  Selain itu `APP_URL` wajib `https://<domain>` dengan hostname DNS (bukan
  alamat IP); syarat runtime ini diperiksa `CompanyFeature::webPushEligible()`
  dan tidak menggagalkan deploy.

### Notifikasi browser (Web Push)

Notifikasi database (`/notifications`, bell, badge unread) selalu aktif tanpa
feature. Web Push hanya dikirim bila seluruh syarat berikut terpenuhi:

1. `APP_URL` memakai `https://` dan hostname berupa domain nyata dengan TLS —
   jangan memakai URL `http://<ip>` atau `https://<ip>`.
2. `COMPANY_FEATURES` memuat `webpush`.
3. `VAPID_SUBJECT` (URL HTTPS atau `mailto:`), `VAPID_PUBLIC_KEY`, dan
   `VAPID_PRIVATE_KEY` terisi.

Urutan operator:

```bash
# Sekali saja per environment; simpan output ke secret manager.
php artisan webpush:vapid

# Isi ketiga VAPID_* di .env, lalu:
php artisan company:validate-config
php artisan migrate --force
php artisan config:cache

# Worker yang sudah ada memproses queue notifications.
php artisan queue:work --queue=notifications,default --tries=3 --timeout=60
```

Pengguna membuka Sheet pemberitahuan lalu menekan “Aktifkan notifikasi browser”;
tidak ada prompt izin otomatis saat page load. Bila tenant tidak memenuhi
syarat, Web Push tidak ditawarkan dan notifikasi database tetap berjalan.
Jangan merotasi kunci VAPID tanpa alasan: seluruh subscription browser lama
menjadi tidak valid dan pengguna harus mengaktifkan ulang.

## 5. Pipeline release

1. PR/push ke `main` → `.github/workflows/tests.yml` (quality gate).
2. Tag `v*.*.*` → `.github/workflows/release.yml` menjalankan quality gate
   (`composer ci:check`) lebih dulu, lalu membangun dan mendorong image ke GHCR.
   `workflow_dispatch` juga menerima input `release` untuk membangun ulang tag
   yang sudah ada. Image diberi label versi dan commit; digest dicatat di
   ringkasan job.
3. Build image menjalankan:
    - `npm ci && npm run build` pada Node 26;
    - `composer install --no-dev --no-interaction` dengan Composer 2.11.1;
    - `composer audit --no-dev` (build gagal bila ada advisory produksi);
    - `php artisan package:discover`;
    - OPcache `validate_timestamps=0` (image immutable).
4. Rollout ke server perusahaan **manual** mengikuti runbook: `docker compose
   pull`, satu-off `migrate --force`, lalu `docker compose up -d`.
   Migrations yang sudah deployed bersifat immutable; perubahan berikutnya
   memakai migration baru.
5. Worker production memproses `notifications` dan `default`:

```bash
php artisan queue:work --queue=notifications,default --tries=3 --timeout=60 --max-time=3600
```

`InboxItemNotification` memakai queue `notifications`; `queue:work` tanpa
`--queue` tidak menjamin mail/Telegram terkirim. Pada container, worker
di-recreate saat release (bukan `queue:restart`), dengan `stop_grace_period`
lebih besar dari `--timeout`.

### Laravel Cloud

Petakan satu perusahaan ke satu environment Cloud (compute + database + cache +
bucket privat + managed worker + domain). Build command menjalankan
`composer install --no-dev` dan `npm run build`; deploy command hanya
`php artisan migrate --force`. Jangan menaruh `storage:link`, `queue:restart`,
atau `horizon:terminate` di deploy command Cloud: worker dikelola platform dan
perubahan filesystem tidak persisten. Lampiran memakai default disk privat
(`Storage::disk()` tanpa nama) dengan bucket privat. Verifikasi setiap deploy
dengan `cloud deploy:monitor -n` dan cek `/up`, worker, serta log.

## 6. Lampiran privat dan migrasi legacy

- Path dihasilkan server: `letter-docs/<Y/m>/<ULID>.<ext>`.
- Kolom `name` menyimpan nama asli; kolom `path` menyimpan lokasi objek.
- URL publik `/storage/...` tidak lagi dibaca aplikasi.
- Unduhan dan pratinjau dialirkan lewat aplikasi setelah pemeriksaan akses,
  sehingga tidak ada URL publik, symlink, atau temporary URL yang bisa
  melewati otorisasi.
- Route `/storage/{path}` bawaan framework dimatikan (`serve => false`) untuk
  disk privat.

Mode volume host:

```text
DATA_PATH/storage/app/private   # lampiran
DATA_PATH/storage/logs          # log aplikasi
```

Container tidak menyimpan lampiran di writable layer, sehingga image tetap
kecil dan backup cukup menyalin `DATA_PATH/storage`.

Mode object storage: bucket privat per perusahaan, versioning/retention aktif,
`FILESYSTEM_DISK=s3`.

Untuk instalasi lama, salin file dari disk `public` ke disk privat default
sebelum URL legacy dihapus sepenuhnya:

```bash
php artisan letter-docs:migrate-storage --source-disk=public
```

Command ini idempotent dan melaporkan file yang hilang. Pada environment baru
tidak ada data legacy sehingga tidak perlu dijalankan. Di pipeline, set variable
`LEGACY_ATTACHMENT_MIGRATION=true` hanya untuk perusahaan yang dimigrasikan
(varian Cloud/VM lama); pada Docker jalankan sekali secara manual.

## 7. Backup, restore, dan observability

- Backup database terjadwal + point-in-time recovery bila provider mendukung.
- Mode volume host: backup `DATA_PATH/storage` (rsync/restic/borg) dan database
  pada jadwal yang sama; simpan off-site. Untuk jalur Docker, jadwal ini
  dibawa repository: `deploy/scripts/backup.sh` + systemd timer
  `esurat-backup@.timer` (pasang per server — langkah lengkap di runbook
  §11.1); dump dibuat non-interaktif dengan kredensial dari `.env`.
- Mode object storage: versioning/retention pada bucket lampiran.
- Backup tidak valid sebelum restore diuji: pulihkan database dan satu lampiran
  ke environment staging terpisah, lalu buktikan login, daftar surat, dan
  unduhan lampiran bekerja.
- Monitor per `company_key`: `/up`, error rate/latency, queue depth, failed jobs,
  delivery mail/Telegram, kapasitas database, disk host (`DATA_PATH`) atau
  object storage usage.
- Audit deploy mencatat `company_key`, `release`, `image_digest`, actor, dan
  hasil migration. Jangan mencatat secret atau isi surat.
- Provisioning harus idempotent: menjalankan template dua kali tidak membuat
  database/bucket/domain/volume ganda.

## 8. Rollback

1. Promosikan image release sebelumnya: set `RELEASE`/`ESURAT_IMAGE` lalu
   `docker compose pull && docker compose up -d --remove-orphans`.
2. **Jangan** menjalankan `migrate:rollback` otomatis: migration yang sudah
   deployed bersifat immutable dan harus backward-compatible.
3. Jika health check gagal, kembali ke tag sebelumnya; container lama tetap ada
   di host selama image belum di-prune.
4. Setelah rollback, periksa failed jobs dan queue depth sebelum menyatakan
   insiden selesai.

## 9. Checklist verifikasi rollout

1. `composer ci:check` lulus dan image untuk tag berhasil dibangun + didorong.
2. Dua environment staging (`company-a`, `company-b`) memakai image/digest yang
   sama, tetapi `APP_KEY`, domain/port, database, storage path, cache/session,
   queue, dan secret berbeda.
3. `COMPANY_KEY`, `COMPANY_FEATURES`, `COMPANY_RULESET` terbaca lewat `config()`
   setelah container menjalankan `config:cache`.
4. `company-a` dengan feature `telegram`: `POST /telegram/webhook` dengan
   `X-Telegram-Bot-Api-Secret-Token` valid mendapat respons controller;
   `company-b` tanpa feature mendapat `404`, juga saat URL dipanggil langsung.
5. Di `company-a`: buat surat + PDF, pastikan file ada di `DATA_PATH/storage`
   (atau bucket privat), unduhan hanya untuk user berhak, dan worker
   `notifications` memproses notifikasi.
6. Buka ID surat `company-a` pada `company-b` → `404`; daftar surat, dashboard,
   cache badge, log, database, dan storage `company-b` tidak memuat data
   `company-a`.
7. `DatabaseSeeder` membuat katalog permission, satu set master data yang sama,
   dan administrator default (`superadmin`/`password`, email kosong);
   `DemoDataSeeder` tidak berjalan dan tidak ada akun demo.
8. Deploy tag berikutnya ke kedua server: `/up`, worker, create/read/update
   surat, upload/download, dan failed jobs terpantau.
9. Rollback ke image sebelumnya tanpa membatalkan migration.
10. Restore database + satu lampiran pada staging terpisah dan buktikan aplikasi
    dapat login serta membaca dokumen.
11. Untuk setiap ruleset variasi: Feature test input domain yang sama pada
    `default` dan ruleset tersebut, plus penolakan `COMPANY_RULESET` tak dikenal.
