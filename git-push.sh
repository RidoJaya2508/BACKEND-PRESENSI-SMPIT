#!/bin/bash
# Script untuk push backend ke GitHub

cd "C:/Users/L380/Documents/SKRIPSI/BACKEND-PRESENSI-SMPIT"

# Initialize git jika belum
if [ ! -d .git ]; then
  echo "Initializing git repository..."
  git init
fi

# Add all files
echo "Adding files..."
git add .

# Commit
echo "Committing changes..."
git commit -m "feat: setup GitHub Actions auto-deploy + production config

- Add .github/workflows/deploy.yml for auto-deployment
- Add .env.production template for production setup
- Add GITHUB-DEPLOY.md documentation
- Configure CORS for production domain
- Setup Telegram webhook for production
"

# Check if remote exists
if git remote | grep -q "origin"; then
  echo "Remote 'origin' exists"
  echo "Ready to push. Run: git push origin main"
else
  echo ""
  echo "==================================================="
  echo "Remote repository belum disetup!"
  echo ""
  echo "Setup steps:"
  echo "1. Buat repository baru di GitHub (nama: sihadir-backend)"
  echo "2. Run command:"
  echo "   git remote add origin https://github.com/username/sihadir-backend.git"
  echo "3. Run: git push -u origin main"
  echo "==================================================="
fi
