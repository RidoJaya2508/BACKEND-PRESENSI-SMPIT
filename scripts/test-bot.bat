@echo off
REM ===========================================
REM  TEST TELEGRAM BOT
REM ===========================================
REM  Script untuk test semua fitur bot
REM ===========================================

title Telegram Bot Test
color 0E

cd /d "%~dp0\.."

echo.
echo ========================================
echo   TELEGRAM BOT TESTING
echo ========================================
echo.

REM Load bot token
for /f "usebackq tokens=1,2 delims==" %%a in (".env") do (
    if "%%a"=="TELEGRAM_BOT_TOKEN" set BOT_TOKEN=%%b
)

if "%BOT_TOKEN%"=="" (
    echo ERROR: TELEGRAM_BOT_TOKEN not found in .env!
    pause
    exit /b 1
)

set /p CHAT_ID="Enter your Chat ID: "
if "%CHAT_ID%"=="" (
    echo ERROR: Chat ID required!
    pause
    exit /b 1
)

echo.
echo Bot Token: %BOT_TOKEN:~0,20%...
echo Chat ID: %CHAT_ID%
echo.
pause

REM Test 1: /start
echo.
echo ========================================
echo TEST 1: /start command
echo ========================================
curl -s -X POST "https://api.telegram.org/bot%BOT_TOKEN%/sendMessage" ^
  -H "Content-Type: application/json" ^
  -d "{\"chat_id\":\"%CHAT_ID%\",\"text\":\"/start\"}"
echo.
echo Check Telegram: Should have inline keyboard buttons
echo.
pause

REM Test 2: /menu
echo.
echo ========================================
echo TEST 2: /menu command
echo ========================================
curl -s -X POST "https://api.telegram.org/bot%BOT_TOKEN%/sendMessage" ^
  -H "Content-Type: application/json" ^
  -d "{\"chat_id\":\"%CHAT_ID%\",\"text\":\"/menu\"}"
echo.
echo Check Telegram: Should show menu with buttons
echo.
pause

REM Test 3: Regular message
echo.
echo ========================================
echo TEST 3: Regular message "Halo"
echo ========================================
curl -s -X POST "https://api.telegram.org/bot%BOT_TOKEN%/sendMessage" ^
  -H "Content-Type: application/json" ^
  -d "{\"chat_id\":\"%CHAT_ID%\",\"text\":\"Halo bot\"}"
echo.
echo Check Telegram: Should reply with guidance
echo.
pause

REM Test 4: /help
echo.
echo ========================================
echo TEST 4: /help command
echo ========================================
curl -s -X POST "https://api.telegram.org/bot%BOT_TOKEN%/sendMessage" ^
  -H "Content-Type: application/json" ^
  -d "{\"chat_id\":\"%CHAT_ID%\",\"text\":\"/help\"}"
echo.
echo Check Telegram: Should show complete help
echo.
pause

echo.
echo ========================================
echo   ALL TESTS COMPLETED!
echo ========================================
echo.
echo If bot didn't respond:
echo 1. Make sure polling is running: scripts\start.bat
echo 2. Check logs: storage\logs\laravel.log
echo.
pause
