const CACHE_NAME = 'totthobox-smart-v12'; // ভার্সন পরিবর্তন করা হয়েছে যাতে পুরনো ভুল ক্যাশ ডিলিট হয়
const OFFLINE_URL = '/offline.html';

const CORE_ASSETS = [
    '/favicon.ico',
    '/manifest.json',
    OFFLINE_URL
];

// ১. ইনস্টল: কোর ফাইলগুলো প্রিক্যাশ করা
self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(CORE_ASSETS))
    );
});

// ২. অ্যাক্টিভেট: পুরনো ভার্সন ডিলিট করে নতুন ভার্সন চালু করা
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
        ))
    );
    self.clients.claim();
});

// ৩. স্মার্ট ফেচ: Network-First Strategy (মেইন সমাধান)
self.addEventListener('fetch', (event) => {
    const { request } = event;

    // শুধুমাত্র GET রিকোয়েস্ট এবং নির্দিষ্ট ফাইল টাইপ হ্যান্ডেল করা
    if (request.method !== 'GET') return;

    // Livewire এর ইন্টারনাল মেসেজ ক্যাশ করা যাবে না (এটি করলে পেজ আপডেট হয় না)
    if (request.url.includes('/livewire/message') || request.url.includes('_debugbar')) return;

    event.respondWith(
        fetch(request)
            .then((networkResponse) => {
                // ইন্টারনেট আছে: লেটেস্ট ডাটা ক্যাশে আপডেট করো এবং রিটার্ন করো
                if (networkResponse && networkResponse.status === 200) {
                    const responseClone = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
                }
                return networkResponse;
            })
            .catch(() => {
                // ইন্টারনেট নেই: এখন ক্যাশ থেকে ডাটা খোঁজো
                return caches.match(request).then((cachedResponse) => {
                    if (cachedResponse) return cachedResponse;

                    // যদি ক্যাশে না থাকে এবং এটি একটি পেজ নেভিগেশন হয়, তবে অফলাইন পেজ
                    if (request.mode === 'navigate') {
                        return caches.match(OFFLINE_URL);
                    }
                });
            })
    );
});
