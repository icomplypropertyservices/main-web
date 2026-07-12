$ErrorActionPreference = 'Stop'
$php = 'C:\xampp\php\php.exe'
$root = $PSScriptRoot

if (-not (Test-Path $php)) {
    $phpCmd = Get-Command php -ErrorAction SilentlyContinue
    if ($phpCmd) { $php = $phpCmd.Source }
    else {
        Write-Host "PHP not found. Install XAMPP or add php to PATH." -ForegroundColor Red
        exit 1
    }
}

Write-Host "Starting Icomply Property Services..." -ForegroundColor Cyan
Write-Host ""
Write-Host "Open your browser at: http://localhost:8000" -ForegroundColor Green
Write-Host ""
Write-Host "Press Ctrl+C to stop the server." -ForegroundColor Yellow
Write-Host ""

Set-Location $root
& $php -S localhost:8000 -t $root router.php
