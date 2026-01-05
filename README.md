<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Backend Presensi SMPIT

Backend API untuk sistem presensi SMPIT dengan integrasi Telegram Bot.

## 🚀 Quick Start

### Menjalankan Server + Bot Sekaligus

**Cara Termudah (Double-click):**
```
START.bat
```

**Atau via Command Line:**
```batch
scripts\start.bat
```

Perintah di atas akan otomatis menjalankan:
- ✅ Laravel Backend Server (http://127.0.0.1:8000)
- ✅ Telegram Bot Polling

### Manual (Jika ingin terpisah)

**Terminal 1 - Backend:**
```bash
php artisan serve
```

**Terminal 2 - Bot:**
```bash
php artisan telegram:poll
```

## 🧪 Testing Bot

```batch
scripts\test-bot.bat
```

Test otomatis untuk:
- `/start` command
- `/menu` command
- Regular messages
- `/help` command

## 📋 Requirements

- PHP >= 8.1
- Composer
- MySQL/MariaDB
- Telegram Bot Token

## 🛠️ Installation

1. Clone repository
2. Install dependencies:
   ```bash
   composer install
   ```
3. Copy `.env.example` ke `.env` dan konfigurasi:
   ```
   TELEGRAM_BOT_TOKEN=your_bot_token
   TELEGRAM_BOT_USERNAME=your_bot_username
   ```
4. Generate key:
   ```bash
   php artisan key:generate
   ```
5. Migrate database:
   ```bash
   php artisan migrate
   ```

## 🤖 Telegram Bot Features

- ✅ Inline Keyboard Menu
- ✅ Context-aware responses
- ✅ Registration via NIS
- ✅ QR Code connection
- ✅ Attendance notifications

### Bot Commands:
- `/start` - Welcome message + menu
- `/menu` - Navigation menu
- `/help` - Bantuan lengkap
- `/daftar NIS` - Registrasi dengan NIS

## 📖 Documentation

- [Scripts README](scripts/README.md) - Panduan semua script
- [Bot Running Guide](../BOT-RUNNING-GUIDE.md)
- [Bot Start Guide](../BOT-START-GUIDE.md)
- [Telegram Professional Menu](../TELEGRAM-PROFESSIONAL-MENU.md)
- [Production Deployment](../PRODUCTION-DEPLOYMENT.md)

---

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
