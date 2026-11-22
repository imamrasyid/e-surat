# Security Policy

## Versi yang didukung

| Versi | Didukung |
| ----- | -------- |
| 2.2.x | Ya       |
| < 2.2 | Tidak    |

## Melaporkan kerentanan

Gunakan **GitHub Private Vulnerability Reporting**:

https://github.com/msbhdn/e-surat/security/advisories/new

Jangan membuka issue publik, diskusi, atau pull request yang memuat detail kerentanan. Jangan menyertakan data pribadi, token, isi surat rahasia, atau data instansi nyata.

Sertakan sebanyak mungkin informasi berikut:

- deskripsi kerentanan dan dampaknya;
- langkah reproduksi atau proof of concept;
- versi/commit dan konfigurasi yang relevan (feature gate, database, browser);
- saran perbaikan bila ada.

## Cakupan

Prioritas utama:

- autentikasi, sesi, 2FA/passkey, dan otorisasi RBAC;
- akses surat, disposisi, nota dinas, arsip, dan lampiran privat;
- kebocoran notifikasi atau data ke kanal eksternal;
- isolasi environment/perusahaan;
- validasi input, upload dokumen, dan eksekusi query;
- konfigurasi deployment dan penanganan secret.

## Respons

Maintainer akan mengonfirmasi laporan secepatnya, menilai dampak, dan mengoordinasikan perbaikan serta pengungkapan. Mohon beri waktu yang wajar sebelum mempublikasikan detail kerentanan.

## Catatan

Pelanggaran perilaku komunitas ditangani melalui [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md), bukan melalui jalur kerentanan.
