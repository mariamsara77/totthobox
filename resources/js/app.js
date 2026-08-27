// শুধু পাবলিক সাইটের জন্য দরকারি জিনিস
import './tracking';
import './share';
import './pwa-tracking';
import './pwa-handle';

// পেজ পুরোপুরি লোড হওয়ার পর ব্যাকগ্রাউন্ডে PWA রেজিস্টার হবে
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .catch(err => console.error('SW registration failed', err));
    }, { once: true });
}

// লাইভওয়্যার নেভিগেশন এবং প্রথম লোডে PWA স্ট্যাটাস সিঙ্ক ফাংশন কল
const handleSync = () => {
    if (typeof window.syncPwaStatus === 'function') {
        window.syncPwaStatus();
    }
};

document.addEventListener('livewire:navigated', handleSync);
handleSync();

// Theme-color আপডেট (ডার্ক/লাইট) - পারফরম্যান্স অপ্টিমাইজড মিউটেশন অবজারভার
const observer = new MutationObserver((mutations) => {
    for (const mutation of mutations) {
        if (mutation.attributeName === 'class') {
            const isDark = document.documentElement.classList.contains('dark');
            const meta = document.querySelector('meta[name="theme-color"]');
            if (meta) {
                meta.setAttribute('content', isDark ? '#262626' : '#ffffff');
            }
        }
    }
});

observer.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class']
});