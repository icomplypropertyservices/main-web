@echo off
set PHP=C:\xampp\php\php.exe
if not exist "%PHP%" set PHP=php
echo Starting Icomply Property Services...
echo.
echo Open your browser at: http://localhost:8000
echo.
echo Press Ctrl+C to stop the server.
echo.
cd /d "%~dp0"
"%PHP%" -S localhost:8000 -t "%~dp0"
pause
