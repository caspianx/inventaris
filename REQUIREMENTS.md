# Spesifikasi Minimal - Inventory App Laravel

Dokumentasi persyaratan minimum untuk menjalankan aplikasi Inventory Management System berbasis Laravel 12.

---

## 📋 Sistem Operasi

- **Windows** 10 / 11 (recommended)
- **Linux** (Ubuntu 20.04 LTS atau lebih baru)
- **macOS** 10.15 atau lebih baru

---

## 💻 Persyaratan Server

### PHP
- **Versi Minimum**: PHP 8.2 atau lebih baru (tested dengan PHP 8.2 & 8.3)
- **Recommended**: PHP 8.3+
- **Extensions yang Diperlukan**:
  - `bcmath`
  - `ctype`
  - `fileinfo`
  - `json`
  - `mbstring`
  - `openssl`
  - `pdo`
  - `tokenizer`
  - `xml`
  - `curl`

### Node.js (untuk Asset Building)
- **Versi Minimum**: Node.js 18.x LTS atau lebih baru
- **npm**: 9.x atau lebih baru (disertakan dengan Node.js)

### Database
- **SQLite** (default, tidak perlu setup tambahan)
- **MySQL** 8.0+ (optional, jika ingin menggunakan MySQL)
- **MariaDB** 10.5+ (optional, alternatif MySQL)

---

## 📦 Persyaratan Software

### Composer
- **Versi**: 2.x atau lebih baru
- [Download Composer](https://getcomposer.org/)

### Git (opsional, untuk version control)
- **Versi**: 2.x atau lebih baru
- [Download Git](https://git-scm.com/)

---

## 🛠️ Tools & Environment Setup

### Windows Users (Recommended Stack)
1. **XAMPP** (Apache + PHP + MySQL)
   - Download: https://www.apachefriends.org/
   - Pilih versi dengan PHP 8.2 atau lebih baru

2. **Visual Studio Code**
   - Download: https://code.visualstudio.com/
   - Extensions: Laravel, Blade, PHP Intelephense

### Linux/macOS Users
```bash
# macOS (dengan Homebrew)
brew install php node composer

# Ubuntu/Debian
sudo apt update
sudo apt install php8.2 php8.2-cli php8.2-mysql php8.2-sqlite3 \
  php8.2-bcmath php8.2-curl php8.2-mbstring php8.2-xml \
  nodejs npm composer git
```

---

## 📋 Dependency Stack

### Backend (PHP/Laravel)
| Package | Versi | Kegunaan |
|---------|-------|----------|
| Laravel Framework | ^12.8 | Framework utama |
| Laravel Tinker | ^3.0 | Interactive shell |
| PHPOffice/PHPSpreadsheet | ^5.8 | Export Excel (opsional) |

### Frontend
| Package | Versi | Kegunaan |
|---------|-------|----------|
| Vite | ^8.0 | Build tool |
| Tailwind CSS | ^4.0 | CSS Framework |
| Bootstrap Icons | - | Icon library (via CDN) |

### Development Tools
| Package | Kegunaan |
|---------|----------|
| PHPUnit | Testing framework |
| Mockery | Mocking library |
| Laravel Pint | Code formatting |
| Faker | Generate dummy data |

---

## 🚀 Quick Start Installation

### 1. Clone Repository
```bash
git clone https://github.com/caspianx/inventaris.git
cd inventaris
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Setup Environment
```bash
# Copy .env file
cp .env.example .env

# Generate app key
php artisan key:generate
```

### 4. Database Setup
```bash
# Run migrations (SQLite akan otomatis create)
php artisan migrate

# (Optional) Seed dummy data
php artisan db:seed
```

### 5. Build Assets
```bash
# Production build
npm run build

# Development mode (dengan hot reload)
npm run dev
```

### 6. Run Application
```bash
# Start Laravel development server
php artisan serve

# Server akan berjalan di: http://localhost:8000
```

**Atau gunakan script setup otomatis:**
```bash
composer run-script setup
```

---

## 📊 Browser Compatibility

| Browser | Versi Minimum | Status |
|---------|---------------|--------|
| Chrome | 90+ | ✅ Fully Supported |
| Firefox | 88+ | ✅ Fully Supported |
| Safari | 14+ | ✅ Fully Supported |
| Edge | 90+ | ✅ Fully Supported |

---

## 💾 Storage Requirements

| Komponen | Ukuran Minimal | Kegunaan |
|----------|----------------|----------|
| Source Code | ~500 MB | Aplikasi utama + vendor |
| Database (SQLite) | 1-10 MB | Data aplikasi |
| Upload Files | 100-500 MB | Barcode, logo, receipts |
| **Total** | **~1 GB** | Kebutuhan minimal |

---

## 🔐 Security Requirements

- ✅ HTTPS (untuk production)
- ✅ Environment variables (.env) tidak di-commit
- ✅ Database credentials aman
- ✅ File permissions yang tepat:
  - `storage/` → writable (755)
  - `bootstrap/cache/` → writable (755)
  - `public/barcodes/` → writable (755)

---

## ⚡ Performance Recommendations

### Minimum Setup
- **RAM**: 2 GB
- **CPU**: 2 cores
- **Storage**: 10 GB

### Recommended Setup (Production)
- **RAM**: 4+ GB
- **CPU**: 4+ cores
- **Storage**: 50+ GB (untuk historical data)
- **Disk Type**: SSD (untuk faster queries)

### Database Optimization
```bash
# Run optimization commands
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🆘 Troubleshooting

### Error: "PHP version must be ^8.2"
```bash
# Check PHP version
php -v

# Update PHP ke versi 8.2+
# XAMPP: Update atau gunakan versi terbaru
# Linux: sudo apt install php8.2
```

### Error: "Module not found (Node.js)"
```bash
# Clear cache dan reinstall
rm -rf node_modules package-lock.json
npm install
```

### Database Connection Error
```bash
# Check database configuration
php artisan tinker
# Di Tinker: DB::connection()->getPdo();
```

### Permission Denied on storage/
```bash
# Linux/macOS
chmod -R 755 storage bootstrap/cache

# Windows: Run as Administrator or set folder properties
```

---

## 📞 Support & Documentation

- **Laravel Docs**: https://laravel.com/docs/12.x
- **GitHub Repository**: https://github.com/caspianx/inventaris
- **Report Issues**: https://github.com/caspianx/inventaris/issues

---

## 📝 License

MIT License - Silakan gunakan untuk keperluan komersial maupun non-komersial.

---

**Last Updated**: 2026-07-08
**Version**: Laravel 12 + PHP 8.2+
