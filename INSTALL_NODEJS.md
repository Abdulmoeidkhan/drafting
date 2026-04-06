# Node.js Installation Required

Your system doesn't have Node.js/npm installed. This is required for real-time broadcasting.

## Download & Install Node.js

1. Go to: https://nodejs.org/
2. Download **LTS (18.x or 20.x)** version
3. Run the installer and follow prompts
4. During installation:
   - ✅ Keep "Add to PATH" checked
   - ✅ Keep "Install npm" checked

## Verify Installation

After installation, open **NEW PowerShell window** and run:
```powershell
node --version
npm --version
```

You should see version numbers like:
```
v20.10.0
10.2.5
```

## After Node.js Installation

Run these commands in your Laravel directory:

```powershell
cd e:\ParticpantManagmentSystem\laravel

# Clean npm cache
npm cache clean --force

# Reinstall all packages
npm install

# Start Vite dev server
npm run dev
```

Then in another terminal:
```powershell
# Start Reverb server
php artisan reverb:start
```

## Multiple Terminal Tabs

You need to keep 2 terminals running:

**Terminal 1 (npm dev server):**
```
npm run dev
```
Output should show:
```
Local: http://localhost:5173
```

**Terminal 2 (Reverb server):**
```
php artisan reverb:start
```
Output should show:
```
Server running on http://localhost:8080
```

Then open browser to http://localhost:5173

---

Once Node.js is installed and npm packages are installed, the real-time broadcasting will work!
