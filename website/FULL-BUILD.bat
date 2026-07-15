@echo off
set PHP=C:\xampp\php\php.exe
cd /d %~dp0
echo === Icomply FULL BUILD ===
"%PHP%" bin\full-build.php
if errorlevel 1 (
  echo BUILD reported issues
) else (
  echo BUILD OK
)
echo.
echo === DEBUG CHECK ===
"%PHP%" bin\debug-check.php
echo.
echo === HTTP CHECK (Apache must be running) ===
"%PHP%" bin\http-check.php
echo.
pause
