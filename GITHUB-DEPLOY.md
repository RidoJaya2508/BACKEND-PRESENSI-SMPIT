# Deployment via GitHub Actions

## Setup GitHub Repository

1. Buat repository baru di GitHub untuk backend
2. Push code:
   ```bash
   git init
   git add .
   git commit -m "initial commit"
   git branch -M main
   git remote add origin https://github.com/username/sihadir-backend.git
   git push -u origin main
   ```

3. Setup Secrets di GitHub → Settings → Secrets and variables → Actions:
   ```
   FTP_SERVER=ftp.manawa.sch.id
   FTP_USERNAME=api@api-sihadirsmp.manawa.sch.id
   FTP_PASSWORD=your_ftp_password
   SSH_USERNAME=cpanel_username
   SSH_PASSWORD=cpanel_password
   ```

## Auto-Deploy Workflow

Setiap push ke branch `main` akan otomatis:
1. Upload source code via FTP (exclude vendor/, .env)
2. SSH ke server
3. Run `composer install --no-dev --optimize-autoloader`
4. Cache configs: `php artisan config:cache`
5. Run migrations: `php artisan migrate --force`

## Manual Trigger

GitHub → Repository → Actions → Deploy Backend to cPanel → Run workflow
