# Dynamic Network Setup Script for Laravel + Vite
# This script automatically detects your network IP and updates configurations

# Get local network IP address
$networkIP = (Get-NetIPAddress -AddressFamily IPv4 -InterfaceAlias "Wi-Fi*", "Ethernet*" | 
    Where-Object {$_.IPAddress -like "192.168.*" -or $_.IPAddress -like "10.*" -or ($_.IPAddress -like "172.*" -and $_.IPAddress -notlike "172.17.*")} | 
    Select-Object -First 1).IPAddress

if (-not $networkIP) {
    $networkIP = (Get-NetIPAddress -AddressFamily IPv4 | 
        Where-Object {$_.IPAddress -like "192.168.*" -or $_.IPAddress -like "10.*" -or ($_.IPAddress -like "172.*" -and $_.IPAddress -notlike "172.17.*")} | 
        Select-Object -First 1).IPAddress
}

if (-not $networkIP) {
    Write-Host "Could not detect network IP. Using localhost." -ForegroundColor Yellow
    $networkIP = "localhost"
}

Write-Host "Detected Network IP: $networkIP" -ForegroundColor Green

# Update Vite config with current IP
$viteConfigPath = "vite.config.ts"
if (Test-Path $viteConfigPath) {
    $viteContent = Get-Content $viteConfigPath -Raw
    $viteContent = $viteContent -replace "hmr:\s*{\s*host:\s*'[^']*'", "hmr: { host: '$networkIP'"
    Set-Content $viteConfigPath $viteContent
    Write-Host "Updated Vite config with IP: $networkIP" -ForegroundColor Cyan
}

# Backup original .env if it doesn't exist
if (-not (Test-Path ".env.backup")) {
    Copy-Item ".env" ".env.backup"
    Write-Host "Created backup of .env file" -ForegroundColor Yellow
}

# Update .env file
$envContent = Get-Content ".env"
$newEnvContent = @()
$appUrlUpdated = $false

foreach ($line in $envContent) {
    if ($line -match "^APP_URL=") {
        $newEnvContent += "APP_URL=http://${networkIP}:8000"
        $appUrlUpdated = $true
        Write-Host "Updated APP_URL to: http://${networkIP}:8000" -ForegroundColor Cyan
    } else {
        $newEnvContent += $line
    }
}

# If APP_URL wasn't found, add it
if (-not $appUrlUpdated) {
    $newEnvContent += "APP_URL=http://${networkIP}:8000"
    Write-Host "Added APP_URL: http://${networkIP}:8000" -ForegroundColor Cyan
}

Set-Content ".env" $newEnvContent

Write-Host "`nNetwork setup complete!" -ForegroundColor Green
Write-Host "Laravel will be accessible at: http://${networkIP}:8000" -ForegroundColor Magenta
Write-Host "Vite dev server will be accessible at: http://${networkIP}:5173" -ForegroundColor Magenta
Write-Host "`nAvailable commands:" -ForegroundColor Yellow
Write-Host "  npm run serve          - Start both servers concurrently" -ForegroundColor White
Write-Host "  npm run dev:network    - Start only Vite with network access" -ForegroundColor White  
Write-Host "  .\start-network.ps1    - Start both servers in separate windows" -ForegroundColor White
Write-Host "  .\start-network.bat    - Start both servers (batch file version)" -ForegroundColor White
Write-Host "`nTo restore original settings, copy .env.backup to .env" -ForegroundColor Gray