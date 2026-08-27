/**
 * Ultra-Optimized PWA Status Tracker
 */
(() => {
    let lastSyncedStatus = null;
    let syncTimer = null;

    const isPwaMode = () =>
        window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

    window.syncPwaStatus = () => {
        const isPWA = isPwaMode();
        if (lastSyncedStatus === isPWA) return;
        lastSyncedStatus = isPWA;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-App-Mode': isPWA ? 'standalone' : 'browser',
        };

        if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken;

        fetch('/api/tracking/sync-pwa', {
            method: 'POST',
            headers,
            body: JSON.stringify({ is_pwa: isPWA }),
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && window.Livewire) {
                    window.Livewire.dispatch('pwa-status-synced', { status: isPWA });
                }
            })
            .catch(() => {
                lastSyncedStatus = null; // Retry on failure
            });
    };

    const debouncedSync = () => {
        clearTimeout(syncTimer);
        syncTimer = setTimeout(window.syncPwaStatus, 200);
    };

    // Initial page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', debouncedSync, { once: true });
    } else {
        debouncedSync();
    }

    // Livewire SPA navigation
    document.addEventListener('livewire:navigated', debouncedSync);

    // Display mode change listener
    window.matchMedia('(display-mode: standalone)').addEventListener('change', () => {
        lastSyncedStatus = null;
        window.syncPwaStatus();
    });
})();