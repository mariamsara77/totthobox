/**
 * Totthobox Advanced Service Worker - v16.0
 * Strategy: High-Performance PWA Hybrid
 */

const CACHE_NAME = 'totthobox-pro-v16';
const OFFLINE_URL = '/offline.html';

const ASSETS_TO_CACHE = [
    '/',
    OFFLINE_URL,
    '/favicon.ico',
    '/manifest.json',
    '/css/app.css', // আপনার মেইন সিএসএস পাথ অনুযায়ী পরিবর্তন করুন
    '/js/app.js'    // আপনার মেইন জেএস পাথ অনুযায়ী পরিবর্তন করুন
];

// ১. ইন্সটল এবং প্রি-ক্যাশিং
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('[SW] Pre-caching critical assets');
            return cache.addAll(ASSETS_TO_CACHE);
        })
    );
    self.skipWaiting();
});

// ২. অ্যাক্টিভেট এবং পুরোনো ক্যাশ ক্লিনিং
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if (key !== CACHE_NAME) {
                        console.log('[SW] Removing old cache:', key);
                        return caches.delete(key);
                    }
                })
            );
        })
    );
    return self.clients.claim();
});

// ৩. স্মার্ট ফেচিং স্ট্রেটেজি
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // ডাইনামিক এবং সেন্সিটিভ রুট বাদ দেওয়া (Admin, Livewire, CSRF ইত্যাদি)
    if (
        request.method !== 'GET' ||
        url.pathname.includes('/livewire/') ||
        url.pathname.includes('/admin/') ||
        url.pathname.includes('/filament') ||
        url.pathname.includes('/telescope') ||
        url.search.includes('_debugbar')
    ) {
        return; // সরাসরি নেটওয়ার্ক থেকে কাজ করবে
    }

    // স্ট্র্যাটেজি ১: স্ট্যাটিক এসেট (Images, Fonts, Scripts) -> Stale-While-Revalidate
    // আগে ক্যাশ থেকে দেখাবে, কিন্তু ব্যাকগ্রাউন্ডে নেটওয়ার্ক থেকে আপডেট করে রাখবে পরের বারের জন্য।
    if (['image', 'font', 'style', 'script'].includes(request.destination)) {
        event.respondWith(staleWhileRevalidate(request));
        return;
    }

    // স্ট্র্যাটেজি ২: মেইন পেজ/HTML -> Network-First with Fast Timeout
    // ৩ সেকেন্ডের মধ্যে নেটওয়ার্ক না পেলে ক্যাশ দেখাবে।
    event.respondWith(networkFirstWithTimeout(request, 3000));
});

/**
 * Stale-While-Revalidate: পারফরম্যান্সের জন্য সেরা।
 * ক্যাশ দেখাবে + ব্যাকগ্রাউন্ডে আপডেট করবে।
 */
async function staleWhileRevalidate(request) {
    const cache = await caches.open(CACHE_NAME);
    const cachedResponse = await cache.match(request);

    const networkFetch = fetch(request).then((networkResponse) => {
        if (networkResponse && networkResponse.status === 200) {
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    }).catch(() => null);

    return cachedResponse || networkFetch;
}

/**
 * Network-First with Timeout: কন্টেন্ট আপডেটেড রাখার জন্য সেরা।
 */
async function networkFirstWithTimeout(request, timeoutMs) {
    const cache = await caches.open(CACHE_NAME);

    try {
        // রেস শুরু: নেটওয়ার্ক বনাম টাইমআউট
        const networkResponse = await Promise.race([
            fetch(request),
            new Promise((_, reject) => setTimeout(() => reject(new Error('Timeout')), timeoutMs))
        ]);

        if (networkResponse && networkResponse.status === 200) {
            cache.put(request, networkResponse.clone());
            return networkResponse;
        }
    } catch (error) {
        // নেটওয়ার্ক ফেইল বা টাইমআউট হলে ক্যাশ খুঁজবে
        const cachedResponse = await cache.match(request);
        if (cachedResponse) return cachedResponse;

        // যদি ক্যাশেও না থাকে এবং ইউজার কোনো পেজ নেভিগেট করতে চায়
        if (request.mode === 'navigate') {
            return cache.match(OFFLINE_URL);
        }
    }
}