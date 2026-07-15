Write-Host "Starting Icomply Property Services..." -ForegroundColor Cyan
Write-Host ""
Write-Host "Open your browser at: http://localhost:8000" -ForegroundColor Green
Write-Host ""
Write-Host "Press Ctrl+C to stop the server." -ForegroundColor Yellow
Write-Host ""

php -S localhost:8000

Read-Host -Prompt "Press Enter to exit"