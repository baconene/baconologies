# Get local network IP address
$networkIP = (Get-NetIPAddress -AddressFamily IPv4 -InterfaceAlias "Ethernet*" | Where-Object {$_.IPAddress -like "192.168.*" -or $_.IPAddress -like "10.*" -or $_.IPAddress -like "172.*"})[0].IPAddress

if (-not $networkIP) {
    $networkIP = (Get-NetIPAddress -AddressFamily IPv4 | Where-Object {$_.IPAddress -like "192.168.*" -or $_.IPAddress -like "10.*" -or $_.IPAddress -like "172.*"})[0].IPAddress
}

Write-Host "Starting Laravel + Vite Development Servers with Network Access" -ForegroundColor Green
Write-Host ""
Write-Host "Your local network IP: $networkIP" -ForegroundColor Yellow
Write-Host "Laravel will be accessible at: http://${networkIP}:8000" -ForegroundColor Cyan
Write-Host "Vite dev server will be accessible at: http://${networkIP}:5173" -ForegroundColor Cyan
Write-Host ""
Write-Host "Press Ctrl+C to stop both servers" -ForegroundColor Red
Write-Host ""

# Start Vite dev server
Start-Process powershell -ArgumentList "-NoExit", "-Command", "npm run dev:network"

# Wait a moment for Vite to start
Start-Sleep -Seconds 3

# Start Laravel artisan serve
Start-Process powershell -ArgumentList "-NoExit", "-Command", "php artisan serve --host=0.0.0.0 --port=8000"

Write-Host "Both servers are starting in separate windows..." -ForegroundColor Green
Write-Host "You can also use 'npm run serve' to start both servers in the same terminal" -ForegroundColor Magenta