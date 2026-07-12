#!/bin/bash
#########################################
# Inventory App Auto-Start Script (Linux)
#########################################
# Gunakan: bash start-app.sh atau ./start-app.sh
# Jangan lupa: chmod +x start-app.sh

# Set warna text
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Tentukan path aplikasi (script directory = project root)
APP_PATH="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

echo ""
echo "========================================="
echo "    INVENTORY APP - AUTO START (Linux)"
echo "========================================="
echo ""

# Check apakah folder aplikasi ada
if [ ! -d "$APP_PATH" ]; then
    echo -e "${RED}ERROR: Folder aplikasi tidak ditemukan!${NC}"
    echo "Path: $APP_PATH"
    exit 1
fi

# Check apakah artisan file ada
if [ ! -f "$APP_PATH/artisan" ]; then
    echo -e "${RED}ERROR: File artisan tidak ditemukan!${NC}"
    echo "Pastikan script berada di root proyek Laravel atau jalankan dari folder proyek yang benar."
    exit 1
fi

echo -e "${GREEN}✓ Folder aplikasi ditemukan${NC}"
echo ""
echo "Aplikasi sedang dimulai..."
echo -e "${YELLOW}Tunggu sampai muncul pesan 'Server running'${NC}"
echo ""
echo "Tekan Ctrl+C untuk menghentikan aplikasi"
echo ""
echo "========================================="
echo ""

# Jalankan Laravel server
cd "$APP_PATH"
php artisan serve

# Jika gagal
if [ $? -ne 0 ]; then
    echo ""
    echo -e "${RED}ERROR: Gagal menjalankan aplikasi!${NC}"
    echo "Pastikan:"
    echo "1. PHP 8.2+ sudah terinstall"
    echo "2. Composer sudah terinstall"
    echo "3. Dependencies sudah diinstall (jalankan: composer install)"
    echo ""
    read -p "Tekan Enter untuk keluar..."
    exit 1
fi
