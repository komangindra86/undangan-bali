@echo off
setlocal
set "ROOT=C:\laragon\www\undangan-bali"
set "PHP=C:\laragon\bin\php\php-8.2.27-Win32-vs16-x64\php.exe"
rem Override only these local server processes, not the production .env files.
set "APP_URL=http://127.0.0.1:8015"
set "EXPO_PUBLIC_API_URL=http://127.0.0.1:8015/api"

echo Menjalankan Undangan Bali Santih - mode lokal...
echo.
echo Akan terbuka 2 jendela:
echo - Backend Laravel
echo - Tampilan aplikasi mobile di browser
echo API lokal: %EXPO_PUBLIC_API_URL%
echo.

start "Backend Laravel - Jangan Ditutup" powershell -NoExit -ExecutionPolicy Bypass -Command "$host.UI.RawUI.WindowTitle='Backend Laravel - Jangan Ditutup'; Set-Location -LiteralPath '%ROOT%'; & '%PHP%' artisan config:clear; & '%PHP%' artisan serve --host=127.0.0.1 --port=8015"

timeout /t 3 /nobreak >nul

start "Mobile Expo - Jangan Ditutup" powershell -NoExit -ExecutionPolicy Bypass -Command "$host.UI.RawUI.WindowTitle='Mobile Expo - Jangan Ditutup'; Set-Location -LiteralPath '%ROOT%\mobile'; npx expo start --web --port 8082 --clear"

timeout /t 10 /nobreak >nul

start "" "http://127.0.0.1:8082"

echo Browser akan membuka aplikasi.
echo Jangan tutup dua jendela server selama masih mencoba aplikasi.
echo Untuk berhenti, tutup dua jendela server tersebut.
pause
