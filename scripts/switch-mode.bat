@echo off
REM ===========================================
REM  SWITCH TELEGRAM BOT MODE
REM ===========================================
REM  Development: Polling mode
REM  Production: Webhook mode
REM ===========================================

title Bot Mode Switcher
color 0B

cd /d "%~dp0\.."

echo.
echo ========================================
echo   TELEGRAM BOT MODE SWITCHER
echo ========================================
echo.

REM Load bot token
for /f "usebackq tokens=1,2 delims==" %%a in (".env") do (
    if "%%a"=="TELEGRAM_BOT_TOKEN" set BOT_TOKEN=%%b
)

if "%BOT_TOKEN%"=="" (
    echo ERROR: TELEGRAM_BOT_TOKEN not found!
    pause
    exit /b 1
)

echo Bot: %BOT_TOKEN:~0,20%...
echo.

echo ========================================
echo SELECT MODE:
echo ========================================
echo.
echo [1] DEVELOPMENT (Polling)
echo     - For local testing
echo     - Requires: scripts\start.bat
echo.
echo [2] PRODUCTION (Webhook)
echo     - For deployment
echo     - Auto-runs via web server
echo.
echo [3] Check Status
echo.
echo ========================================
echo.

set /p MODE="Select (1/2/3): "

if "%MODE%"=="1" goto dev
if "%MODE%"=="2" goto prod
if "%MODE%"=="3" goto status
echo Invalid option!
goto end

:dev
echo.
echo Switching to DEVELOPMENT mode...
curl -X POST "https://api.telegram.org/bot%BOT_TOKEN%/deleteWebhook"
echo.
call php artisan cache:clear >nul
call php artisan config:clear >nul
echo.
echo ========================================
echo   DEVELOPMENT MODE ACTIVE
echo ========================================
echo.
echo Run: scripts\start.bat
echo.
goto end

:prod
echo.
set /p URL="Enter webhook URL: "
if "%URL%"=="" goto end
echo.
echo Setting webhook...
curl -X POST "https://api.telegram.org/bot%BOT_TOKEN%/setWebhook" ^
  -H "Content-Type: application/json" ^
  -d "{\"url\":\"%URL%\"}"
echo.
call php artisan config:cache >nul
call php artisan route:cache >nul
echo.
echo ========================================
echo   PRODUCTION MODE ACTIVE
echo ========================================
echo.
echo Webhook: %URL%
echo Bot will run automatically
echo.
goto end

:status
echo.
curl -s "https://api.telegram.org/bot%BOT_TOKEN%/getWebhookInfo"
echo.
echo.
echo If url="" = DEVELOPMENT (Polling)
echo If url="..." = PRODUCTION (Webhook)
echo.
goto end

:end
pause
