# Scripts Directory

Folder ini berisi semua script helper untuk development.

## 📂 Available Scripts

### 🚀 Main Scripts

**`start.bat`** - Start Laravel server + Telegram bot
```batch
scripts\start.bat
```
Menjalankan:
- Laravel development server (http://127.0.0.1:8000)
- Telegram bot polling

---

### 🧪 Testing

**`test-bot.bat`** - Test semua fitur bot
```batch
scripts\test-bot.bat
```
Test:
- `/start` command
- `/menu` command  
- Regular messages
- `/help` command

---

### 🔄 Configuration

**`switch-mode.bat`** - Switch antara development/production
```batch
scripts\switch-mode.bat
```
Options:
1. Development mode (polling)
2. Production mode (webhook)
3. Check current status

---

## 🎯 Quick Start

### Development (Local):
```batch
cd BACKEND-PRESENSI-SMPIT
scripts\start.bat
```

### Testing:
```batch
scripts\test-bot.bat
```

### Deploy Production:
```batch
scripts\switch-mode.bat
# Pilih option 2, masukkan webhook URL
```

---

## 📝 Notes

- **Development**: Butuh polling (start.bat)
- **Production**: Pakai webhook (otomatis via Nginx)
- Semua script relative path, bisa dijalankan dari folder manapun
