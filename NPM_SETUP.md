# NPM Package Installation Guide

## Current Status
The `laravel-echo` and `pusher-js` packages are listed in `package.json` but not yet installed in `node_modules/`.

## What You Need

To install npm packages, you need **Node.js** with npm installed and available in your PATH.

### Check if Node.js is Installed

Open PowerShell and run:
```powershell
node --version
npm --version
```

If you get command not found errors, Node.js is not installed or not in PATH.

---

## Installation Options

### Option 1: Install Node.js (Recommended)

1. Download Node.js from: https://nodejs.org/ (LTS version)
2. Run the installer and follow the prompts
3. Restart PowerShell/terminal
4. Verify installation:
   ```powershell
   node --version
   npm --version
   ```

### Option 2: Add Node.js to PATH

If Node.js is installed but not in PATH (common with XAMPP):

1. Locate Node.js installation (commonly in `C:\Program Files\nodejs`)
2. Add it to system PATH:
   - Right-click "This PC" → Properties
   - Click "Advanced system settings"
   - Click "Environment Variables"
   - Edit "Path" and add Node.js path
   - Restart PowerShell

---

## Install Packages

Once Node.js and npm are available, run:

```powershell
cd e:\ParticpantManagmentSystem\laravel
npm install
```

This will:
- Install all packages listed in `package.json`
- Create/update `node_modules/` directory
- Generate `package-lock.json`

---

## Verify Installation

After `npm install` completes, verify the packages are installed:

```powershell
ls node_modules | findstr "laravel-echo"
ls node_modules | findstr "pusher-js"
```

Both should return results if installation was successful.

---

## Start Development

Once packages are installed, restart the dev server:

```powershell
npm run dev
```

You should see:
- No Vite import errors
- Browser console shows: `✓ Laravel Echo initialized for real-time broadcasting`

---

## Troubleshooting

### "npm: command not found"
- Node.js is not installed or not in PATH
- Install Node.js or add it to PATH (see above)

### "node_modules still missing after npm install"
- Check internet connection
- Try: `npm install --verbose` to see detailed output
- Try: `npm cache clean --force` then `npm install`

### Port already in use (npm run dev)
- Change the port: `npm run dev -- --port 5174`
- Or kill the process using port 5173

### Still getting Vite import error
- Clear your browser cache (Ctrl+Shift+Delete)
- Restart dev server with: `npm run dev`
- Check browser console (F12) for errors

---

## Next Steps

1. Install Node.js if not already installed
2. Run `npm install` in the Laravel root directory
3. Restart `npm run dev`
4. Refresh browser (Ctrl+F5)
5. Real-time features will now be available

---

If issues persist, check `npm-debug.log` in the project root for detailed error information.
