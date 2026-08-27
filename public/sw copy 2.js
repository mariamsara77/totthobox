importScripts('https://storage.googleapis.com/workbox-cdn/releases/6.4.1/workbox-sw.js');

if (workbox) {
    console.log("✅ Totthobox Professional SW Active!");

    // ১. Force Update
    self.addEventListener('install', () => self.skipWaiting());
    self.addEventListener('activate', () => self.clients.claim());

    // 💥 এক্সক্লুসিভ রুট: গুগল ট্রান্সলেটের সব রিকোয়েস্ট সরাসরি নেটওয়ার্ক থেকে নিবে (কোনো ক্যাশ করবে না)
    workbox.routing.registerRoute(
        ({ url }) => url.hostname.includes('translate.google.com') ||
            url.hostname.includes('translate.googleapis.com'),
        new workbox.strategies.NetworkOnly()
    );

    // ২. Static Assets Caching (JS, CSS, Fonts, Images)
    // শুধুমাত্র নিজের অরিজিনের অ্যাসেট ক্যাশ করার জন্য সেফটি চেক যুক্ত করা হয়েছে
    workbox.routing.registerRoute(
        ({ request, url }) =>
            url.origin === self.location.origin && (
                request.destination === 'style' ||
                request.destination === 'script' ||
                request.destination === 'font' ||
                request.destination === 'image'
            ),
        new workbox.strategies.CacheFirst({
            cacheName: 'totthobox-assets',
            plugins: [
                new workbox.expiration.ExpirationPlugin({ maxEntries: 200, maxAgeSeconds: 30 * 24 * 60 * 60 })
            ],
        })
    );

    // ৩. Every Page Caching
    // থার্ড পার্টি রিকোয়েস্ট যেন এখানে না ঢোকে, তাই strict self origin চেক করা হয়েছে
    workbox.routing.registerRoute(
        ({ request, url }) => url.origin === self.location.origin &&
            (request.mode === 'navigate' || request.destination === 'document'),
        new workbox.strategies.NetworkFirst({
            cacheName: 'totthobox-pages-cache',
            networkTimeoutSeconds: 3,
            plugins: [
                new workbox.cacheableResponse.CacheableResponsePlugin({
                    statuses: [200], // ওপেকের (0) ঝামেলা এড়াতে শুধু সফল 200 রেসপন্স রাখা নিরাপদ
                }),
            ],
        })
    );

    self.addEventListener('push', function (event) {
        if (!(self.Notification && self.Notification.permission === 'granted')) {
            return;
        }

        const data = event.data ? event.data.json() : {};

        event.waitUntil(
            self.registration.showNotification(data.title || 'Totthobox', {
                body: data.body || 'নতুন নোটিফিকেশন',
                icon: '/web-app-manifest-192x192.png',
                badge: '/web-app-manifest-192x192.png'
            })
        );
    });

    self.addEventListener('notificationclick', function (event) {
        event.notification.close();
        event.waitUntil(
            clients.openWindow('/') // ক্লিক করলে যেখানে যাবে
        );
    });

    // ৪. Offline Fallback
    workbox.recipes.offlineFallback({ pageFallback: '/offline' });
}