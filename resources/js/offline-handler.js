import db from './db';

// ১. ডাটা লোকাল ডাটাবেজে জমা করা
export const trackActivity = async (type, key, value = null, id = null) => {
    const data = {
        type,
        key,
        value,
        timestamp: Date.now()
    };
    
    // যদি ID দেওয়া থাকে (যেমন input_draft এর জন্য), তবে সেটি ব্যবহার করবে
    if (id) {
        data.id = id;
        await db.activities.put(data); // আগের ডাটা আপডেট করবে
    } else {
        await db.activities.add(data); // নতুন ডাটা যোগ করবে
    }

    if (navigator.onLine) {
        syncDataWithServer();
    }
};

// ২. সার্ভারের সাথে সিঙ্ক করা
export const syncDataWithServer = async () => {
    if (!navigator.onLine) return;

    const drafts = await db.activities.toArray();
    if (!drafts || drafts.length === 0) return;

    try {
        const response = await fetch('/api/sync-offline-data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            },
            body: JSON.stringify({ activities: drafts }) // key নাম 'activities' রাখলাম লারাভেল কন্ট্রোলারের সাথে মিল রেখে
        });

        if (response.ok) {
            await db.activities.clear();
            console.log('✅ Offline data synced successfully!');
        }
    } catch (e) {
        console.warn('❌ Sync failed, will retry later.');
    }
};

// ৩. উইন্ডো ইভেন্ট লিসেনার
window.addEventListener('online', syncDataWithServer);