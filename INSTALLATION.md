# 📖 Panduan Instalasi Lengkap - Inventory App

**Panduan Mudah untuk Pemula - Tidak Perlu Pengalaman Programming!**

Aplikasi ini adalah sistem untuk mencatat barang/stok toko Anda. Panduan ini akan memandu Anda langkah demi langkah untuk menginstallnya di komputer.

---

## ✅ Apa Yang Perlu Anda Siapkan

**Sebelum mulai, pastikan Anda punya:**

- ✅ Komputer Windows, Mac, atau Linux
- ✅ Koneksi Internet (untuk download tools)
- ✅ Ruang di hard drive minimal 1 GB (ukuran folder kosong kurang lebih)
- ✅ 30-60 menit waktu luang
- ✅ Notepad atau text editor (seperti Notepad bawaan Windows)

**Yang Akan Kami Install (Jangan khawatir, otomatis semua):**
- 🔧 PHP = Bahasa program untuk membuat aplikasi berjalan
- 📦 Composer = Alat bantu mengunduh komponen aplikasi
- 📱 Node.js = Alat bantu membuat tampilan lebih cantik
- 📊 Database = Tempat penyimpanan data barang Anda

Semua tools ini gratis dan aman digunakan!

---

## 🪟 PANDUAN INSTALASI UNTUK WINDOWS (Yang Paling Mudah)

Jika Anda pakai Windows, ini cara yang paling mudah! Kami akan menggunakan XAMPP yang sudah mengemas semua tools yang dibutuhkan jadi tinggal klik-klik saja.

### 📥 LANGKAH 1: Download XAMPP (5 menit)

**XAMPP adalah:** Seperti kotak berisikan semua alat yang kita butuhkan dalam satu tempat.

1. **Buka browser** (Chrome, Firefox, Edge, dll)
2. **Kunjungi:** https://www.apachefriends.org/
3. **Anda akan melihat tombol download**, pilih yang **Windows** (bukan Mac atau Linux)
4. Cari yang versi **8.2 atau lebih besar** di bagian PHP
5. Klik tombol **"Download"** dan tunggu file selesai diunduh (sekitar 300 MB, 5-10 menit tergantung internet)

✅ **Selesai! File biasanya tersimpan di folder "Downloads"**

---

### 💾 LANGKAH 2: Install XAMPP (3 menit)

1. **Buka folder Downloads** dan temukan file `xampp-installer-x.x.x` (ada nama versi di belakang)
2. **Double-klik file tersebut** (atau klik kanan → Open)
3. Jika ada pertanyaan "Apakah Anda ingin mengizinkan perubahan?", klik **"Yes"**
4. **Jendela instalasi akan terbuka**, klik tombol **"Next"** sampai selesai
5. Saat diminta memilih komponen, pastikan yang di-centang:
   - ✅ Apache
   - ✅ MySQL
   - ✅ PHP (versi 8.2 atau lebih baru)
   - ✅ PhpMyAdmin (opsional tapi berguna)
6. Terus klik **"Next"** hingga ada tombol **"Finish"**, lalu klik **"Finish"**

✅ **Selesai! XAMPP sudah terinstall di komputer Anda**

---

### ▶️ LANGKAH 3: Jalankan XAMPP (1 menit)

1. **Cari dan buka** "XAMPP Control Panel" dari menu Windows (atau double-klik XAMPP shortcut jika ada)
2. **Jendela XAMPP akan muncul**, Anda akan melihat daftar services:
   - Apache
   - MySQL
   - PHP

3. **Klik tombol "Start" untuk Apache dan MySQL**

Hasilnya akan terlihat seperti ini:
```
Apache     [Start]  ← Klik Start, nanti akan hijau
MySQL      [Start]  ← Klik Start, nanti akan hijau
PHP        (tidak perlu di-start)
```

✅ **Jika sudah berwarna HIJAU dan tulisannya "Running", berarti berhasil!**

---

### 📱 LANGKAH 4: Install Node.js (3 menit)

**Node.js adalah:** Alat yang membuat tampilan aplikasi lebih cantik dan responsif.

1. **Buka browser** dan kunjungi: https://nodejs.org/
2. **Anda akan melihat 2 tombol download:**
   - Pilih yang **"LTS"** (Long Term Support = lebih stabil)
   - **Untuk Windows**, ambil yang **.msi**
3. Klik tombol download dan tunggu file selesai (sekitar 30 MB, 1-2 menit)
4. **Double-klik file `node-vXX.msi`** yang sudah diunduh
5. **Setup wizard akan muncul**, klik **"Next"** berkali-kali
6. Pastikan ada centang di "Add to PATH" (akan otomatis di-centang)
7. Klik **"Install"** dan tunggu selesai
8. Klik **"Finish"**

✅ **Selesai! Node.js sudah terinstall**

---

### 🔧 LANGKAH 5: Install Composer (2 menit)

**Composer adalah:** Seperti app store, tempat kita mengunduh komponen-komponen siap pakai untuk aplikasi kita.

1. **Buka browser** dan kunjungi: https://getcomposer.org/download/
2. **Cari tombol "Composer Setup"** dan klik untuk Windows installer
3. Klik download dan tunggu file selesai (sekitar 5 MB)
4. **Double-klik file `Composer-Setup.exe`**
5. Setup wizard akan muncul dan **secara otomatis akan mendeteksi PHP Anda**
6. Klik **"Next"** sampai bertemu setup screen, lalu klik **"Install"**
7. Tunggu proses selesai, klik **"Finish"**

✅ **Selesai! Semua tools sudah terinstall!**

---

### 📂 LANGKAH 6: Download Aplikasi (2 menit)

Sekarang kita akan mendownload aplikasi Inventory-nya.

**Opsi A: Menggunakan Git (Jika sudah terinstall)**
```
Abaikan langkah ini jika belum pernah dengar Git
```

**Opsi B: Download File Langsung (RECOMMENDED untuk pemula)**

1. **Buka browser** dan kunjungi: https://github.com/caspianx/inventaris
2. **Cari tombol hijau yang bertulisan "Code"** di atas kanan
3. **Klik dan pilih "Download ZIP"**
4. File akan diunduh (ukuran sekitar 50-100 MB, tunggu 1-2 menit)
5. **Extract ZIP file:**
   - Klik kanan pada file ZIP yang sudah diunduh
   - Pilih "Extract All..."
   - Pilih lokasi `C:\xampp\htdocs` (folder tempat XAMPP menyimpan aplikasi web)
   - Klik "Extract"

✅ **Folder "inventaris" sudah ada di `C:\xampp\htdocs\`**

---

### ⚙️ LANGKAH 7: Setup Aplikasi (5 menit)

Sekarang kita akan mengatur aplikasi agar siap digunakan.

1. **Buka Command Prompt** (CMD):
   - Tekan **Windows + R**
   - Ketik: `cmd`
   - Tekan Enter

2. **Masuk ke folder aplikasi**, ketik baris ini dan tekan Enter:
   ```
   cd C:\xampp\htdocs\inventaris
   ```
   (Jika folder nama berbeda, composen namanya)

3. **Ketik perintah ini dan tekan Enter:**
   ```
   composer install
   ```
   Tunggu proses berjalan (akan melihat banyak text bergerak, 2-5 menit)

4. **Setelah selesai, ketik:**
   ```
   npm install
   ```
   Tunggu lagi (1-3 menit)

✅ **Jika selesai tanpa error merah, berarti berhasil!**

---

### 🔑 LANGKAH 8: Setup File Konfigurasi (3 menit)

1. **Buka folder** `C:\xampp\htdocs\inventaris` menggunakan File Explorer
2. **Cari file bernama `.env.example`** (jika tidak terlihat, tekan Ctrl+H untuk melihat hidden files)
3. **Buat copy file tersebut** (klik kanan → Copy)
4. **Paste di folder yang sama** (klik kanan → Paste)
5. **Rename hasil copy menjadi `.env`** (klik kanan file → Rename → ketik `.env` → Enter)
6. **Double-click file `.env`** untuk membukanya dengan text editor
7. **Cari baris ini:**
   ```
   APP_KEY=
   ```
8. **Di Command Prompt, ketik:**
   ```
   php artisan key:generate
   ```
   Tunggu sebentar, akan melihat pesan "Application key set successfully"

9. **File `.env` akan otomatis ter-update**, sekarang Anda bisa tutup file tersebut

✅ **Setup konfigurasi selesai!**

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
