import './tracking';
import './echo';
import './share';
import './sw-register';
import './pwa-handle';
import './quil-editor';
import db from './db';
import { initGoogleTranslate } from './google-translator';

// --- Google Translate Logic ---
const handleTranslate = () => {
    initGoogleTranslate();
    const match = document.cookie.match(/googtrans=\/bn\/([^;]+)/);
    if (match && match[1] !== 'bn') {
        setTimeout(() => window.changeAppLanguage?.(match[1]), 500);
    }
};

initGoogleTranslate();
document.addEventListener('livewire:navigated', handleTranslate);

// --- Offline Data Tracking & Sync Logic ---

let syncTimeout = null;

const syncDataWithServer = async () => {
    if (!navigator.onLine) return;

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
            await db.activities.clear();
            console.log('✅ Sync Success');
        }
    } catch (e) {
        console.warn('❌ Sync Failed');
    }
};

// ডাটা সেভ করার ফাংশন
const logActivity = async (type, key, value) => {
    await db.activities.add({
        type,
        key,
        value,
        timestamp: Date.now()
    });

    // প্রতি ক্লিকে সিঙ্ক না করে ৫ সেকেন্ড পর পর করার চেষ্টা করবে (Performance Optimization)
    clearTimeout(syncTimeout);
    syncTimeout = setTimeout(syncDataWithServer, 5000); 
};

// ১. গ্লোবাল ক্লিক লিসেনার
document.addEventListener('click', (event) => {
    const el = event.target.closest('button, a, input[type=radio], input[type=checkbox]');
    if (!el) return;

    const key = el.id || el.name || el.getAttribute('aria-label') || 'unnamed_element';
    const value = el.innerText.trim() || el.value || 'clicked';
    
    logActivity('click', key, value);
});

// ২. অটোমেটিক ইনপুট ট্র্যাকার (MCQ বা ফর্ম)
document.addEventListener('input', (event) => {
    const { name, id, value } = event.target;
    logActivity('input', name || id || 'form_field', value);
});

// ৩. অটো সিঙ্ক ইভেন্টস
window.addEventListener('online', syncDataWithServer);
document.addEventListener('livewire:navigated', syncDataWithServer);