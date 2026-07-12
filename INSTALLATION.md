# 📖 Panduan Instalasi Lengkap - Inventory App

**Panduan Mudah untuk Pemula - Tidak Perlu Pengalaman Programming!**

Aplikasi ini adalah sistem untuk mencatat barang/stok toko Anda. Panduan ini akan memandu Anda langkah demi langkah untuk menginstallnya di komputer.

---

PANDUAN RINGKAS INSTALASI (diperbarui)

Dokumentasi ini disederhanakan untuk mencerminkan alur instalasi terbaru:

- `Create Database` di halaman instalasi (`/install`) hanya membuat database.
- Menu migrasi (`/install/migrate-existing`) menjalankan migrasi struktur tabel saja (tanpa seeder).
- Setelah migrasi berhasil, UI akan mengarahkan kembali ke halaman instalasi untuk membuat admin pertama.

Langkah cepat:

1. Pastikan Anda punya PHP (8.2+), Composer, dan Node.js terinstall, serta database server (MySQL/MariaDB) berjalan.
2. Salin `.env.example` ke `.env` atau gunakan web installer untuk memasukkan konfigurasi DB.
3. Jalankan `composer install` dan `npm install` jika Anda bekerja dari source.
4. Jalankan server lokal:

```bash
php artisan serve
```

5. Buka browser ke `http://127.0.0.1:8000/install` dan ikuti alur:
   - Isi konfigurasi database (atau biarkan `.env` terisi), klik **Create Database** untuk membuat DB.
   - Setelah DB dibuat, Anda akan diarahkan ke halaman migrasi; klik **Run Migrations** (struktur saja).
   - Setelah migrasi selesai, Anda kembali ke halaman instalasi dan akan melihat form pembuatan admin pertama.

6. Isi form admin pertama dan submit.

Catatan: Jika Anda lebih suka melakukan semuanya lewat CLI, langkah yang setara adalah:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force   # hanya jika Anda yakin
npm install
npm run build
php artisan serve
```

Jika Anda menjalankan di lingkungan XAMPP, pastikan Apache dan MySQL aktif sebelum mengakses aplikasi.

Troubleshooting singkat:
- Jika gagal akses database, periksa kredensial di `.env`.
- Jika migrasi gagal karena tabel migrations tidak ada, buka menu migrasi di UI atau jalankan `php artisan migrate:install`.

---

### 📊 LANGKAH 9: Setup Database (2 menit)

Database adalah tempat penyimpanan semua data barang Anda (seperti spreadsheet tapi lebih canggih).

1. **Di Command Prompt, ketik:**
   ```
   php artisan migrate
   ```

2. Tunggu proses (akan melihat banyak text seperti "Migrating: 2024_01_01_000000_create_tables" dll)
3. Saat selesai akan terlihat: "Database seeded successfully" atau semacamnya

✅ **Database sudah siap!**

---

### 🎨 LANGKAH 10: Build Tampilan Aplikasi (2 menit)

1. **Di Command Prompt, ketik:**
   ```
   npm run build
   ```

2. Tunggu proses selesai (akan melihat text berubah-ubah)

✅ **Tampilan aplikasi sudah siap!**

---

### 🚀 LANGKAH 11: Jalankan Aplikasi (1 menit)

Sekarang saatnya melihat aplikasi Anda berjalan!

1. **Di Command Prompt, ketik:**
   ```
   php artisan serve
   ```

2. Tunggu sampai muncul pesan seperti:
   ```
   Starting Laravel development server: http://127.0.0.1:8000
   ```

3. **Buka browser** dan kunjungi: **http://localhost:8000**

🎉 **SELAMAT! Aplikasi sudah berjalan! Anda akan melihat halaman dashboard aplikasi.**

---

### ✅ Verifikasi Instalasi Berhasil

Cek hal-hal ini:

- ✅ Browser menampilkan halaman aplikasi (tidak ada error)
- ✅ Menu dan tombol-tombol terlihat dengan baik
- ✅ Tidak ada tulisan merah (error) di halaman
- ✅ Command Prompt tidak menunjukkan error messages

**Jika semua ✅, selamat! Instalasi berhasil 100%!**

---

## 🐧 PANDUAN INSTALASI UNTUK LINUX (Ubuntu/Debian)

**Untuk pengguna Linux yang tidak paham programming:**

Tenang! Prosesnya sama seperti Windows, hanya command-nya yang sedikit berbeda. Ikuti langkah-langkah ini dengan seksama.

### Langkah 1: Buka Terminal

Tekan kombinasi tombol **Ctrl + Alt + T** pada keyboard Anda. Jendela terminal akan terbuka.

### Langkah 2: Copy-Paste Perintah Ini Satu Per Satu

Dalam terminal, salin perintah berikut, paste ke terminal, lalu tekan Enter. Tunggu sampai selesai sebelum menjalankan perintah berikutnya.

**Perintah 1 - Update sistem:**
```bash
sudo apt update && sudo apt upgrade -y
```
*Apa ini: Mengupdate daftar software yang tersedia di komputer Anda*

**Perintah 2 - Install PHP dan tools:**
```bash
sudo apt install -y php8.3 php8.3-cli php8.3-mysql php8.3-sqlite3 php8.3-bcmath php8.3-curl php8.3-mbstring php8.3-xml composer nodejs npm git
```
*Apa ini: Menginstall semua tools yang kita butuhkan sekaligus*

### Langkah 3: Download Aplikasi

```bash
cd ~/Desktop
git clone https://github.com/caspianx/inventaris.git
cd inventaris
```

### Langkah 4: Install Komponen-Komponen Aplikasi

```bash
composer install
npm install
```

### Langkah 5: Setup Konfigurasi

```bash
cp .env.example .env
php artisan key:generate
```

### Langkah 6: Setup Database & Build

```bash
php artisan migrate
npm run build
```

### Langkah 7: Jalankan Aplikasi

```bash
php artisan serve
```

Buka browser ke: **http://localhost:8000**

✅ **Selesai!**

---

## 🍎 PANDUAN INSTALASI UNTUK macOS

**Untuk pengguna Mac yang tidak paham programming:**

Prosesnya mirip dengan Linux. Ikuti langkah-langkah ini:

### Langkah 1: Install Homebrew (Jika belum punya)

Homebrew adalah aplikasi untuk menginstall software di Mac dengan mudah.

Buka **Terminal** (tekan Cmd + Space, ketik "Terminal", tekan Enter)

Copy-paste perintah ini:
```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

Tekan Enter dan tunggu selesai.

### Langkah 2: Install Tools

Copy-paste satu per satu ke Terminal dan tekan Enter setiap kali:

```bash
brew install php nodejs composer git
```

### Langkah 3: Download Aplikasi

```bash
cd ~/Desktop
git clone https://github.com/caspianx/inventaris.git
cd inventaris
```

### Langkah 4: Install Komponen

```bash
composer install
npm install
```

### Langkah 5: Setup & Database

```bash
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
```

### Langkah 6: Jalankan

```bash
php artisan serve
```

Buka browser: **http://localhost:8000**

✅ **Selesai!**

---

## 🔧 SETELAH INSTALASI SELESAI

### Troubleshooting (Kalau Ada Masalah)

#### ❌ Error: "PHP version must be 8.2"

**Artinya:** PHP yang terinstall versinya kurang baru

**Cara Perbaiki:**
- Windows: Download XAMPP dengan PHP 8.2+ dan reinstall
- Linux: `sudo apt install php8.2`
- Mac: `brew install shivammathur/php/php@8.2`

---

#### ❌ Error: "No application key"

**Artinya:** File konfigurasi belum lengkap

**Cara Perbaiki:**
```
Di Command Prompt/Terminal ketik:
php artisan key:generate
```

---

#### ❌ Error: "Could not find driver"

**Artinya:** Database tidak terbaca

**Cara Perbaiki:**
```
Di Command Prompt/Terminal ketik:
php artisan migrate --force
```

---

#### ❌ Error: "npm not found"

**Artinya:** Node.js belum terinstall dengan benar

**Cara Perbaiki:**
- Windows: Download dan install ulang dari nodejs.org
- Linux: `sudo apt install nodejs npm`
- Mac: `brew install node`

---

#### ❌ Halaman tidak bisa diakses di http://localhost:8000

**Artinya:** Server belum berjalan

**Cara Perbaiki:**
1. Pastikan Command Prompt/Terminal masih terbuka dan menunjukkan "Server running..."
2. Jika tidak, ketik: `php artisan serve`
3. Tunggu sampai muncul "Server running..." kemudian buka browser

---

#### ❌ Aplikasi berjalan tapi lambat

**Artinya:** Konfigurasi perlu dioptimalkan

**Cara Perbaiki:**
```
Ketik di Command Prompt/Terminal:
php artisan optimize
php artisan config:cache
```

---

### Hal-Hal Penting Untuk Diingat

✅ **Jangan tutup Command Prompt/Terminal** saat aplikasi berjalan. Jika ditutup, aplikasi akan berhenti.

✅ **Jika ingin menghentikan aplikasi**, tekan **Ctrl + C** di Command Prompt/Terminal.

✅ **Untuk menjalankan lagi**, ketik `php artisan serve` di Command Prompt/Terminal.

---

## 📖 BAGAIMANA CARA MENGGUNAKAN APLIKASI?

### Halaman Utama

Setelah instalasi selesai dan aplikasi berjalan, Anda akan melihat:

| Menu | Fungsi |
|------|--------|
| **Master Barang** | Daftar barang/stok toko Anda |
| **Penjualan** | Input transaksi penjualan (seperti kasir) |
| **Laporan** | Laporan stok, penjualan, dll |
| **Pengaturan** | Ubah nama toko, user, dll |

### Login

Jika ada halaman login:
- **Email**: `admin@example.com`
- **Password**: Sesuai yang Anda buat saat instalasi

---

## ❓ PERTANYAAN YANG SERING DIAJUKAN

**T: Berapa lama instalasi memakan waktu?**
J: Sekitar 30-60 menit (tergantung kecepatan internet)

**T: Apakah perlu koneksi internet untuk menjalankan aplikasi setelah instalasi?**
J: Tidak! Setelah terinstall, aplikasi bisa berjalan offline.

**T: Bagaimana jika saya lupa password?**
J: Hubungi admin atau reset database dengan perintah `php artisan migrate:fresh`

**T: Bisakah dua orang menggunakan aplikasi ini bersamaan?**
J: Ya, asalkan akses dari komputer yang berbeda dengan URL: `http://[IP-KOMPUTER]:8000`

**T: Apakah datanya aman?**
J: Ya, data disimpan di database lokal. Asal komputer aman, data Anda aman.

**T: Bagaimana jika komputer dimatikan?**
J: Data aman tersimpan. Tinggal jalankan `php artisan serve` lagi untuk menghidupkan aplikasinya.

---

## 📞 BUTUH BANTUAN?

Jika masih ada masalah setelah mengikuti semua langkah:

1. **Baca file** `storage/logs/laravel.log` (berisi catatan error detail)
2. **Cek ulang** semua langkah instalasi di atas
3. **Hubungi developer** melalui: https://github.com/caspianx/inventaris/issues

---

**Status Instalasi: ✅ Lengkap dan Siap Pakai!**
