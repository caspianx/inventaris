# Panduan Penggunaan Aplikasi Inventaris

Dokumentasi singkat untuk menjalankan dan menggunakan aplikasi Inventory (Laravel).

## Ringkasan
- Nama aplikasi: Inventaris
- Framework: Laravel 12
- Target PHP: 8.2+

## Persyaratan Sistem
- PHP 8.2 atau lebih baru (direkomendasikan 8.2.12)
- Composer
- Database: MySQL / MariaDB / SQLite
- Node.js & npm (untuk asset + Vite)

Jika menggunakan XAMPP di Windows, gunakan `C:\xampp\php\php.exe` untuk memastikan versi PHP yang tepat.

## Instalasi (singkat)
1. Clone repository:

   git clone https://github.com/caspianx/inventaris.git
   cd inventaris

2. Pasang dependensi PHP:

   composer install --no-interaction --prefer-dist

3. Salin file environment dan generate key:

   cp .env.example .env
   php artisan key:generate

4. Konfigurasi database di `.env` (DB_CONNECTION, DB_DATABASE, DB_USERNAME, DB_PASSWORD).

5. Jalankan migrasi dan seed (opsional):

   php artisan migrate --seed

6. Pasang dependensi frontend dan build:

   npm install
   npm run build    # produksi
   npm run dev      # pengembangan (Vite)

## Menjalankan Aplikasi

- Menggunakan PHP built-in (untuk pengembangan):

  C:\xampp\php\php.exe artisan serve --host=127.0.0.1 --port=8000

- Jika menggunakan XAMPP, pastikan `DocumentRoot` mengarah ke `public/` dan PHP yang digunakan kompatibel.

## Perintah Umum

- `php artisan migrate` — Jalankan migrasi database
- `php artisan migrate:rollback` — Membatalkan migrasi terakhir
- `php artisan migrate --seed` — Jalankan migrasi dan seed
- `php artisan route:list` — Tampilkan daftar route
- `php artisan tinker` — REPL untuk interaksi aplikasi

Gunakan path penuh PHP XAMPP jika `php -v` menunjukkan PHP 8.3 di sistem, misal:

```
C:\xampp\php\php.exe artisan --version
C:\xampp\php\php.exe php82-test.php
```

## Menjalankan Test

- PHPUnit (lokal):

  vendor\bin\phpunit

atau

  C:\xampp\php\php.exe vendor\bin\phpunit

## Troubleshooting Umum

- Error tentang versi PHP di Composer (misal "Your Composer dependencies require a PHP version \">= 8.3.0\""):
  - Pastikan `composer config platform.php` diset ke `8.2.12` atau versi yang sesuai:

    composer config platform.php 8.2.12

  - Jika masih muncul, periksa `vendor/composer/platform_check.php` — file ini di-generate Composer untuk pengecekan platform. Jika terlanjur dihasilkan dengan requirement yang salah, regenerasi autoload:

    composer dump-autoload

- Jika menggunakan XAMPP tapi `php -v` mengarah ke PHP lain, gunakan path XAMPP secara eksplisit atau sesuaikan `PATH` OS.

## Deployment

- Pastikan environment variabel di server produksi sesuai.
- Jalankan `composer install --no-dev --optimize-autoloader` dan `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`.

## Kontribusi

- Ikuti gaya kode Laravel.
- Buat branch baru untuk setiap fitur/bugfix dan ajukan PR ke `main`.

## Kontak

- Untuk bantuan lebih lanjut, buka issue di repository atau hubungi pemelihara proyek.
