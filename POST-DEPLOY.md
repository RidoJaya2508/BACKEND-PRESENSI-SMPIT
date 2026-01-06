# Post-Deployment Manual Steps

Setelah GitHub Actions selesai upload files via FTP, jalankan perintah ini di **cPanel Terminal**:

## 1. Login cPanel Terminal
- cPanel → **Terminal** (icon console)

## 2. Masuk ke direktori backend
```bash
cd ~/public_html/api-sihadirsmp.manawa.sch.id
```

## 3. Set PHP Version (PENTING!)

**Server default PHP 7.4, Laravel 12 butuh PHP 8.2+**

**Option A: Gunakan EA-PHP 8.2 langsung di command:**
```bash
# Cek PHP version yang tersedia
ls /opt/cpanel/ea-php*/root/usr/bin/php

# Set alias untuk sesi terminal ini
alias php='/opt/cpanel/ea-php82/root/usr/bin/php'
alias composer='/opt/cpanel/ea-php82/root/usr/bin/php composer.phar'

# Verifikasi PHP version
php -v
# Harus tampil: PHP 8.2.x
```

**Option B: Set di .htaccess (permanent):**

Buat/edit file `.htaccess` di root direktori backend:
```bash
nano .htaccess
```

Tambahkan baris ini:
```apache
AddHandler application/x-httpd-ea-php82 .php
```

**Option C: Via cPanel MultiPHP Manager:**
- cPanel → **MultiPHP Manager**
- Cari domain: `api-sihadirsmp.manawa.sch.id`
- Select → Pilih **PHP 8.2** → Apply

---

## 4. Install dependencies

**Composer tidak ada di server, install manual:**

```bash
# Download dan install Composer (gunakan PHP 8.2!)
/opt/cpanel/ea-php82/root/usr/bin/php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
/opt/cpanel/ea-php82/root/usr/bin/php composer-setup.php
/opt/cpanel/ea-php82/root/usr/bin/php -r "unlink('composer-setup.php');"

# Verifikasi Composer terinstall
php composer.phar --version

# Install dependencies Laravel
php composer.phar install --no-dev --optimize-autoloader

# Jika muncul error "lock file is not up to date" atau "package not present in lock file":
php composer.phar update --no-dev --optimize-autoloader
```

**Note:** 
- Pastikan menggunakan PHP 8.2+ (sudah di-set di step 3)
- `composer install` = install dari lock file (untuk konsistensi)
- `composer update` = regenerate lock file dari composer.json (jika lock file outdated)
- File `composer.phar` akan tersimpan di direktori current dan bisa digunakan untuk update dependencies nanti.

## 5. Setup .env file

### 5.1. Cek Database yang Sudah Ada di cPanel

**Jika sudah import database sebelumnya:**

1. **cPanel → MySQL Databases**
2. Lihat di section **Current Databases** → catat nama database yang sudah ada
3. Lihat di section **Current Users** → catat username yang sudah dibuat
4. Pastikan user sudah di-assign ke database (di section **Add User To Database**)
5. **Jika user belum punya privileges:**
   - Cari database di **Modify Databases Privileges**
   - Pilih user → Klik **Manage**
   - Centang **ALL PRIVILEGES** → Save

**Jika belum ada database, buat baru:**

1. **Create New Database:**
   - Database Name: `sihadir` (akan jadi `manc5174_sihadir`)
   - Klik **Create Database**
2. **Create MySQL User:**
   - Username: `sihadir_user` (akan jadi `manc5174_sihadir_user`)
   - Password: (generate/buat password kuat)
   - Klik **Create User**
3. **Add User to Database:**
   - User: `manc5174_sihadir_user`
   - Database: `manc5174_sihadir`
   - Klik **Add** → **ALL PRIVILEGES** → **Make Changes**

### 5.2. Setup .env file

```bash
# Copy template production ke .env
cp .env.production .env

# Edit .env dan sesuaikan:
nano .env
```

**Update nilai-nilai berikut di .env:**
```env
APP_KEY=                          # Akan di-generate di step berikutnya
APP_ENV=production
APP_DEBUG=false
APP_URL=http://api-sihadirsmp.manawa.sch.id

# Database - SESUAIKAN dengan yang ADA di cPanel MySQL Databases!
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=manc5174_presensi_smpit   # Nama EXACT dari cPanel (dari error sebelumnya)
DB_USERNAME=manc5174_admin            # Username EXACT dari cPanel (dari error sebelumnya)
DB_PASSWORD=your_actual_password      # Password user database (tanyakan/reset jika lupa)

# Frontend URL
FRONTEND_URL=http://sihadir-smp.manawa.sch.id
SESSION_DOMAIN=api-sihadirsmp.manawa.sch.id
SANCTUM_STATEFUL_DOMAINS=sihadir-smp.manawa.sch.id

# Telegram
TELEGRAM_BOT_TOKEN=8569505953:AAE-g3a7Dzqsc2gySrmIvRz5AZCMNfeQpKE
TELEGRAM_BOT_USERNAME=Sihadirsmpbot
TELEGRAM_WEBHOOK_URL=http://api-sihadirsmp.manawa.sch.id/api/telegram/webhook
```

**Note:** Berdasarkan error sebelumnya, database adalah `manc5174_presensi_smpit` dan user adalah `manc5174_admin`. Pastikan:
- Password benar (jika lupa, reset di cPanel → MySQL Databases → Change Password)
- User `manc5174_admin` sudah di-assign ke database `manc5174_presensi_smpit` dengan ALL PRIVILEGES

**Generate APP_KEY:**
```bash
php artisan key:generate
```

### 5.3. Test Database Connection
```bash
# Clear config cache untuk reload .env
php artisan config:clear

# Test koneksi database
php artisan migrate:status

# Jika error "Access denied", cek:
# 1. DB_DATABASE, DB_USERNAME, DB_PASSWORD di .env sudah benar
# 2. User sudah di-assign ke database di cPanel MySQL Databases
# 3. User punya ALL PRIVILEGES
```

## 6. Cache configs (optimization)
```bash
php artisan config:cache
php artisan route:cache

# Skip view:cache jika error "View path not found" (resources/views kosong)
# php artisan view:cache
```

## 7. Run migrations
```bash
# Jika database kosong (belum ada tables):
php artisan migrate --force

# Jika database sudah ada (dari import sebelumnya):
php artisan migrate:status
# Jika sudah ada migrations, skip step ini
```

## 8. Set permissions (jika perlu)
```bash
chmod -R 775 storage bootstrap/cache
```

## 9. Verifikasi

**Jika muncul Error 500, cek log:**
```bash
# Cek Laravel error log
tail -20 storage/logs/laravel.log

# Cek PHP error log (jika ada)
tail -20 error_log

# Test database connection
php artisan tinker
# Di tinker ketik: DB::connection()->getPdo();
# Tekan Ctrl+D untuk keluar
```

**Common fixes Error 500:**
```bash
# 1. Fix permissions
chmod -R 775 storage bootstrap/cache
chown -R manc5174:manc5174 storage bootstrap/cache

# 2. Clear all cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 3. Regenerate cache
php artisan config:cache
php artisan route:cache
```

**Test API endpoint:**
```bash
curl http://api-sihadirsmp.manawa.sch.id/api/classes
```

---

**Note:** Steps ini hanya perlu dijalankan sekali setelah deployment pertama, atau jika ada perubahan di:
- Database schema (migrations)
- Config files
- Dependencies (composer.json)

Untuk update code biasa, FTP deploy saja sudah cukup.
