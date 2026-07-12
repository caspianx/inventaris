# 🚀 Cara Menjalankan Aplikasi Otomatis (Tanpa Command Prompt)

Panduan mudah untuk menjalankan aplikasi Inventory tanpa perlu membuka Command Prompt/Terminal setiap kali.

---

## 🪟 UNTUK PENGGUNA WINDOWS

### Cara Paling Mudah: Double-Click File

1. **Letakkan `start-app.bat` di root proyek Laravel** (folder yang sama dengan file `artisan`).
2. **Double-click `start-app.bat`** — skrip akan menggunakan path file itu sendiri sebagai root proyek.
3. **Command Prompt akan terbuka dan menjalankan** `php artisan serve`.
4. **Buka browser** ke: http://localhost:8000

✅ **Selesai! Aplikasi sudah berjalan!**

---

### Cara Membuat Shortcut di Desktop (Opsional)

Jika ingin membuat shortcut di desktop agar lebih mudah diakses:

1. **Buka folder aplikasi** (`C:\xampp\htdocs\inventaris`)
2. **Klik kanan pada file `start-app.bat`**
3. **Pilih "Send to" → "Desktop (create shortcut)"**
4. **Shortcut akan muncul di desktop Anda**
5. **Sekarang Anda bisa double-click shortcut untuk jalankan aplikasi**

---

### Troubleshooting Windows

Jika skrip gagal, periksa hal-hal ini:

- Pastikan file `start-app.bat` berada di root proyek (sejajar `artisan`).
- Pastikan `php` ada di PATH atau anda menjalankan skrip dari XAMPP's command prompt.
- Jika `php` tidak ditemukan, jalankan XAMPP Control Panel dan gunakan PHP yang tersedia di folder XAMPP, atau tambahkan path PHP ke environment variable.

---

## 🐧 UNTUK PENGGUNA LINUX

### Langkah 1: Membuat Script Executable

```bash
cd ~/Desktop/inventaris  # sesuaikan path aplikasi Anda
chmod +x start-app.sh
```

### Langkah 2: Jalankan Script

```bash
./start-app.sh
```

Atau:

```bash
bash start-app.sh
```

✅ **Selesai! Aplikasi akan berjalan**

---

### Cara Membuat Launcher Desktop (Opsional)

Agar bisa double-click untuk menjalankan:

1. **Buka file manager**
2. **Navigasi ke folder aplikasi**
3. **Klik kanan pada `start-app.sh`**
4. **Pilih "Properties" atau "Make Executable"**
5. **Sekarang Anda bisa double-click untuk jalankan**

Atau buat file launcher:

```bash
cat > ~/Desktop/Inventory\ App.desktop << 'EOF'
[Desktop Entry]
Type=Application
Name=Inventory App
Exec=/path/to/aplikasi/start-app.sh
Icon=applications-system
Terminal=true
EOF

chmod +x ~/Desktop/Inventory\ App.desktop
```

---

## 🍎 UNTUK PENGGUNA macOS

### Langkah 1: Membuat Script Executable

Buka Terminal dan jalankan:

```bash
cd ~/Desktop/inventaris  # sesuaikan path aplikasi Anda
chmod +x start-app.sh
chmod +x start-app-mac.command
```

### Langkah 2: Jalankan dengan Double-Click

**Opsi A: Double-Click start-app-mac.command**

1. **Buka folder aplikasi**
2. **Double-click file `start-app-mac.command`**
3. **Aplikasi akan berjalan di Terminal otomatis**

**Opsi B: Dari Terminal**

```bash
./start-app.sh
```

atau

```bash
./start-app-mac.command
```

✅ **Aplikasi akan berjalan!**

---

### Cara Membuat App di Dock (Opsional)

Agar bisa di-pin di Dock:

1. **Buka Terminal**
2. **Buat executable app:**
   ```bash
   mkdir -p ~/Applications/InventoryApp.app/Contents/MacOS
   cp start-app.sh ~/Applications/InventoryApp.app/Contents/MacOS/
   chmod +x ~/Applications/InventoryApp.app/Contents/MacOS/start-app.sh
   ```

3. **Akses aplikasi dari Launchpad atau Finder**

---

## ⏰ SETUP OTOMATIS SAAT KOMPUTER RESTART (Advanced)

### Untuk Windows: Menggunakan Task Scheduler

Agar aplikasi otomatis jalan saat Windows startup:

1. **Tekan Windows + R**
2. **Ketik: `taskschd.msc`** dan tekan Enter
3. **Klik "Create Basic Task"** di sidebar kanan
4. **Isi Form:**
   - Name: "Inventory App Auto Start"
   - Description: "Jalankan Inventory App otomatis"
5. **Klik Next**
6. **Pilih "At log on"** dan klik Next
7. **Pilih "Start a program"** dan klik Next
8. **Isi Program/script:**
   ```
   C:\xampp\htdocs\inventaris\start-app.bat
   ```
9. **Klik Finish**

Sekarang aplikasi akan otomatis jalan saat Windows startup!

---

### Untuk Linux: Menggunakan Cron atau Systemd

**Opsi 1: Menggunakan Cron**

```bash
crontab -e
```

Tambahkan baris ini di akhir file:

```
@reboot /path/to/aplikasi/start-app.sh &
```

Simpan dan keluar. Aplikasi akan jalan otomatis saat sistem restart.

**Opsi 2: Menggunakan Systemd (Recommended)**

```bash
sudo nano /etc/systemd/system/inventory-app.service
```

Salin dan paste:

```ini
[Unit]
Description=Inventory App
After=network.target

[Service]
User=your_username
WorkingDirectory=/path/to/aplikasi
ExecStart=/path/to/aplikasi/start-app.sh
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

Kemudian jalankan:

```bash
sudo systemctl daemon-reload
sudo systemctl enable inventory-app
sudo systemctl start inventory-app
```

---

### Untuk macOS: Menggunakan LaunchAgent

1. **Buat file:**
   ```bash
   nano ~/Library/LaunchAgents/com.inventory.app.plist
   ```

2. **Salin dan paste:**
   ```xml
   <?xml version="1.0" encoding="UTF-8"?>
   <!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
   <plist version="1.0">
   <dict>
       <key>Label</key>
       <string>com.inventory.app</string>
       <key>ProgramArguments</key>
       <array>
           <string>/path/to/aplikasi/start-app.sh</string>
       </array>
       <key>RunAtLoad</key>
       <true/>
       <key>KeepAlive</key>
       <false/>
   </dict>
   </plist>
   ```

3. **Ganti `/path/to/aplikasi/` dengan path aplikasi Anda**
4. **Simpan (Ctrl+O, Enter, Ctrl+X)**
5. **Jalankan:**
   ```bash
   launchctl load ~/Library/LaunchAgents/com.inventory.app.plist
   ```

Aplikasi akan otomatis jalan saat Mac startup!

---

## 🛑 CARA MENGHENTIKAN APLIKASI

### Windows
1. Klik pada window Command Prompt
2. Tekan **Ctrl + C**
3. Ketik **Y** dan tekan Enter (jika diminta)

### Linux / macOS
1. Klik pada window Terminal
2. Tekan **Ctrl + C**

---

## ✅ Tips Penting

- ✅ **Jangan tutup window** saat aplikasi berjalan
- ✅ **Biarkan running di background** jika ingin gunakan komputer untuk hal lain
- ✅ **Aplikasi hanya berjalan lokal** (http://localhost:8000)
- ✅ **Data aman** tersimpan di database lokal
- ✅ **Bisa diakses dari komputer lain** dengan URL: `http://[IP-KOMPUTER]:8000`

---

## 📞 Butuh Bantuan?

Jika mengalami masalah, pastikan:

1. ✅ PHP terinstall dengan benar
2. ✅ Composer sudah dijalankan (`composer install`)
3. ✅ Database sudah dimigrasi (`php artisan migrate`)
4. ✅ Path folder aplikasi benar di file start script
5. ✅ Port 8000 tidak digunakan aplikasi lain

---

**Selamat! Aplikasi Anda sudah siap berjalan otomatis! 🎉**
