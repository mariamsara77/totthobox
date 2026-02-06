import './tracking';
import './echo';
import './share';
import './sw-register';
import './pwa-handle';
import './quil-editor';


import { initGoogleTranslate } from './google-translator';

// প্রথমবার লোড
initGoogleTranslate();

// Livewire পেজ পরিবর্তন করলে
document.addEventListener('livewire:navigated', () => {
    initGoogleTranslate();
    
    // যদি কুকিতে ভাষা সেভ থাকে, তবে অটোমেটিক সেট করার চেষ্টা করবে
    const match = document.cookie.match(/googtrans=\/bn\/([^;]+)/);
    if (match && match[1] !== 'bn') {
        setTimeout(() => window.changeAppLanguage(match[1]), 500);
    }
});
import db from './db';

// ১. গ্লোবাল ক্লিক লিসেনার (অটোমেটিক ক্লিক ডাটা সেভ করবে)
document.addEventListener('click', async (event) => {
    const el = event.target.closest('button, a, input[type=radio]');
    if (!el) return;

    const interaction = {
        type: 'click',
        key: el.id || el.name || 'btn_' + Math.floor(Math.random() * 1000), // অটো আইডি জেনারেশন
        value: el.innerText.trim() || el.value || 'clicked',
        timestamp: new Date().getTime()
    };

    // Dexie-তে সেভ করা
    await db.activities.add(interaction);
    
    if (navigator.onLine) {
        syncDataWithServer();
    }
});

// ২. অটোমেটিক ইনপুট ট্র্যাকার (MCQ বা ফর্মের জন্য)
document.addEventListener('input', async (event) => {
    const interaction = {
        type: 'input',
        key: event.target.name || event.target.id || 'unknown_field',
        value: event.target.value,
        timestamp: new Date().getTime()
    };

    // এটি শুধু বর্তমান ইনপুটকে একটি নির্দিষ্ট কী-তে আপডেট রাখবে
    await db.activities.put({ id: 'live_draft', ...interaction });
});

// ৩. সার্ভার সিঙ্ক ফাংশন (Professional Logic)
async function syncDataWithServer() {
    // ডেক্সি থেকে সব ডাটা নেওয়া
    const drafts = await db.activities.toArray();
    if (drafts.length === 0) return;

    try {
        const response = await fetch('/api/sync-offline-data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            body: JSON.stringify({ data: drafts })
        });

        if (response.ok) {
            // সার্ভারে চলে গেলে ব্রাউজার ডাটাবেস ক্লিন করা
            await db.activities.clear();
            console.log('Sync Success: Browser DB Cleared');
        }
    } catch (e) {
        console.warn('Sync Failed: Saving for later.');
    }
}

// ৪. অটো সিঙ্ক ইভেন্ট (নেট আসলে বা পেজ ন্যাভিগেট করলে)
window.addEventListener('online', syncDataWithServer);
document.addEventListener('livewire:navigated', syncDataWithServer);