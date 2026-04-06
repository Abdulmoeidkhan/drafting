# Real-Time Broadcasting Setup Guide

## Overview
Your Participant Management System now includes real-time broadcasting using **Laravel Reverb** (free, open-source) and **Pusher JS** (free tier compatible). This allows:

- ✅ Real-time timer updates during player picks
- ✅ Instant turn notifications when it's a team's turn
- ✅ Live player pick announcements without page refresh
- ✅ Automatic round completion notifications

## Architecture

### Backend Broadcasting
- **Framework**: Laravel 11 with Broadcasting module
- **Driver**: Laravel Reverb (free WebSocket server)
- **Events Broadcasted**:
  - `PlayerPicked` - When a player is drafted
  - `TurnChanged` - When current turn moves to next team
  - `RoundCompleted` - When a draft round finishes

### Frontend Real-Time Listeners
- **Library**: Laravel Echo + Pusher JS
- **Channels**: Organized by `draft.{league_type}.{category_id}`
- **Auto-Updates**: Pages reload with fresh data when events arrive

---

## Getting Started

### 1. Start the Reverb WebSocket Server

**On Windows (PowerShell):**
```powershell
cd e:\ParticpantManagmentSystem\laravel
php artisan reverb:start
```

The server will start on `ws://localhost:8080` by default.

**Console Output:**
```
INFO  Starting Reverb server...
INFO  Server running on http://localhost:8080
INFO  Waiting for connections...
```

### 2. Verify Frontend is Connected

1. Open your application in a browser
2. Open **Developer Tools** (F12)
3. Check the **Console** tab:
   - You should see no errors related to WebSocket connection
   - If successful, you'll see `window.Echo` available in console

4. Test by running in console:
   ```javascript
   console.log(window.Echo); // Should show Echo instance
   window.Echo.subscribe('test').listen('TestEvent', (data) => {
       console.log('Received:', data);
   });
   ```

### 3. Verify Broadcasting is Working

Start a draft round and:
1. Open the **Team Dashboard** in one browser tab
2. Open the **Admin Teams Draft Tab** in another tab
3. Make a pick from either tab
4. Both tabs should **automatically refresh** showing the new state

---

## Configuration Files

### Backend Configuration

**`.env` Broadcasting Settings:**
```env
BROADCAST_DRIVER=reverb
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=414743
REVERB_APP_KEY=yjbvz2rxvpdn6rhbmdhx
REVERB_APP_SECRET=ffkwz9ull8bdpia9bj4n
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

**`config/broadcasting.php`:**
- Contains Reverb, Pusher, and fallback broadcast drivers
- Default driver is set to `reverb`
- Pusher configuration available for production use

### Frontend Configuration

**`resources/js/bootstrap.js`:**
```javascript
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: '{{ REVERB_APP_KEY }}',
    host: window.location.hostname,
    port: 8080,
    wsHost: window.location.hostname,
    wsPort: 8080,
});
```

**`.env` (Frontend Variables):**
```env
VITE_PUSHER_APP_KEY=yjbvz2rxvpdn6rhbmdhx
VITE_PUSHER_HOST=localhost
VITE_PUSHER_PORT=8080
VITE_PUSHER_SCHEME=http
```

---

## Broadcasting Events

### 1. PlayerPicked Event
**Triggered**: When a team picks a player
**Data Sent**:
```php
[
    'draft_pick_id' => 123,
    'draft_round_id' => 5,
    'team_id' => 2,
    'team_name' => 'Eagles',
    'participant_id' => 45,
    'participant_name' => 'John Doe',
    'pick_number' => 8,
    'picked_at' => '2026-03-31 14:23:45',
    'league_type' => 'male',
    'category_id' => 1,
]
```

### 2. TurnChanged Event
**Triggered**: When turn advances to next team (either by pick or timer expiry)
**Data Sent**:
```php
[
    'draft_round_id' => 5,
    'current_team_id' => 3,
    'previous_team_id' => 2,
    'current_turn_started_at' => '2026-03-31 14:25:10',
    'turn_time_seconds' => 180,
    'league_type' => 'male',
    'category_id' => 1,
    'status' => 'active',
]
```

### 3. RoundCompleted Event
**Triggered**: When all teams finish picking in a round
**Data Sent**:
```php
[
    'draft_round_id' => 5,
    'league_type' => 'male',
    'category_id' => 1,
    'status' => 'completed',
    'completed_at' => '2026-03-31 15:12:33',
]
```

---

## Channel Structure

Events are broadcasted on channels named:
```
draft.{league_type}.{category_id}
```

Examples:
- `draft.male.1` - Male league, Category 1 (e.g., Goalkeepers)
- `female.2` - Female league, Category 2 (e.g., Defenders)

This ensures:
- Teams only see updates relevant to their league
- Admin can monitor specific categories separately
- Efficient network usage (no unnecessary broadcasts)

---

## Frontend Implementation

### Team Dashboard
**File**: `resources/views/team/dashboard.blade.php`

Features:
- ✅ Real-time timer countdown (updates every second)
- ✅ Auto-refresh on player pick
- ✅ Auto-refresh on turn change
- ✅ Auto-refresh on round completion

### Admin Teams View
**File**: `resources/views/admin/teams.blade.php`

Features:
- ✅ Real-time broadcast listening on draft tab
- ✅ Auto-reload when events occur
- ✅ Existing timer countdown maintained
- ✅ Works with multiple admin viewers

---

## Troubleshooting

### WebSocket Connection Failed
**Problem**: Console shows `WebSocket connection failed`

**Solutions**:
1. Ensure Reverb server is running:
   ```powershell
   php artisan reverb:start
   ```

2. Check port 8080 is not blocked:
   ```powershell
   netstat -ano | findstr :8080
   ```

3. Verify `.env` settings match your environment:
   ```env
   REVERB_HOST=localhost
   REVERB_PORT=8080
   REVERB_SCHEME=http
   ```

4. Check browser console for specific errors (F12 → Console)

### Events Not Being Received
**Problem**: Broadcasts are dispatched but not received

**Solutions**:
1. Verify `BROADCAST_DRIVER=reverb` in `.env`
2. Check that Reverb server console shows message like:
   ```
   INFO Connected client from 127.0.0.1
   ```
3. Verify channel name matches: `draft.{league_type}.{category_id}`
4. Clear browser cache and reload

### Page Keeps Refreshing
**Problem**: Pages reload continuously

**Solutions**:
1. Check for JavaScript errors (F12 → Console)
2. Verify events are actually being dispatched in controller logs
3. Check that `location.reload()` isn't being called repeatedly in JavaScript
4. Consider removing auto-reload and using targeted DOM updates instead

---

## Production Deployment

For production, you should:

1. **Use Pusher Cloud** (has free tier):
   - Sign up at https://pusher.com
   - Get free credentials (up to 100 connections)
   - Update `.env`:
     ```env
     BROADCAST_DRIVER=pusher
     PUSHER_APP_ID=your-app-id
     PUSHER_APP_KEY=your-app-key
     PUSHER_APP_SECRET=your-app-secret
     PUSHER_APP_CLUSTER=mt1
     ```

2. **Or Deploy Reverb to VPS**:
   - Keep Reverb running in background with supervisor or systemd
   - Use WSS (secure WebSocket) with SSL certificate
   - Update `.env`:
     ```env
     REVERB_HOST=your-domain.com
     REVERB_SCHEME=https
     ```

3. **Scale Reverb**:
   ```bash
   php artisan reverb:start --port=8080 --workers=4
   ```

---

## API Reference for Events

### Dispatching Events in Code

```php
use App\Events\PlayerPicked;
use App\Models\DraftPick;

// After creating a draft pick
$draftPick = DraftPick::create([...]);
PlayerPicked::dispatch($draftPick);
```

### Listening in JavaScript

```javascript
// Listen to a specific channel
window.Echo.channel('draft.male.1')
    .listen('PlayerPicked', (data) => {
        console.log('Player picked:', data.participant_name);
    })
    .listen('TurnChanged', (data) => {
        console.log('New turn:', data.current_team_id);
    });

// Leave channel
window.Echo.leave('draft.male.1');
```

---

## Files Modified/Created

- ✅ `app/Events/PlayerPicked.php` - New event class
- ✅ `app/Events/TurnChanged.php` - New event class
- ✅ `app/Events/RoundCompleted.php` - New event class
- ✅ `app/Http/Controllers/TeamController.php` - Updated to dispatch events
- ✅ `config/broadcasting.php` - Broadcasting driver configuration
- ✅ `resources/js/bootstrap.js` - Laravel Echo setup
- ✅ `resources/views/team/dashboard.blade.php` - Real-time listening
- ✅ `resources/views/admin/teams.blade.php` - Real-time listening
- ✅ `.env` - Broadcasting and Reverb configuration
- ✅ `package.json` - Added `pusher-js` and `laravel-echo`

---

## Monitoring & Debugging

### Enable Broadcasting Logs
Edit `config/logging.php`:
```php
'channels' => [
    'broadcasting' => [
        'driver' => 'single',
        'path' => storage_path('logs/broadcasting.log'),
    ],
],
```

### Check Reverb Connections
The Reverb server console shows:
```
INFO Connected client from 192.168.1.100
INFO Broadcasting to channel: draft.male.1
INFO Disconnected client from 192.168.1.100
```

### Test Broadcasting Manually
```bash
php artisan tinker

# Dispatch a test event
use App\Events\TurnChanged;
use App\Models\DraftRound;
$round = DraftRound::find(1);
TurnChanged::dispatch($round, '2');
```

---

## FAQ

**Q: Do I need to run Reverb for development?**
A: Yes, the Reverb WebSocket server must be running for real-time features to work.

**Q: Can I use Pusher Cloud instead?**
A: Yes! Follow the "Production Deployment" section above.

**Q: Will this work behind a firewall/proxy?**
A: WebSockets need proper configuration. If port 8080 is blocked, you may need to use WSS (secure WebSocket) on port 443.

**Q: What if I want polling instead of WebSockets?**
A: Revert to original polling implementation by removing the real-time listeners and using tickRound() API calls.

**Q: How many concurrent connections does Reverb support?**
A: Limited mainly by server resources. For small leagues (< 100 teams), local Reverb is sufficient.

---

## Next Steps

1. ✅ Start Reverb server: `php artisan reverb:start`
2. ✅ Open draft round and make a pick
3. ✅ Watch pages auto-update with real-time events
4. ✅ Monitor browser console for any issues
5. ✅ Consider upgrading to Pusher Cloud for production

Enjoy real-time draft updates! 🎉
