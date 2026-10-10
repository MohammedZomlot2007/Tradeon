@echo off
title T1 - Laravel + Vite
cd /d "%~dp0"

echo ==========================================
echo        STARTING T1 PROJECT
echo ==========================================
echo.

REM Force local development settings
powershell -NoProfile -ExecutionPolicy Bypass -Command "$p='.env'; if (Test-Path $p) { $c=Get-Content $p; $c=$c -replace '^APP_URL=.*$','APP_URL=http://127.0.0.1:8000'; $c=$c -replace '^APP_FORCE_HTTPS=.*$','APP_FORCE_HTTPS=false'; $c=$c -replace '^SESSION_SECURE_COOKIE=.*$','SESSION_SECURE_COOKIE=false'; Set-Content $p $c }"

echo [1/3] Clearing Laravel cache...
php artisan optimize:clear

echo.
echo [2/3] Starting Laravel...
start "T1 - Laravel" cmd /k "cd /d ""%~dp0"" && php artisan serve"

timeout /t 2 /nobreak >nul

echo [3/3] Starting Vite...
start "T1 - Vite" cmd /k "cd /d ""%~dp0"" && npm run dev"

timeout /t 4 /nobreak >nul

echo Opening browser...
start "" "http://127.0.0.1:8000"

echo.
echo ==========================================
echo T1 is starting.
echo Keep both terminal windows open.
echo ==========================================
pause
