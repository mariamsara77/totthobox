// ১. ফাংশনটি গ্লোবাল রাখুন যাতে যেকোনো জায়গা থেকে কল করা যায়
window.syncPwaStatus = function() {
    const isPWA = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    
    fetch('/api/tracking/sync-pwa', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-App-Mode': isPWA ? 'standalone' : 'browser',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
        },
        body: JSON.stringify({ is_pwa: isPWA }) // পেলোড সহ পাঠানো নিরাপদ
    })
    .then(response => response.json())
    .then(data => {
        // লাইভওয়্যার কম্পোনেন্টকে জানানো যে স্ট্যাটাস আপডেট হয়েছে (ঐচ্ছিক)
        if (window.Livewire) {
            window.Livewire.dispatch('pwa-status-synced', { status: isPWA });
        }
    })
    .catch(err => console.error('PWA Sync Error:', err));
};

// ২. পেজ ইনিশিয়াল লোড এবং লাইভওয়্যার নেভিগেশনের জন্য লিসেনার
document.addEventListener('livewire:navigated', () => {
    window.syncPwaStatus();
});

// ৩. প্রথমবারের জন্য (যদি livewire:navigated দেরি করে)
document.addEventListener('DOMContentLoaded', () => {
    window.syncPwaStatus();
});

// ৪. অ্যাপ মোড লাইভ চেঞ্জ হলে (Install/Uninstall/Switch)
window.matchMedia('(display-mode: standalone)').addEventListener('change', (e) => {
    window.syncPwaStatus();
});