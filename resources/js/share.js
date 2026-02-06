function initShareButtons() {
    const buttons = document.querySelectorAll('[data-share-button]');
    buttons.forEach(btn => {
        if (btn.dataset.bound) return; // Prevent duplicate binding
        btn.dataset.bound = true;

        btn.addEventListener('click', async () => {
            const url = btn.dataset.url || window.location.href;
            const title = btn.dataset.title || document.title;
            const text = btn.dataset.text || 'Check this out!';

            const shareData = { title, text, url };

            if (navigator.share) {
                try {
                    await navigator.share(shareData);
                    console.log('Shared successfully');
                } catch (err) {
                    console.error('Error sharing:', err);
                }
            } else {
                // Desktop fallback (Facebook)
                const fallbackUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
                window.open(fallbackUrl, '_blank');
            }
        });
    });
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', initShareButtons);

// Re-initialize after Livewire updates
if (window.Livewire) {
    window.Livewire.hook('message.processed', () => {
        initShareButtons();
    });
}
