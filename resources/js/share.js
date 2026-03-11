const initShareButtons = () => {
    // শুধুমাত্র সেই বাটনগুলো ধরবে যেগুলোতে এখনও ইভেন্ট বাইন্ড করা হয়নি
    const buttons = document.querySelectorAll('[data-share-button]:not([data-bound])');

    buttons.forEach(btn => {
        btn.dataset.bound = true; // ডুপ্লিকেট বাইন্ডিং রোধ করতে

        btn.addEventListener('click', async (e) => {
            e.preventDefault();

            const url = btn.getAttribute('data-url') || window.location.href;
            const title = btn.getAttribute('data-title') || document.title;
            const text = btn.getAttribute('data-text') || 'Check this out!';

            if (navigator.share) {
                try {
                    await navigator.share({ title, text, url });
                } catch (err) {
                    if (err.name !== 'AbortError') console.error('Share failed:', err);
                }
            } else {
                // Desktop Fallback
                const fallbackUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
                window.open(fallbackUrl, '_blank', 'width=600,height=400');
            }
        });
    });
};

// যখন পেজ লোড হবে বা Livewire নেভিগেট করবে
document.addEventListener('livewire:navigated', initShareButtons);
document.addEventListener('DOMContentLoaded', initShareButtons);