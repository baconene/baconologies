# Network Hosting Setup for Laravel + Vite

This Laravel project is now configured for dynamic network hosting, allowing you to access your development server from other devices on your local network.

## 🚀 Quick Start

### Option 1: Automatic Setup + Start (Recommended)
```bash
# Run setup and start both servers
.\setup-network.ps1
npm run serve
```

### Option 2: Start Both Servers Concurrently
```bash
npm run serve
```

### Option 3: Start Servers in Separate Windows
```bash
.\start-network.ps1
# or
.\start-network.bat
```

### Option 4: Manual Start
```bash
# Terminal 1: Start Vite dev server
npm run dev:network

# Terminal 2: Start Laravel server
php artisan serve --host=0.0.0.0 --port=8000
```

## 📱 Access Your Application

After starting the servers, your application will be accessible at:

- **Laravel App**: `http://192.168.1.5:8000`
- **Vite Dev Server**: `http://192.168.1.5:5173`

*Note: Replace `192.168.1.5` with your actual local network IP address.*

## 🔧 Configuration Details

### What the setup includes:

1. **Vite Configuration** (`vite.config.ts`):
   - Server listens on all network interfaces (`0.0.0.0`)
   - HMR (Hot Module Replacement) configured for network access
   - Port set to 5173

2. **Package.json Scripts**:
   - `dev:network`: Starts Vite with network access
   - `serve`: Starts both Laravel and Vite servers concurrently

3. **Environment Configuration**:
   - `APP_URL` automatically updated to use network IP
   - Original `.env` backed up as `.env.backup`

4. **Helper Scripts**:
   - `setup-network.ps1`: Automatically detects IP and configures everything
   - `start-network.ps1`: Starts both servers in separate windows
   - `start-network.bat`: Batch file version for starting servers

## 📲 Testing on Mobile Devices

1. Make sure your computer and mobile device are on the same WiFi network
2. Start the development servers using any method above
3. Open your mobile browser and navigate to `http://192.168.1.5:8000`
4. The application should load with full hot-reload functionality

## 🔧 Troubleshooting

### IP Address Changes
If your IP address changes (different WiFi network, DHCP renewal), run:
```bash
.\setup-network.ps1
```

### Firewall Issues
If you can't access from other devices:
1. Check Windows Firewall settings
2. Ensure ports 8000 and 5173 are allowed
3. Try temporarily disabling firewall for testing

### Restore Original Settings
```bash
copy .env.backup .env
```

## 🛡️ Security Notes

- This setup is for **development only**
- Never use `0.0.0.0` binding in production
- The servers are accessible to anyone on your local network
- Consider using a VPN if working on sensitive projects

## ⚙️ Customization

### Change Ports
Edit `package.json` and `vite.config.ts` to use different ports:
```json
"serve": "concurrently \"npm run dev:network\" \"php artisan serve --host=0.0.0.0 --port=8080\""
```

### Different IP Detection
Modify `setup-network.ps1` to detect different network interfaces or use a static IP.