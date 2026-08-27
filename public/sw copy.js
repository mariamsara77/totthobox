importScripts('https://storage.googleapis.com/workbox-cdn/releases/6.4.1/workbox-sw.js');

if (workbox) {
    console.log("✅ Totthobox Professional SW Active!");

    // ১. Force Update: SW update hole jate instant kaj kore
    self.addEventListener('install', () => self.skipWaiting());
    self.addEventListener('activate', () => self.clients.claim());

    // ২. Static Assets Caching (JS, CSS, Fonts, Images)
    workbox.routing.registerRoute(
        ({request}) => 
            request.destination === 'style' || 
            request.destination === 'script' || 
            request.destination === 'font' || 
            request.destination === 'image',
        new workbox.strategies.CacheFirst({
            cacheName: 'totthobox-assets',
            plugins: [
                new workbox.expiration.ExpirationPlugin({ maxEntries: 200, maxAgeSeconds: 30 * 24 * 60 * 60 })
            ],
        })
    );

    // ৩. Every Page Caching (The Heart of your Request)
    // Ei strategy-ti prottekta visited page-ke automatic save korbe
    workbox.routing.registerRoute(
        ({request, url}) => request.mode === 'navigate' || url.origin === self.location.origin,
        new workbox.strategies.NetworkFirst({
            cacheName: 'totthobox-pages-cache',
            networkTimeoutSeconds: 3, // ৩ সেকেন্ডের modhe net na পেলে cache theke load korbe
            plugins: [
                new workbox.cacheableResponse.CacheableResponsePlugin({
                    statuses: [0, 200],
                }),
            ],
        })
    );

    // ৪. Offline Fallback
    workbox.recipes.offlineFallback({ pageFallback: '/offline' });
}