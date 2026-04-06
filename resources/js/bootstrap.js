import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Setup Laravel Echo for real-time broadcasting using Reverb
// This initializes WebSocket connection to the Reverb server
(async function initializeEcho() {
    try {
        console.log('Starting Echo initialization...');
        
        // Try importing with detailed error handling
        let Echo, Pusher;
        
        try {
            console.log('  Importing laravel-echo...');
            const echoModule = await import('laravel-echo');
            Echo = echoModule.default;
            console.log('  ✓ laravel-echo imported successfully');
        } catch (err) {
            // bootstrap.js:75 ✗✗✗ REAL-TIME BROADCASTING FAILED ✗✗✗
            console.error('  ✗ Failed to import laravel-echo:', err.message || err);
            throw new Error('laravel-echo import failed: ' + (err.message || 'unknown error'));
        }
        
        try {
            console.log('  Importing pusher-js...');
            const pusherModule = await import('pusher-js');
            Pusher = pusherModule.default;
            console.log('  ✓ pusher-js imported successfully');
        } catch (err) {
            console.error('  ✗ Failed to import pusher-js:', err.message || err);
            throw new Error('pusher-js import failed: ' + (err.message || 'unknown error'));
        }
        
        if (!Echo || !Pusher) {
            throw new Error('Echo or Pusher modules are null/undefined after import');
        }
        
        // Build WebSocket URL from Reverb environment variables
        const reverbHost = import.meta.env.VITE_REVERB_HOST || 'localhost';
        const reverbPort = import.meta.env.VITE_REVERB_PORT || 8080;
        const reverbScheme = import.meta.env.VITE_REVERB_SCHEME || 'http';
        const appKey = import.meta.env.VITE_REVERB_APP_KEY || 'app-key';
        
        // Map http -> ws, https -> wss
        const wsScheme = reverbScheme === 'https' ? 'wss' : 'ws';
        
        console.log('Initializing Laravel Echo...');
        console.log('  Reverb Host: ' + reverbHost);
        console.log('  Reverb Port: ' + reverbPort);
        console.log('  Reverb Scheme: ' + reverbScheme + ' (WebSocket: ' + wsScheme + ')');
        
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: appKey,
            wsHost: reverbHost,
            wsPort: reverbPort,
            wssPort: reverbPort,
            forceTLS: reverbScheme === 'https',
            encrypted: reverbScheme === 'https',
            enabledTransports: ['ws', 'wss'],
            Pusher: Pusher,
        });
        
        window.Pusher = Pusher;
        window.EchoReady = true;
        
        // Log success
        console.log('✓✓✓ REAL-TIME BROADCASTING ACTIVE ✓✓✓');
        console.log('  WebSocket URL: ' + wsScheme + '://' + reverbHost + ':' + reverbPort);
        console.log('  Connected via Laravel Echo + Pusher + Reverb');
        
    } catch (error) {
        window.EchoReady = false;
        console.error('✗✗✗ REAL-TIME BROADCASTING FAILED ✗✗✗');
        console.error('Error: ' + (error.message || error));
        console.error('');
        console.error('Troubleshooting steps:');
        console.error('  1. Check if node_modules exists: ls node_modules/laravel-echo');
        console.error('  2. Check npm packages: npm list laravel-echo pusher-js');
        console.error('  3. Reinstall packages: npm install');
        console.error('  4. Clear Vite cache: rm -r node_modules/.vite');
        console.error('  5. Restart Vite: npm run dev');
        console.error('');
        console.error('Make sure you have:');
        console.error('  - Node.js and npm installed');
        console.error('  - Laravel Reverb running (php artisan reverb:start)');
        console.error('  - Vite dev server running (npm run dev)');
    }
})();
