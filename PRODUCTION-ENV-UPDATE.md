# Update Environment Variables di Production Server

## ⚠️ PENTING: Update Manual Required

File `.env.production` tidak di-commit ke Git untuk keamanan. Anda perlu update manual di server production.

## Langkah Update di Server Production

### 1. Login ke cPanel / SSH Server
- URL: `api-sihadirsmp.manawa.sch.id`
- Masuk ke File Manager atau SSH

### 2. Edit file `.env` di root folder backend
Lokasi: `/home/[username]/public_html/.env`

### 3. Update Konfigurasi Berikut:

```env
# Frontend URL
FRONTEND_URL=https://sihadir-smp.manawa.sch.id

# CORS
SANCTUM_STATEFUL_DOMAINS=sihadir-smp.manawa.sch.id

# Telegram Bot Configuration
TELEGRAM_BOT_TOKEN=8569505953:AAE-g3a7Dzqsc2gySrmIvRz5AZCMNfeQpKE
TELEGRAM_WEBHOOK_URL=https://api-sihadirsmp.manawa.sch.id/api/telegram/webhook

# Session Configuration
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_DOMAIN=.manawa.sch.id
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

### 4. Setelah Update File .env

Jalankan command berikut di terminal/SSH:

```bash
# Clear cache
php artisan config:clear
php artisan cache:clear

# Set webhook Telegram ke production URL
php artisan telegram:set-webhook
```

### 5. Verifikasi Webhook Telegram

Test webhook dengan curl:
```bash
curl https://api-sihadirsmp.manawa.sch.id/api/telegram/webhook
```

Atau cek via Telegram API:
```
https://api.telegram.org/bot8569505953:AAE-g3a7Dzqsc2gySrmIvRz5AZCMNfeQpKE/getWebhookInfo
```

## Checklist ✅

- [ ] Update `FRONTEND_URL` ke HTTPS
- [ ] Update `TELEGRAM_WEBHOOK_URL` ke HTTPS  
- [ ] Set `SESSION_SECURE_COOKIE=true`
- [ ] Set `SESSION_DOMAIN=.manawa.sch.id`
- [ ] Clear config cache: `php artisan config:clear`
- [ ] Set webhook: `php artisan telegram:set-webhook`
- [ ] Verifikasi webhook info dari Telegram API

## Troubleshooting

**Jika webhook tidak berfungsi:**
1. Pastikan SSL/HTTPS sudah aktif di domain
2. Cek webhook info: `/getWebhookInfo`
3. Cek log Laravel: `storage/logs/laravel.log`
4. Pastikan route `/api/telegram/webhook` accessible dari internet

**Test webhook manual:**
```bash
curl -X POST https://api-sihadirsmp.manawa.sch.id/api/telegram/webhook \
  -H "Content-Type: application/json" \
  -d '{"message":{"text":"test"}}'
```
