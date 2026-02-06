import './tracking';
import './echo';
import './share';
import './sw-register';
import './pwa-handle';
import './quil-editor';
import db from './db';
import { initGoogleTranslate } from './google-translator';

// --- ১. Google Translate Logic ---
initGoogleTranslate();

document.addEventListener('livewire:navigated', () => {
    initGoogleTranslate();
    const match = document.cookie.match(/googtrans=\/bn\/([^;]+)/);
    if (match && match[1] !== 'bn') {
        setTimeout(() => window.changeAppLanguage?.(match[1]), 500);
    }
});

// --- ২. Sync Logic (সার্ভারে ডাটা পাঠানো) ---
const syncDataWithServer = async () => {
    if (!navigator.onLine) return;

    const drafts = await db.activities.toArray();
    if (drafts.length === 0) return;

    try {
        const response = await fetch('/api/sync-offline-data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            },
            body: JSON.stringify({ data: drafts })
        });

        if (response.ok) {
            await db.activities.clear();
            console.log('✅ Offline data synced successfully!');
        }
    } catch (e) {
        console.warn('❌ Sync failed, will retry later.');
    }
};

// --- ৩. ক্লিক ট্র্যাকার (Specific Elements) ---
document.addEventListener('click', async (event) => {
    const el = event.target.closest('[data-track]'); 
    if (!el) return;

    await db.activities.add({
        type: 'interaction',
        key: el.getAttribute('data-track') || el.id || 'unnamed_btn',
        value: el.innerText.trim() || el.value,
        timestamp: Date.now()
    });

    syncDataWithServer();
});

// --- ৪. ইনপুট ট্র্যাকার (MCQ বা ফর্মের ড্রাফট সেভ করা) ---
// এটি বাদ দেওয়ার দরকার নেই, তবে 'live_draft' আইডি ব্যবহার করলে এটি শুধু একটি ডাটাই বারবার আপডেট করবে।
document.addEventListener('input', async (event) => {
    const { name, id, value } = event.target;
    if (!name && !id) return;

    const interaction = {
        id: 'live_draft', // এটি দিলে আগের ড্রাফট মুছে নতুনটা বসবে, ডাটাবেস বড় হবে না।
        type: 'input_draft',
        key: name || id,
        value: value,
        timestamp: Date.now()
    };

    await db.activities.put(interaction);
});

// --- ৫. অটো সিঙ্ক ইভেন্টস ---
window.addEventListener('online', syncDataWithServer);
document.addEventListener('livewire:navigated', syncDataWithServer);