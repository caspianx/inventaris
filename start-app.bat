@echo off
REM ========================================
REM Inventory App Auto-Start Script (Windows)
REM ========================================
REM Script ini otomatis menjalankan aplikasi
REM Letakkan di desktop atau folder manapun
REM Double-click untuk menjalankan

setlocal enabledelayedexpansion

REM Tentukan path aplikasi (sesuaikan jika berbeda)
set APP_PATH=C:\xampp\htdocs\inventaris

REM Check apakah folder aplikasi ada
if not exist "%APP_PATH%" (
    echo.
    echo ERROR: Folder aplikasi tidak ditemukan!
    echo Path yang dicari: %APP_PATH%
    echo.
    echo Silakan edit script ini dan ubah APP_PATH sesuai lokasi aplikasi Anda
    echo.
    pause
    exit /b 1
)

REM Change ke folder aplikasi
cd /d "%APP_PATH%"

REM Clear screen
cls

REM Tampilkan welcome message
echo.
echo ========================================
echo     INVENTORY APP - AUTO START
echo ========================================
echo.
echo Aplikasi sedang dimulai...
echo Tunggu sampai muncul pesan "Server running"
echo.
echo Jangan tutup window ini saat aplikasi berjalan!
echo.
echo Tekan Ctrl+C untuk menghentikan aplikasi
echo.
echo ========================================
echo.

REM Jalankan Laravel server
php artisan serve

REM Jika terjadi error
if errorlevel 1 (
    echo.
    echo ERROR: Gagal menjalankan aplikasi!
    echo Pastikan:
    echo 1. PHP sudah terinstall
    echo 2. Composer sudah terinstall
    echo 3. Folder aplikasi benar
    echo.
    pause
    exit /b 1
)

pause
