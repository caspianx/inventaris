# Inventaris (Inventory App)

Inventory App adalah aplikasi manajemen persediaan sederhana berbasis Laravel.

Ringkasan singkat:
- Web-based installer (`/install`) untuk membuat database dan menjalankan migrasi.
- `Create Database` pada halaman instalasi hanya membuat database (tidak menjalankan migrate/seed).
- Menu migrasi (`/install/migrate-existing`) menjalankan migrasi struktur tabel saja (tidak menjalankan seeder).
- Setelah migrasi berhasil, pengguna diarahkan kembali ke halaman instalasi untuk membuat admin pertama.

Dokumentasi lengkap instalasi ada di `INSTALLATION.md`.

Mulai cepat (developer):

1. Salin `.env.example` ke `.env` dan atur konfigurasi database jika perlu.
2. Jalankan:

```bash
composer install
npm install
php artisan key:generate
php artisan serve
```

3. Buka browser ke `http://127.0.0.1:8000/install` dan ikuti alur instalasi.

Untuk panduan menjalankan skrip auto-start, lihat `START-APP.md`.

Lisensi: MIT
