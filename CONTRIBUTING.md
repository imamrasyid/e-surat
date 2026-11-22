# Contributing to e-Surat

Terima kasih telah meluangkan waktu untuk berkontribusi. Issue dan pull request dari siapa pun — pengguna, operator, maupun developer — sangat membantu.

## Reporting issues

- Cari issue yang sudah ada sebelum membuka issue baru.
- Sertakan langkah reproduksi yang minimal, hasil yang diharapkan, dan hasil aktual.
- Cantumkan environment yang relevan: versi PHP/Node, database, browser, dan feature gate yang aktif.
- Lampirkan log, screenshot, atau stack trace bila ada. Jangan sertakan data pribadi, token, atau isi surat yang bersifat rahasia.

> [!IMPORTANT]
> Jangan membuka issue publik untuk kerentanan keamanan. Ikuti [SECURITY.md](SECURITY.md) untuk pelaporan privat.

## Development setup

```bash
git clone https://github.com/msbhdn/e-surat.git
cd e-surat
composer setup
php artisan db:seed --class=DatabaseSeeder
composer dev
```

Default-nya aplikasi memakai SQLite. Detail kebutuhan, data demo, dan perintah development ada di [`README.md`](README.md).

## Pull requests

- Fork repository dan buat branch dari `main`.
- Usahakan satu pull request berisi satu perubahan yang fokus. Hindari mencampur refactor besar dengan perbaikan bug.
- Kaitkan issue yang relevan di deskripsi pull request.
- Tambahkan test untuk perubahan pada domain inti: surat, disposisi, nota dinas, arsip, dan master data.
- Jalankan `composer ci:check` sebelum membuka pull request. Perintah itu menjalankan lint/format frontend, `vue-tsc`, Pint, PHPStan, dan seluruh test Pest.
- Perbarui `resources/js/types/models/*` bila props Inertia berubah.
- Perbarui `README.md` dan `CHANGELOG.md` untuk perubahan yang terlihat oleh pengguna.
- Sertakan screenshot atau GIF untuk perubahan UI.
- Jangan commit `.env`, kredensial, `public/build`, atau file generated lainnya.
- Gunakan Conventional Commits (`feat:`, `fix:`, `test:`, `docs:`) agar riwayat mudah dibaca.

## Code conventions

Ikuti pola yang sudah ada di kode sekitar:

- **Backend** — logika bisnis di Action dengan `handle()`; validasi di FormRequest; penulisan multi-langkah dibungkus `DB::transaction`.
- **Frontend** — Vue 3 Composition API dengan `<script setup lang="ts">`; komponen `ui/*` adalah generated reka-ui dan tidak diedit manual; class Tailwind memakai `cn`/`cva`.
- **Tipe** — props Inertia dan tipe TypeScript harus tetap sinkron.
- **Format** — Pint untuk PHP, `npm run check` untuk TS/Vue, dan `npm run types:check` untuk `vue-tsc`.

## Tests

Test backend memakai Pest 4 dengan SQLite in-memory, jadi tidak menyentuh database development.

```bash
php artisan test
composer test          # lint + PHPStan + Pest
composer ci:check      # yang dijalankan CI
```

Untuk perubahan pada domain inti, tambahkan Feature test yang mencakup alur yang berubah. Jangan hanya mengandalkan test yang sudah ada.

## Documentation

- Perbarui `README.md` bila ada perubahan setup, konfigurasi, atau fitur.
- Catat perubahan penting di [`CHANGELOG.md`](CHANGELOG.md) dengan format Keep a Changelog.
- Dokumentasi deployment ada di [`docs/deployment.md`](docs/deployment.md) dan [`docs/runbook-docker-frankenphp.md`](docs/runbook-docker-frankenphp.md).

## Conduct

Behavior in issues, pull requests, and discussions is covered by [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md).
