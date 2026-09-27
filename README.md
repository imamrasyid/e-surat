<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## 🚀 Laravel 12 + Vue 3 + TypeScript (Starter Kit Fullstack)

Starter kit aplikasi fullstack menggunakan Laravel 12 sebagai backend & API, Inertia, serta Vue 3 + TypeScript + Vite sebagai frontend dengan integrasi melalui Inertia.js.

## ✨ Fitur Utama

- Laravel 12: Framework backend dengan API resource, middleware, validation.
- Inertia.js: Menghubungkan Laravel dengan Vue tanpa perlu membuat API terpisah.
- Vue 3 (Composition API) + TypeScript: Frontend modern dengan dukungan type-safety.
- Tailwind CSS: Utility-first styling untuk UI responsif.
- Vite: Build tool cepat untuk development & production.
- Autentikasi siap pakai (login) dari starter kit.

## 📦 Instalasi

**1. Clone Repository**

```javascript
git clone https://github.com/msbhdn/e-surat.git
cd e-surat
```

**2. Setup Backend (Laravel)**

```javascript
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

👉 Backend berjalan di http://127.0.0.1:8000.

**3. Setup Frontend (Vue 3 + TS via Inertia)**

```javascript
npm install
npm run dev
```

👉 Frontend berjalan di http://127.0.0.1:5173 (atau otomatis dari Laravel melalui Inertia).

## ⚙️ Konfigurasi

- Sesuaikan database di file .env.
- Untuk email/queue, sesuaikan juga konfigurasi .env.
- Jika pakai Inertia, frontend otomatis disajikan melalui Laravel route.

## 📂 Struktur Proyek

```javascript
.
├── app/                   # Laravel backend
├── bootstrap/
├── config/
├── database/
├── resources/             # Frontend (Vue 3 + TS + Inertia)
│   ├── js/
│   │   ├── components/    # Reusable Vue components
│   │   ├── composables/   # Vue composables / hooks
│   │   ├── layouts/       # Application layouts
│   │   ├── lib/           # Utility functions and configuration
│   │   ├── pages/         # Page components
│   │   └── types/         # TypeScript definitions
│   └── css/
├── routes/
│   └── web.php
└── ...
```

## 🧪 Testing

**Backend**

```javascript
php artisan test
```

**Frontend (Vitest)**

```javascript
npm run test
```

## 🚀 Deployment

- Laravel: Bisa dideploy ke server (Nginx/Apache) atau platform seperti Laravel Forge/Vapor.
- Frontend (Inertia + Vite): Dibuild otomatis saat menjalankan npm run build.
