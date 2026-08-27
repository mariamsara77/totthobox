/**
 * Ultra-Optimized Visitor Tracker for Totthobox
 */
(() => {
    if (window.visitorTrackerInstance) return;

    // ১. গ্লোবাল মেমোরি এক্সেস অপটিমাইজেশন (একবার রিড করা হবে)
    const storage = (key, val, session = false) => {
        const store = session ? sessionStorage : localStorage;
        if (val !== undefined) return store.setItem(key, val);
        return store.getItem(key);
    };

    const makeId = (prefix) => prefix + (Math.random().toString(36).substring(2, 10) + Date.now().toString(36));

    const getId = (key, prefix, session) => {
        let id = storage(key, undefined, session);
        if (!id) {
            id = makeId(prefix);
            storage(key, id, session);
        }
        return id;
    };

    const visitorId = getId('visitor_id', 'vis_', false);
    const sessionId = getId('session_id', 'ses_', true);

    // ২. হার্ডওয়্যার ও সিস্টেম ইনফো একবারে ক্যাশ করে রাখা (বারবার রান হবে না)
    const systemPayload = {
        ram: navigator.deviceMemory || null,
        cpu_cores: navigator.hardwareConcurrency || null,
        screen_res: `${screen.width}x${screen.height}`,
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
    };

    // ৩. CSRF টোকেন হেলপার
    const getCsrfToken = () => {
        const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
        return match ? decodeURIComponent(match[1]) : '';
    };

    // ৪. অফলাইন কিউ ম্যানেজমেন্ট
    const queueOffline = (url, data) => {
        try {
            const existing = JSON.parse(storage('tracking_queue') || '[]');
            existing.push({ url, data, ts: Date.now() });
            storage('tracking_queue', JSON.stringify(existing.slice(-30))); // Max 30 items
        } catch (_) { }
    };

    // ৫. ডাটা সেন্ড ফাংশন (Beacon + Fetch fallback)
    const send = (url, data) => {
        if (!navigator.onLine) return queueOffline(url, data);

        const json = JSON.stringify(data);

        if (navigator.sendBeacon) {
            const blob = new Blob([json], { type: 'application/json' });
            if (navigator.sendBeacon(url, blob)) return;
        }

        fetch(url, {
            method: 'POST',
            keepalive: true,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': getCsrfToken(),
            },
            body: json,
        }).catch(() => queueOffline(url, data));
    };

    // ৬. পাবলিক ট্র্যাকার অবজেক্ট
    const Tracker = {
        trackEvent(category, action, payload = {}) {
            send('/api/tracking/event', {
                category,
                action,
                js_visitor_id: visitorId,
                session_id: sessionId,
                payload: { ...payload, ...systemPayload },
            });
        },

        flushOfflineQueue() {
            try {
                const queue = JSON.parse(storage('tracking_queue') || '[]');
                if (!queue.length) return;

                const activities = queue.map((item) => ({
                    type: item.data.category || 'interaction',
                    key: item.data.action || 'unknown',
                    value: item.data.payload || null,
                    timestamp: item.ts,
                    id: item.data.js_visitor_id || null,
                }));

                send('/api/tracking/sync', { activities });
                localStorage.removeItem('tracking_queue');
            } catch (_) { }
        },
    };

    // ৭. ইভেন্ট লিসেনার অপটিমাইজেশন
    // হার্ডওয়্যার ট্র্যাকিং (সেশন প্রতি ১ বার)
    if (!storage('hw_tracked', undefined, true)) {
        if (document.readyState === 'complete') {
            Tracker.trackEvent('system', 'hardware_info');
            storage('hw_tracked', '1', true);
        } else {
            window.addEventListener('load', () => {
                Tracker.trackEvent('system', 'hardware_info');
                storage('hw_tracked', '1', true);
            }, { once: true });
        }
    }

    // [data-track] ক্লিক ট্র্যাকিং (Event Delegation)
    document.addEventListener('click', (e) => {
        const el = e.target.closest('[data-track]');
        if (el) {
            Tracker.trackEvent('interaction', 'click', {
                label: el.dataset.track,
                element: el.tagName.toLowerCase(),
                href: el.href || null,
            });
        }
    }, { passive: true });

    // অনলাইন এলে অফলাইন ডাটা ফ্লাশ
    window.addEventListener('online', () => Tracker.flushOfflineQueue());

    // এক্সপোজ টু উইন্ডো
    window.visitorTrackerInstance = Tracker;
    window.tracker = Tracker;
})();