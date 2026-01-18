@echo off
setlocal

REM Photoblast launcher (Windows Print Pictures / Photo Print Wizard)
REM - Opens Chrome in FULLSCREEN (NOT kiosk) so Windows dialogs can appear on top.
REM - Printing flow should use /open-print-pictures (Shell verb 'print' like right-click JPG -> Print).

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

REM Dedicated profile so it always opens a new window
set KIOSK_PROFILE=%PROJECT_DIR%\.kiosk-profile
start "Photoblast Fullscreen" "%CHROME_EXE%" --new-window --start-fullscreen --user-data-dir="%KIOSK_PROFILE%" --no-first-run --disable-pinch --overscroll-history-navigation=0 "%URL%"

endlocal
