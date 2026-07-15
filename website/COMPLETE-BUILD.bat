@echo off
cd /d "%~dp0"
echo === ICOMPLY FULL BUILD ===
echo.
echo [1/4] Keyword stock images (skip if already done)...
powershell -ExecutionPolicy Bypass -File "bin\download-stock-images.ps1"
echo.
echo [2/4] Manufacturer panel images...
powershell -ExecutionPolicy Bypass -File "bin\download-manufacturer-images.ps1"
echo.
echo [3/4] Service + area combo pages (3 images each)...
C:\xampp\php\php.exe bin\generate-site.php --limit=150
echo.
echo [4/4] Keyword pages + area hubs...
C:\xampp\php\php.exe bin\generate-keyword-pages.php
C:\xampp\php\php.exe bin\generate-area-hubs.php
echo.
echo === DONE ===
echo Open: http://localhost/icomply
echo Keywords: http://localhost/icomply/pages/keywords/
echo Example: http://localhost/icomply/pages/fire-alarms/oldham.php
pause
