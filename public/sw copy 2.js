const CACHE_NAME = 'totthobox-smart-v15';
const OFFLINE_URL = '/offline.html';

const PRECACHE_ASSETS = [
    '/',
    OFFLINE_URL,
    '/favicon.ico',
    '/manifest.json'
];

// ১. ইনস্টল এবং প্রিক্যাশ
self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return Promise.allSettled(
                PRECACHE_ASSETS.map(url => cache.add(url).catch(() => console.log(url + " failed to cache")))
            );
        })
    );
});

// ২. অ্যাক্টিভেট
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
        ))
    );
    self.clients.claim();
});

// ৩. স্মার্ট ফেচ (দ্রুত অফলাইন হ্যান্ডলিং)
self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET' ||
        request.url.includes('/livewire/message') ||
        request.url.includes('_debugbar')) return;

    // স্ট্যাটিক ফাইল (CSS, JS, Image) - Cache First Strategy
    if (request.destination === 'style' || request.destination === 'script' || request.destination === 'image' || request.destination === 'font') {
        event.respondWith(
            caches.match(request).then((cached) => {
                return cached || fetch(request).then((response) => {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(request, clone));
                    return response;
                }).catch(() => null); // অফলাইনে ছবি না থাকলে ব্ল্যাঙ্ক থাকবে
            })
        );
        return;
    }

    // HTML পেজ - Network First Strategy (দ্রুত ফেইল করার মেকানিজম সহ)
    event.respondWith(
        // ৩ সেকেন্ডের মধ্যে রেসপন্স না পেলে ক্যাশ বা অফলাইন পেজে চলে যাবে
        timeoutFetch(3000, fetch(request))
            .then((networkResponse) => {
                if (networkResponse.status === 200) {
                    const responseClone = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
                }
                return networkResponse;
            })
            .catch(() => {
                // নেটওয়ার্ক ফেইল করলে বা টাইমআউট হলে এখানে আসবে
                return caches.match(request).then((cachedResponse) => {
                    if (cachedResponse) return cachedResponse;

                    // ক্যাশে না থাকলে এবং এটি যদি পেজ নেভিগেশন হয়, সরাসরি অফলাইন পেজ
                    if (request.mode === 'navigate') {
                        return caches.match(OFFLINE_URL);
                    }
                });
            })
    );
});

/**
 * নেটওয়ার্ক স্লো থাকলে বেশিক্ষণ ওয়েট না করার ফাংশন
 */
function timeoutFetch(ms, promise) {
    return new Promise((resolve, reject) => {
        const timer = setTimeout(() => {
            reject(new Error('Network timeout'));
        }, ms);

        promise.then(
            (res) => {
                clearTimeout(timer);
                resolve(res);
            },
            (err) => {
                clearTimeout(timer);
                reject(err);
            }
        );
    });
}
