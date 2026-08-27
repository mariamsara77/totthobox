document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-share-button]');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    const url = btn.getAttribute('data-url') || window.location.href;
    const title = btn.getAttribute('data-title') || document.title;
    const description = btn.getAttribute('data-text') || '';
    const combinedText = description ? `${title}\n${description}` : title;

    if (navigator.share) {
        try {
            await navigator.share({
                title: title,
                text: combinedText,
                url: url,
            });
        } catch (err) {
            if (err.name !== 'AbortError') console.error('Share failed:', err);
        }
    } else {
        const fallbackUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
        window.open(fallbackUrl, '_blank', 'width=600,height=400');
    }
});