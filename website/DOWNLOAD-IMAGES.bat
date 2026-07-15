@echo off
cd /d "%~dp0"
echo Downloading keyword stock images...
powershell -ExecutionPolicy Bypass -File "bin\download-stock-images.ps1"
echo.
echo Downloading manufacturer / panel images...
powershell -ExecutionPolicy Bypass -File "bin\download-manufacturer-images.ps1"
echo.
echo Regenerating all area pages with 3 images + manufacturers...
php bin\generate-site.php --limit=150
echo.
echo DONE. Open http://localhost/icomply
pause
