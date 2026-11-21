# Changelog

Semua perubahan penting pada proyek ini didokumentasikan di file ini.
Format mengikuti [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
dan [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.2.0] - 2026-11-21

### Added

- Pusat notifikasi dengan kanal database, Web Push, email, dan Telegram untuk surat masuk/undangan, disposisi, dan nota dinas.
- Halaman riwayat notifikasi, bell notifikasi, dan aksi baca semua.
- Sinkronisasi status baca notifikasi dan item dua arah: membuka notifikasi menandai item, membuka item menandai notifikasi, dan `Baca Semua` menandai item terkait.
- Tautan email, Telegram, dan Web Push melewati endpoint `notifications.open`.
- Catatan dan follow-up disposisi/nota dinas pada daftar notifikasi.
- Metadata TNDE, audit akses, dan detail metadata pada surat masuk/undangan.
- Arsip surat masuk berstatus `close` dengan lampiran privat dan riwayat audit.
- File komunitas: `LICENSE`, `CHANGELOG.md`, `CONTRIBUTING.md`, dan `CODE_OF_CONDUCT.md`.

### Changed

- Label status surat menjadi Terbuka/Selesai.
- README ditulis ulang dan diperluas untuk pengguna, kontributor, dan operator.

### Fixed

- Notifikasi surat berklasifikasi SR/R/T tetap dibatasi ke database dan Web Push.
- Hardening pusat notifikasi setelah review: akses per user, payload push yang di-redaksi, dan validasi endpoint push.
- Perbaikan parser backup deployment untuk header MariaDB.

## [2.1.7] - 2026-11-18

### Fixed

- Penerima disposisi dan nota dinas dapat membuat follow-up.
- Backup deployment membaca `.env` dengan benar dan memakai path unit systemd yang tepat.

[Unreleased]: https://github.com/msbhdn/e-surat/compare/v2.2.0...HEAD
[2.2.0]: https://github.com/msbhdn/e-surat/releases/tag/v2.2.0
[2.1.7]: https://github.com/msbhdn/e-surat/releases/tag/v2.1.7
