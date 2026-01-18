@echo off
setlocal

REM Photoblast kiosk launcher (auto print, tanpa dialog browser)
REM Windows 10/11 + Chrome + php artisan serve
REM Pastikan default printer + A6 + kualitas/paper type sudah di-set di driver Epson.

set PROJECT_DIR=C:\laragon\www\photoblast-rev
set URL=http://127.0.0.1:8000
set PORT=8000

cd /d "%PROJECT_DIR%"
start "Photoblast Server" /min cmd /c "php artisan serve --host=127.0.0.1 --port=%PORT%"
timeout /t 2 /nobreak >nul

REM Find Chrome exe (registry -> common paths -> where)
set CHROME_EXE=
for /f "tokens=2*" %%A in ('reg query "HKLM\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\chrome.exe" /ve 2^>nul ^| find /i "REG_SZ"') do set CHROME_EXE=%%B
if not defined CHROME_EXE for /f "tokens=2*" %%A in ('reg query "HKCU\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\chrome.exe" /ve 2^>nul ^| find /i "REG_SZ"') do set CHROME_EXE=%%B
if not defined CHROME_EXE if exist "C:\Program Files\Google\Chrome\Application\chrome.exe" set CHROME_EXE=C:\Program Files\Google\Chrome\Application\chrome.exe
if not defined CHROME_EXE if exist "C:\Program Files (x86)\Google\Chrome\Application\chrome.exe" set CHROME_EXE=C:\Program Files (x86)\Google\Chrome\Application\chrome.exe
if not defined CHROME_EXE for /f "delims=" %%P in ('where chrome 2^>nul') do if not defined CHROME_EXE set CHROME_EXE=%%P

if not defined CHROME_EXE (
  echo Chrome tidak ditemukan.
  echo Install Chrome, atau update path di file: %~dp0%~nx0
  pause
  exit /b 1
)

REM Start Chrome in kiosk + auto printing
set KIOSK_PROFILE=%PROJECT_DIR%\.kiosk-profile
start "Photoblast Kiosk" "%CHROME_EXE%" --new-window --kiosk --kiosk-printing --user-data-dir="%KIOSK_PROFILE%" --no-first-run --disable-pinch --overscroll-history-navigation=0 "%URL%"

endlocal
