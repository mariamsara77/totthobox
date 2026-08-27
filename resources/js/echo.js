import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: 443, // Nginx রিসিভ করবে
    wssPort: 443,
    forceTLS: true, // এখন এটি কাজ করবে কারণ Nginx SSL হ্যান্ডেল করবে
    enabledTransports: ['ws', 'wss'],
});
