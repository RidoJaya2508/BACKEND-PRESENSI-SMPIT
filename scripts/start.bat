@echo off
REM ===========================================
REM  START LARAVEL SERVER + TELEGRAM BOT
REM ===========================================
REM  Script ini akan menjalankan:
REM  1. Laravel development server
REM  2. Telegram bot polling
REM ===========================================

title Laravel + Telegram Bot
color 0A

cd /d "%~dp0\.."

echo.
echo ====================================================
echo    STARTING LARAVEL SERVER + TELEGRAM BOT
echo ====================================================
echo.

REM Clear cache
echo [1/3] Clearing cache...
call php artisan cache:clear >nul 2>&1
call php artisan config:clear >nul 2>&1
echo      Done!
echo.

REM Start Laravel Server
echo [2/3] Starting Laravel Server...
start "Laravel Server - http://127.0.0.1:8000" cmd /k "cd /d %CD% && echo Laravel Backend Server && echo ======================== && echo Running on: http://127.0.0.1:8000 && echo. && php artisan serve"
ping 127.0.0.1 -n 2 >nul
echo      Server starting at http://127.0.0.1:8000
echo.

REM Start Telegram Bot
echo [3/3] Starting Telegram Bot...
start "Telegram Bot Polling" cmd /k "cd /d %CD% && echo Telegram Bot Polling && echo ======================== && echo Bot: @Sihadirsmpbot && echo. && php artisan telegram:poll"
echo      Bot polling started
echo.

echo ====================================================
echo    ALL SERVICES STARTED!
echo ====================================================
echo.
echo  [BACKEND]  http://127.0.0.1:8000
echo  [BOT]      Polling mode (running)
echo.
echo  Check the opened windows to see logs
echo  Close windows to stop services
echo.
echo ====================================================
pause
