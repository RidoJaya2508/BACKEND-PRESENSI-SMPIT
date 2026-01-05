# Quick Git Setup & Push Backend

## Cara 1: Via Git Bash Terminal (Recommended)

1. Buka **Git Bash** terminal di VS Code (atau Git Bash app)
2. Run script:
   ```bash
   cd /c/Users/L380/Documents/SKRIPSI/BACKEND-PRESENSI-SMPIT
   bash git-push.sh
   ```

## Cara 2: Manual Commands

### Step 1: Initialize Git Repository

```bash
cd /c/Users/L380/Documents/SKRIPSI/BACKEND-PRESENSI-SMPIT
git init
```

### Step 2: Add All Files

```bash
git add .
```

### Step 3: Commit Changes

```bash
git commit -m "feat: setup GitHub Actions auto-deploy + production config

- Add .github/workflows/deploy.yml for auto-deployment
- Add .env.production template for production setup
- Add GITHUB-DEPLOY.md documentation
- Configure CORS for production domain
- Setup Telegram webhook for production
"
```

### Step 4: Create GitHub Repository

1. Buka https://github.com/new
2. Repository name: `sihadir-backend`
3. Visibility: **Private** (recommended)
4. **JANGAN** centang "Initialize with README"
5. Click **Create repository**

### Step 5: Connect to GitHub

```bash
# Ganti 'username' dengan GitHub username Anda
git remote add origin https://github.com/username/sihadir-backend.git
```

### Step 6: Push to GitHub

```bash
git branch -M main
git push -u origin main
```

## Verifikasi

Setelah push berhasil:
1. Buka repository di GitHub
2. Pastikan semua files ter-upload
3. Tab **Actions** → seharusnya ada workflow "Deploy Backend to cPanel"

## Setup GitHub Secrets

Repository → **Settings** → **Secrets and variables** → **Actions**

Tambahkan:
- `FTP_SERVER` = `ftp.manawa.sch.id`
- `FTP_USERNAME` = `api@api-sihadirsmp.manawa.sch.id`
- `FTP_PASSWORD` = (FTP password dari cPanel)
- `SSH_USERNAME` = (cPanel username)
- `SSH_PASSWORD` = (cPanel password)

## Test Auto-Deploy

```bash
# Buat perubahan kecil
echo "// test" >> routes/api.php
git add .
git commit -m "test: deployment"
git push origin main
```

Check GitHub Actions → seharusnya workflow running!
