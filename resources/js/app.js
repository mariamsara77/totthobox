import './tracking' ;
import './echo'; 
import './share';
import './pwa-handle';
import './pwa-tracking';
import './sw-register';
import './quil-editor';
// অফলাইন হ্যান্ডলার থেকে ইমপোর্ট
import { trackActivity, syncDataWithServer } from './offline-handler';

// --- ১. ক্লিক ট্র্যাকার ---
document.addEventListener('click', async (event) => {
    const el = event.target.closest('[data-track]'); 
    if (!el) return;

    trackActivity(
        'interaction', 
        el.getAttribute('data-track') || el.id || 'unnamed_btn', 
        el.innerText.trim() || el.value
    );
});

// --- ২. ইনপুট ট্র্যাকার (Draft Save) ---
document.addEventListener('input', async (event) => {
    const { name, id, value } = event.target;
    if (!name && !id) return;

    trackActivity(
        'input_draft', 
        name || id, 
        value, 
        'live_draft' // একই ID ব্যবহার করলে ডাটাবেজ বড় হবে না
    );
});

// --- ৩. অটো সিঙ্ক ইভেন্টস (Livewire Navigation) ---
document.addEventListener('livewire:navigated', () => {
    syncDataWithServer();
});

// প্রাথমিক লোডে একবার সিঙ্ক করার চেষ্টা
syncDataWithServer();







const observer = new MutationObserver(() => {
        const isDark = document.documentElement.classList.contains('dark');
        const color = isDark ? '#262626' : '#ffffff';
        
        // এটি সরাসরি মেটা ট্যাগ আপডেট করবে কোনো ফ্লিকার ছাড়া
        document.querySelector('meta[name="theme-color"]').setAttribute('content', color);
    });

    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
