@echo off
echo Starting Laravel + Vite Development Servers with Network Access
echo.
echo Your local network IP: 192.168.1.5
echo Laravel will be accessible at: http://192.168.1.5:8000
echo Vite dev server will be accessible at: http://192.168.1.5:5173
echo.
echo Press Ctrl+C to stop both servers
echo.

start "Vite Dev Server" cmd /k "npm run dev:network"
timeout /t 3 /nobreak >nul
start "Laravel Artisan Serve" cmd /k "php artisan serve --host=0.0.0.0 --port=8000"

echo Both servers are starting in separate windows...
echo Close this window or press any key to return to prompt
pause >nul