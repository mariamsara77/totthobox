importScripts('https://storage.googleapis.com/workbox-cdn/releases/6.4.1/workbox-sw.js');

if (workbox) {
    console.log('✅ Totthobox SW Active (Smart Caching)');

    self.skipWaiting();
    workbox.core.clientsClaim();

    // Google Translate → always network (optional, চাইলে রাখতে পারো)
    workbox.routing.registerRoute(
        ({ url }) =>
            url.hostname.includes('translate.google.com') ||
            url.hostname.includes('translate.googleapis.com'),
        new workbox.strategies.NetworkOnly()
    );

    // 1. Static Assets (Vite, Livewire, Flux, CSS, JS, Font, Image)
    // CacheFirst → একবার load হলে চিরকাল cache থেকে আসবে (hash থাকলে perfect)
    workbox.routing.registerRoute(
        ({ request, url }) =>
            url.origin === self.location.origin &&
            (request.destination === 'style' ||
                request.destination === 'script' ||
                request.destination === 'font' ||
                request.destination === 'image' ||
                request.destination === 'worker'),
        new workbox.strategies.CacheFirst({
            cacheName: 'totthobox-assets-v1',
            plugins: [
                new workbox.expiration.ExpirationPlugin({
                    maxEntries: 300,
                    maxAgeSeconds: 60 * 24 * 60 * 60, // 60 days
                }),
                new workbox.cacheableResponse.CacheableResponsePlugin({
                    statuses: [0, 200],
                }),
            ],
        })
    );

    // 2. Page / Navigation → NetworkFirst
    // নেট থাকলে নতুন data আনবে, না থাকলে cache থেকে দিবে
    workbox.routing.registerRoute(
        ({ request, url }) =>
            url.origin === self.location.origin &&
            (request.mode === 'navigate' || request.destination === 'document'),
        new workbox.strategies.NetworkFirst({
            cacheName: 'totthobox-pages-v1',
            networkTimeoutSeconds: 4,
            plugins: [
                new workbox.cacheableResponse.CacheableResponsePlugin({
                    statuses: [200],
                }),
                new workbox.expiration.ExpirationPlugin({
                    maxEntries: 50,
                    maxAgeSeconds: 7 * 24 * 60 * 60, // 7 days
                }),
            ],
        })
    );

    // 3. Offline fallback
    workbox.recipes.offlineFallback({
        pageFallback: '/offline',
    });
}