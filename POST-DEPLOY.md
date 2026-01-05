# Post-Deployment Manual Steps

Setelah GitHub Actions selesai upload files via FTP, jalankan perintah ini di **cPanel Terminal**:

## 1. Login cPanel Terminal
- cPanel → **Terminal** (icon console)

## 2. Masuk ke direktori backend
```bash
cd ~/public_html/api-sihadirsmp.manawa.sch.id
```

## 3. Install dependencies
```bash
composer install --no-dev --optimize-autoloader
```

## 4. Cache configs (optimization)
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 5. Run migrations
```bash
php artisan migrate --force
```

## 6. Set permissions (jika perlu)
```bash
chmod -R 775 storage bootstrap/cache
```

## 7. Verifikasi
```bash
# Test API endpoint
curl http://api-sihadirsmp.manawa.sch.id/api/classes
```

---

**Note:** Steps ini hanya perlu dijalankan sekali setelah deployment pertama, atau jika ada perubahan di:
- Database schema (migrations)
- Config files
- Dependencies (composer.json)

Untuk update code biasa, FTP deploy saja sudah cukup.
