# Real-Time Broadcasting Troubleshooting Guide

## Overview

Your application now has **real-time broadcasting** with visual notifications when:
- ✅ Players are picked
- ✅ Turns change
- ✅ Rounds complete
- ✅ Timer counts down

However, it requires **3 running services** to work properly.

---

## Required Services & Setup

### 1. **Node.js + npm** (One-time installation)
Install Node.js from https://nodejs.org (LTS version)

Then run once:
```powershell
cd e:\ParticpantManagmentSystem\laravel
npm install
```

### 2. **Reverb WebSocket Server** (Running)
Start in Terminal 1:
```powershell
cd e:\ParticpiantManagmentSystem\laravel
php artisan reverb:start
```

Expected output:
```
INFO  Starting Reverb server...
INFO  Server running on http://localhost:8080
INFO  Waiting for connections...
```

### 3. **Vite Dev Server** (Running)  
Start in Terminal 2:
```powershell
cd e:\ParticpiantManagmentSystem\laravel
npm run dev
```

Expected output:
```
VITE v7.0.7  ready in 200 ms

➜  Local:   http://localhost:5173/
```

---

## Testing Real-Time Updates

### Step-by-Step Test

1. **Start all 3 services** (Node, Vite, Reverb are running)

2. **Open Application**
   - Team Dashboard: http://localhost:5173/team/dashboard
   - Admin Panel: http://localhost:5173/admin/teams?tab=draft

3. **Start Draft Round**
   - Go to Admin → Teams → Draft Pool tab
   - Select a category and timer length
   - Click "Start Round"

4. **Open Two Tabs**
   - Tab 1: Admin Teams (Draft Pool tab)
   - Tab 2: Team Dashboard

5. **Make a Pick in Tab 1**
   - Click "Pick Player" for any eligible player
   - Should see success notification

6. **Check Tab 2**
   - Should update automatically WITHOUT refresh
   - Should show:
     - ✅ New current team
     - ✅ Updated timer
     - ✅ Success notification
     - ✅ Player added to activity log

---

## Verifying Services

### Check Reverb is Running
- Terminal should show `Server running on http://localhost:8080`
- Port 8080 should be listening

```powershell
netstat -ano | findstr :8080
```

### Check Vite is Running
- Terminal should show `Local: http://localhost:5173`
- Port 5173 should be listening

```powershell
netstat -ano | findstr :5173
```

### Check npm Packages Installed
```powershell
ls node_modules | grep -E "(laravel-echo|pusher)"
```

Both should return results.

---

## Browser Console Check

1. Open browser (http://localhost:5173)
2. Press **F12** to open Developer Tools
3. Click **Console** tab
4. Look for startup messages:

**If working:**
```
✓ Laravel Echo initialized
  Broadcaster: pusher
  Host: localhost
  Port: 8080
  Ready for real-time broadcasting
```

**If not working:**
```
⚠ Laravel Echo initialization failed
  Error: Failed to resolve module specifier "laravel-echo"
  
To enable real-time features:
  1. Ensure npm is installed
  2. Run: npm install
  3. Restart dev server
  4. Refresh this page
```

---

## Common Issues & Solutions

### Issue 1: "Can't resolve laravel-echo"

**Cause:** npm packages not installed

**Solution:**
```powershell
npm install
npm run dev  # Restart dev server
# Refresh browser
```

### Issue 2: WebSocket Connection Failed

**Cause:** Reverb server not running

**Solution:**
```powershell
php artisan reverb:start
# Keep this terminal open and running
```

### Issue 3: Pages Not Auto-Updating

**Causes:**
- Reverb not running
- npm packages not installed  
- Browser cache issues
- Vite dev server crashed

**Solutions:**
1. Clear browser cache: Ctrl+Shift+Delete
2. Check all 3 services running
3. Restart npm run dev
4. Refresh page: Ctrl+F5

### Issue 4: No Notifications

**Cause:** Browser notifications disabled or Echo not ready

**Solution:**
1. Check browser console (F12) for errors
2. Verify "Echo initialized" message in console
3. Allow notifications in browser settings

### Issue 5: "Echo not available" Message

**Cause:** npm packages not installed yet

**Solution:**
```powershell
npm install
npm run dev
# Refresh page after restart
```

---

## Using START.bat (Windows)

Double-click `START.bat`:
```
1. Run dev server + Reverb (RECOMMENDED)
2. Run dev server only
3. Run Reverb only
4. Build for production
5. Run tests
6. Clear cache
```

**Choose Option 1** to start both Vite and Reverb together.

---

## Port Conflicts

### Port 8080 Already in Use
Change `REVERB_PORT` in `.env`:
```env
REVERB_PORT=8081
```

Update browser connections:
```javascript
# In resources/js/bootstrap.js or .env
VITE_PUSHER_PORT=8081
```

### Port 5173 Already in Use
```powershell
npm run dev -- --port 5174
```

---

## Notification Types

### Success Notifications (Green)
- Player picked
- Turn changed
- Round completed

### Info Notifications (Blue)
- Generic updates
- Timer changes
- Status changes

### Error Notifications (Red)
- Connection failed
- Echo initialization failed
- Broadcasting errors

---

## Polling Fallback

If Echo/WebSockets not available, the app has a **polling fallback** that checks for changes every 10 seconds.

**Indicators:**
- Notifications appear after up to 10 seconds delay
- Console shows: "Polling: Changes detected"
- Older browsers without WebSocket support still work

---

## Production Deployment

For production, use Pusher Cloud (free tier supports 100 concurrent connections):

1. Sign up: https://pusher.com
2. Get credentials
3. Update `.env`:
   ```env
   BROADCAST_DRIVER=pusher
   PUSHER_APP_ID=your-id
   PUSHER_APP_KEY=your-key
   PUSHER_APP_SECRET=your-secret
   PUSHER_APP_CLUSTER=mt1
   ```
4. Deploy without running Reverb locally

---

## Debug Mode

Enable verbose logging in console:

Edit `resources/js/bootstrap.js` and add:
```javascript
window.debugBroadcasting = true;
```

Then check console for detailed event logs:
```
✓ Real-time broadcast received: Player picked John Doe
✓ Real-time broadcast received: Turn changed
✓ Broadcasting: Page reloading...
```

---

## Performance Tips

- Chrome/Firefox: Native WebSocket support (fastest)
- Safari: May use polling fallback
- Edge: Full WebSocket support
- Mobile: May have connection issues on cellular networks

---

## Monitoring Real-Time Health

### Check Server Logs
```powershell
# Reverb logs
tail -f storage/logs/laravel.log
```

### Check Browser Network
1. F12 → Network tab
2. Filter for "ws" (WebSocket connections)
3. Should show connection to localhost:8080
4. Messages should appear as you pick players

---

## Getting Help

If real-time features still aren't working:

1. **Verify all 3 services running:**
   - npm (node_modules installed)
   - Vite (terminal shows port 5173)
   - Reverb (terminal shows port 8080)

2. **Check console messages (F12):**
   - Look for initialization status
   - Check for error messages

3. **Try isolated test:**
   - Kill all services
   - Start fresh with START.bat option 1
   - Refresh application
   - Make a test pick

4. **Review logs:**
   - `storage/logs/laravel.log`
   - Browser console (F12)
   - Terminal output

---

## Quick Checklist

- [ ] Node.js installed (`node --version` works)
- [ ] npm packages installed (`npm install` completed)
- [ ] Reverb running (`php artisan reverb:start` showing "Waiting for connections")
- [ ] Vite running (`npm run dev` showing port 5173)
- [ ] Browser console shows "Echo initialized" (F12 → Console)
- [ ] Can open http://localhost:5173 in browser
- [ ] Can start a draft round in admin panel
- [ ] Can see notifications when picks are made
- [ ] Multiple tabs update without manual refresh

✅ If all checkboxes pass, real-time broadcasting is working!

---

## Related Files

- `BROADCASTING.md` - Technical documentation  
- `NPM_SETUP.md` - npm installation guide
- `REAL_TIME_SETUP.md` - Quick start guide
- `START.bat` - Windows startup script
- `resources/js/bootstrap.js` - Echo initialization
- `resources/views/team/dashboard.blade.php` - Team listener
- `resources/views/admin/teams.blade.php` - Admin listener

---

Remember: **3 services must be running:**
1. Node.js (manages npm packages)
2. Reverb (WebSocket server on port 8080)
3. Vite (dev server on port 5173)

Start all three and real-time broadcasting will work! 🎉
