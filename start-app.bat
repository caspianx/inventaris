@echo off
REM ========================================
REM Inventory App Auto-Start Script (Windows)
REM ========================================
REM Script ini otomatis menjalankan aplikasi
REM Letakkan di desktop atau folder manapun
REM Double-click untuk menjalankan

setlocal enabledelayedexpansion

REM Change to script directory (assumes the script lives in the project root)
cd /d "%~dp0"

REM Current folder is project root now

REM Check apakah artisan ada
if not exist "artisan" (
    echo.
    echo ERROR: Tidak menemukan file artisan di folder ini.
    echo Pastikan Anda menempatkan start-app.bat di root proyek Laravel.
    echo.
    pause
    exit /b 1
)

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

REM Jalankan Laravel server (port default 8000)
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
