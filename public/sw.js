const CACHE_NAME = 'totthobox-ultimate-v17'; // ভার্সন আপডেট করা হয়েছে
const OFFLINE_URL = '/offline.html';

const PRECACHE_ASSETS = [
    '/',
    OFFLINE_URL,
    '/favicon.ico',
    '/manifest.json',
    '/css/app.css', // আপনার মেইন সিএসএস এবং জেএস এখানে দিন
    '/js/app.js'
];

// ১. ইনস্টল
self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE_ASSETS))
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

// ৩. স্মার্ট ফেচ (The Advanced Part)
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

// সব POST রিকোয়েস্ট ক্যাশ থেকে বাদ দেওয়া (লাইভওয়্যার POST রিকোয়েস্ট বেশি ব্যবহার করে)
if (request.method !== 'GET') {
    return; // এটি লাইভওয়্যারের সব ডাটা সাবমিশনকে নিরাপদ রাখবে
}

    // Livewire, Debugbar এবং API কলগুলো ক্যাশ হবে না (নেটওয়ার্ক অনলি)
    if (url.pathname.includes('/livewire/') || url.pathname.includes('_debugbar') || url.pathname.includes('/api/')) {
        return;
    }

    // --- স্ট্র্যাটেজি ১: স্ট্যাটিক ফাইল (Cache-First) ---
    if (['style', 'script', 'font', 'image'].includes(request.destination)) {
        event.respondWith(
            caches.match(request).then((cached) => {
                return cached || fetch(request).then((response) => {
                    const copy = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                    return response;
                });
            })
        );
        return;
    }

    // --- স্ট্র্যাটেজি ২: HTML পেজ (Stale-While-Revalidate) ---
    // এটি সবচেয়ে অ্যাডভান্সড: আগে ক্যাশ থেকে পেজ দেখাবে (খুব দ্রুত), 
    // আবার ব্যাকগ্রাউন্ডে নেট থেকে নতুন ডাটা এনে ক্যাশ আপডেট করে রাখবে।
    event.respondWith(
        caches.match(request).then((cached) => {
            const networkFetch = fetch(request).then((networkResponse) => {
                if (networkResponse.status === 200) {
                    const copy = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                }
                return networkResponse;
            }).catch(() => {
                if (request.mode === 'navigate') return caches.match(OFFLINE_URL);
            });

            return cached || networkFetch;
        })
    );
});