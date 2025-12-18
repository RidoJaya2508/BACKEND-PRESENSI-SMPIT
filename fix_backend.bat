@echo off
echo ====================================================================
echo == Backend Environment Fix Script
echo ====================================================================
echo.
echo IMPORTANT:
echo This script will attempt to install required packages and clear caches.
echo Please ensure you have Composer and PHP installed and available in
echo your system's PATH.
echo.
echo This script CANNOT enable your PHP extensions. The logs show they are
echo missing. Before running this, please ensure 'extension=pdo_sqlite'
echo and 'extension=pdo_mysql' are enabled in your php.ini file and that
echo you have restarted your web server/PHP service.
echo.
pause

echo.
echo [Step 1/5] Installing Composer dependencies (including missing 'laravel/sanctum')...
composer install
if %errorlevel% neq 0 (
    echo.
    echo ERROR: Composer install failed. Please check your Composer and PHP setup.
    pause
    exit /b %errorlevel%
)
echo Composer dependencies installed successfully.
echo.

echo [Step 2/5] Clearing route cache...
php artisan route:clear
echo.

echo [Step 3/5] Clearing config cache...
php artisan config:clear
echo.

echo [Step 4/5] Clearing application cache...
php artisan cache:clear
echo.

echo ====================================================================
echo == Fixes applied successfully!
echo ====================================================================
echo.
echo [Step 5/5] Please now start the server manually by running:
echo.
echo   php artisan serve
echo.

pause
