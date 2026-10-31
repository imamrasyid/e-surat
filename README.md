# e-Surat

Sistem manajemen surat elektronik untuk instansi pemerintah. Mengelola siklus hidup surat resmi — penyusunan dan pengiriman surat, nota dinas, disposisi, kotak masuk (surat masuk & undangan), arsip, sampai master data pengguna/jabatan/role.

[![tests](https://github.com/msbhdn/e-surat/actions/workflows/tests.yml/badge.svg)](https://github.com/msbhdn/e-surat/actions/workflows/tests.yml)
![PHP](https://img.shields.io/badge/php-%5E8.3-777BB4)
![Laravel](https://img.shields.io/badge/laravel-13-FF2D20)
![Vue](https://img.shields.io/badge/vue-3.5-4FC08D)
![License](https://img.shields.io/badge/license-MIT-blue)

## Fitur

| Modul            | Isi                                                                                                                                                                                                               |
| ---------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Penyusunan surat | Compose surat dengan nomor agenda otomatis (dari `RefAgendaSurat` + kode klasifikasi), penerima, dokumen lampiran, dan riwayat aktivitas. Surat terkirim (`sent`) bisa diedit per bagian: isi, penerima, dokumen. |
| Kotak masuk      | Surat masuk utama dan undangan: tabel dengan pencarian, filter status baca, pagination, dan detail surat + timeline penerima. Surat yang belum dibaca otomatis ditandai terbaca saat dibuka.                      |
| Nota dinas       | Daftar nota dinas dengan pencarian/filter, pembuatan nota, dan penutupan (`close`) nota.                                                                                                                          |
| Disposisi        | Disposisi per surat, termasuk disposisi khusus per pengguna, dengan timeline.                                                                                                                                     |
| Arsip            | Kerangka halaman `/archive` — daftar arsip **belum diimplementasikan** (masih `PlaceholderPattern`).                                                                                                              |
| Master data      | Pengguna, jabatan, role & permission, kode klasifikasi arsip, dan follow-up.                                                                                                                                      |
| Dashboard        | Statistik ringkas dan data chart.                                                                                                                                                                                 |
| Autentikasi      | Laravel Fortify: email/password, verifikasi email, 2FA (TOTP + recovery code), passkeys.                                                                                                                          |
| Otorisasi        | spatie/laravel-permission (RBAC) dengan middleware `permission`, `role`, dan `wpermission` (wildcard, mis. `master.*`).                                                                                           |
| UX               | Badge jumlah inbox di sidebar (di-cache 30 detik per jabatan aktif), toast, tema terang/gelap.                                                                                                                    |

## Tech Stack

| Lapisan       | Teknologi                                                                                                      |
| ------------- | -------------------------------------------------------------------------------------------------------------- |
| Backend       | PHP ^8.3, Laravel ^13, Inertia ^3, Laravel Fortify, spatie/laravel-permission, Ziggy                           |
| Frontend      | Vue 3 (Composition API + `<script setup>`), TypeScript, Inertia Vue, Tailwind CSS 4, reka-ui, GSAP, vue-sonner |
| Build         | Vite+ (`vp`) — `vite` di-override ke `@voidzero-dev/vite-plus-core`; SSR dinonaktifkan                         |
| Database      | Default SQLite; MySQL/PostgreSQL didukung lewat `.env`                                                         |
| Kualitas kode | Pest 4, Larastan (PHPStan), Pint, `vue-tsc`                                                                    |

## Kebutuhan Sistem

- PHP **^8.3** (CI memakai 8.4), Composer 2
- Node **22+** dan npm (lockfile npm — jangan pakai pnpm/yarn)
- Database: SQLite (default, file `database/database.sqlite`) atau MySQL/PostgreSQL

## Instalasi

### Cara cepat (satu perintah)

```bash
composer setup
```

Perintah tersebut menjalankan `composer install` → salin `.env` → `key:generate` → `migrate --force` → `npm install` → `npm run build`.

### Manual

```bash
git clone https://github.com/msbhdn/e-surat.git
cd e-surat

composer install
cp .env.example .env
php artisan key:generate
php artisan migrate    # SQLite: file database/database.sqlite dibuat otomatis

npm install
npm run build          # wajib: public/build tidak di-track (gitignored)
```

### Akun hasil seeding (opsional)

```bash
php artisan migrate --seed
```

Seeder membuat permission, role `Super Admin` / `Pengelola` / `Pengguna`, dan satu user super admin:

| Field    | Nilai                                                     |
| -------- | --------------------------------------------------------- |
| Username | `superadmin`                                              |
| Email    | `msbhdn@icloud.com`                                       |
| Password | `password` (default factory — **ganti sebelum produksi**) |

## Menjalankan Development

```bash
composer dev
```

Menjalankan tiga proses sekaligus via `concurrently`: `php artisan serve`, `php artisan queue:listen` (queue driver `database`), dan `npm run dev` (Vite dev server). Aplikasi diakses di `http://127.0.0.1:8000`.

## Perintah Penting

| Perintah                       | Fungsi                                                                         |
| ------------------------------ | ------------------------------------------------------------------------------ |
| `composer setup`               | Bootstrap lengkap dari nol (install, `.env`, key, migrate, npm install, build) |
| `composer dev`                 | Server + queue worker + Vite dev server                                        |
| `npm run dev`                  | Vite dev server saja                                                           |
| `npm run build`                | Build produksi ke `public/build`                                               |
| `npm run check` / `check:fix`  | Lint + format TS/Vue (`vp check`)                                              |
| `npm run types:check`          | Type-check frontend (`vue-tsc --noEmit`)                                       |
| `composer lint` / `lint:check` | Pint (format / cek tanpa mengubah file)                                        |
| `composer types:check`         | PHPStan (Larastan)                                                             |
| `composer test`                | `config:clear` → `lint:check` → `types:check` → `php artisan test`             |
| `composer ci:check`            | Yang dijalankan CI: `npm run check` → `npm run types:check` → `composer test`  |

## Arsitektur

MVC dengan lapisan **Action** tipis untuk logika bisnis. Controller hanya memvalidasi lewat FormRequest dan mendelegasikan ke Action.

```mermaid
flowchart LR
    R[routes/*.php] --> M["middleware: auth, permission, wpermission"]
    M --> C[Controller]
    C --> V[FormRequest]
    C --> A["Action / Service<br/>DB::transaction"]
    A --> D[(Eloquent Models)]
    C --> I["Inertia::render('pages/...')"]
    I --> P[Vue 3 + TS]
```

- **Route modular**: `routes/web.php` memuat `memo.php`, `disposition.php`, `inbox.php`, `arsip.php`, `master.php`, `settings.php`. Otorisasi sepenuhnya lewat middleware per-route (tanpa Policy).
- **Inertia props**: controller mengembalikan props bertipe (termasuk `breadcrumbItems`); daftar memakai `paginate(15)->withQueryString()` dan pencarian `whereLike`.
- **Transaksi**: penulisan inti (surat + nomor agenda + penerima + dokumen) dibungkus `DB::transaction` di Action.
- **Tipe bersama**: `resources/js/types/models/*` harus diperbarui bersamaan dengan props backend, jika tidak `npm run types:check` gagal.

## Struktur Direktori

```text
.
├── app/
│   ├── Actions/                 # Logika bisnis: Letter, Memo, Disposition, Dashboard, MasterData, Fortify
│   ├── Http/
│   │   ├── Controllers/         # Controller tipis (MasterData/, Inbox/, Settings/)
│   │   ├── Middleware/          # HandleInertiaRequests, HandleAppearance, WildcardPermissionMiddleware
│   │   └── Requests/            # Satu FormRequest per operasi tulis
│   ├── Models/                  # Eloquent: letters, memo, dispositions, ref_agenda_surat, ref_kd_klasifikasi, …
│   └── Services/                # LetterActivityService, InboxCountsCache, PermissionsCache
├── bootstrap/app.php            # Alias middleware + web stack
├── database/                    # Migrations, seeders (Permissions, RoleSeeder), factories
├── public/                      # Document root; public/build hasil build (tidak di-track)
├── resources/
│   ├── css/app.css
│   └── js/
│       ├── components/          # Shared components + ui/ (gaya shadcn/reka-ui, jangan diedit manual)
│       ├── composables/         # usePermissions, useAppearance, useFormattedDate, useDecades, …
│       ├── layouts/             # Layout Inertia
│       ├── pages/               # app/{sent,memo,disposition,primary,undangan}, archive, master, settings, auth
│       └── types/               # Tipe model TypeScript (selaras dengan props backend)
├── routes/                      # web.php + modul per domain
├── storage/
└── tests/                       # Pest: Unit/, Feature/{Auth,Settings}
```

## Routing & Hak Akses

| Modul                  | Route file        | Permission middleware                                                                                              |
| ---------------------- | ----------------- | ------------------------------------------------------------------------------------------------------------------ |
| Surat (compose & sent) | `web.php`         | `compose.create`, `compose.store`, `sent.index`, `sent.show`, `sent.edit`                                          |
| Kotak masuk            | `inbox.php`       | `primary.index`, `primary.show`, `undangan.index`, `undangan.show`                                                 |
| Nota dinas             | `memo.php`        | `memo.index`, `memo.show`, `memo.create`, `memo.store`, `memo.close`                                               |
| Disposisi              | `disposition.php` | `disposition.index`, `disposition.show`, `disposition.create`, `disposition.store`                                 |
| Arsip                  | `arsip.php`       | Cukup `auth` (halaman masih placeholder)                                                                           |
| Master data            | `master.php`      | `master.pengguna`, `master.jabatan`, `master.klasifikasi`, `master.followup`; index memakai `wpermission:master.*` |
| Settings               | `settings.php`    | Cukup `auth` (profil, password, tampilan, keamanan)                                                                |

## Testing

Backend memakai **Pest 4** dengan SQLite in-memory (`phpunit.xml` memaksa `DB_CONNECTION=sqlite`, `:memory:`), jadi aman dijalankan kapan saja tanpa menyentuh database development.

```bash
php artisan test                              # seluruh suite
php artisan test --filter=AuthenticationTest  # satu file
php artisan test tests/Feature/Auth/          # satu direktori
composer test                                 # lint + PHPStan + Pest
```

Cakupan saat ini: alur auth, settings, dashboard, cache badge inbox, dan cache permission. Domain inti e-surat (surat, nota dinas, disposisi, arsip, master data) masih minim test — tambahkan Feature test saat mengubah alur tersebut.

## Deployment

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link          # dokumen surat disimpan di disk `public`
npm ci && npm run build           # public/build gitignored — wajib di-build di server
php artisan config:cache
php artisan queue:work            # QUEUE_CONNECTION=database
```

Catatan:

- `public/build` sengaja **tidak di-commit** (lihat `.gitignore`). Tanpa `npm run build`, Inertia gagal memuat manifest Vite.
- `php artisan route:cache` belum bisa dipakai karena beberapa route didefinisikan sebagai closure; gunakan `config:cache` saja.
- File `.env` produksi perlu `APP_ENV=production`, `APP_DEBUG=false`, kredensial database, dan konfigurasi mail/queue.

## Lisensi

MIT — sesuai deklarasi `composer.json`.
