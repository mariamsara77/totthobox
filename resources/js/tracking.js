class VisitorTracker {
    constructor() {
        if (window.visitorTrackerInstance) return window.visitorTrackerInstance;
        this.visitorId = this.getVisitorId();
        this.sessionId = this.getSessionId();
        window.visitorTrackerInstance = this;
        this.init();
    }

    init() {
        // পেজ লোড হওয়ার ১ সেকেন্ড পর ট্র্যাক করা যাতে ব্রাউজার স্ট্যাবল হয়
        setTimeout(() => {
            this.trackInitialPageView();
            this.setupEventListeners();
        }, 1000);
    }

    getVisitorId() {
        let id = localStorage.getItem('visitor_id') || 'vis_' + crypto.randomUUID().substring(0, 8);
        localStorage.setItem('visitor_id', id);
        return id;
    }

    getSessionId() {
        let id = sessionStorage.getItem('session_id') || crypto.randomUUID();
        sessionStorage.setItem('session_id', id);
        return id;
    }

  trackEvent(category, action, payload = {}) {
        // এখন আর CSRF টোকেন খোঁজার বা পাঠানোর দরকার নেই
        const data = {
            category: category,
            action: action,
            js_visitor_id: this.visitorId,
            session_id: this.sessionId,
            payload: {
                ...payload,
                ram: navigator.deviceMemory || null,
                cpu_cores: navigator.hardwareConcurrency || null,
                screen_res: `${window.screen.width}x${window.screen.height}`,
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || null
            }
        };

        // ক্লিন ইউআরএল (যেহেতু CSRF বাদ দেওয়া হয়েছে)
      const url = '/api/tracking/event';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        }).catch(() => {});
    }

    trackInitialPageView() {
        if (!sessionStorage.getItem('hw_tracked')) {
            this.trackEvent('system', 'hardware_info');
            sessionStorage.setItem('hw_tracked', 'true');
        }
    }

    setupEventListeners() {
        if (this.listenersSet) return;
        document.addEventListener('click', (e) => {
            const target = e.target.closest('[data-track]');
            if (target) {
                this.trackEvent('interaction', 'click', { label: target.dataset.track });
            }
        }, { passive: true });
        this.listenersSet = true;
    }
}
new VisitorTracker();