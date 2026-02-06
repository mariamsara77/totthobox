/**
 * Optimized Service Worker Registration
 */
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(reg => {
                // যখনই নতুন কোনো আপডেট আসবে
                reg.addEventListener('updatefound', () => {
                    const newWorker = reg.installing;
                    if (newWorker) {
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                // নতুন আপডেট পাওয়া গেছে, ইউজারকে নোটিফাই করে পেজ রিফ্রেশ
                                console.log('PWA: New content is available; please refresh.');
                                window.location.reload();
                            }
                        });
                    }
                });
            })
            .catch(error => {
                console.error('PWA: Service Worker registration failed:', error);
            });
    });
}
