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

**Keamanan & APP_KEY**

- Jangan commit `/.env` atau nilai `APP_KEY` ke repositori publik.
- Setelah clone, salin `.env.example` ke `.env`, install dependensi, lalu generate key:

```bash
cp .env.example .env
composer install
php artisan key:generate
```

- Jika menggunakan PowerShell (Windows):

```powershell
Copy-Item .env.example .env
composer install
php artisan key:generate
```

**Otomatisasi (opsional)**

Untuk otomatis generate `APP_KEY` saat `composer install`, tambahkan ke bagian `scripts` pada `composer.json`:

```json
"post-install-cmd": [
	"@php artisan key:generate --ansi"
]
```

Catatan: pastikan file `.env` sudah dibuat (mis. dengan menyalin `.env.example`) sebelum menjalankan skrip ini.
