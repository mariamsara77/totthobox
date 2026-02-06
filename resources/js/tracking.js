class VisitorTracker {
    constructor() {
        this.visitorId = this.getVisitorId();
        this.sessionId = this.getSessionId();
        this.lastActivity = Date.now();
        this.inactivityTimeout = 30 * 60 * 1000; // 30 minutes
        
        // ডাবল কল রোধ করতে timeout ব্যবহার করা হয়েছে
        setTimeout(() => {
            this.trackInitialPageView();
            this.setupEventListeners();
            this.setupInactivityCheck();
        }, 500);
    }

    getVisitorId() {
        let id = localStorage.getItem('visitor_id');
        if (!id) {
            id = 'vis_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('visitor_id', id);
        }
        return id;
    }

    getSessionId() {
        let id = sessionStorage.getItem('session_id');
        if (!id) {
            id = 'sess_' + Math.random().toString(36).substr(2, 9);
            sessionStorage.setItem('session_id', id);
        }
        return id;
    }

    trackInitialPageView() {
        this.trackEvent('pageview', 'Page View', {
            url: window.location.href,
            referrer: document.referrer,
            screen_resolution: `${window.screen.width}x${window.screen.height}`,
            ram: navigator.deviceMemory || 'unknown',
            cpu_cores: navigator.hardwareConcurrency || 'unknown',
            network: navigator.connection ? navigator.connection.effectiveType : 'unknown',
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            viewport_width: window.innerWidth,
            viewport_height: window.innerHeight,
        });
    }

    trackEvent(eventType, eventName, eventData = {}) {
        // ইভেন্ট ট্র্যাক করার সময় lastActivity আপডেট করা যাবে না (লুপ রোধ করতে)
        if (eventType !== 'session') {
            this.lastActivity = Date.now();
        }

        const payload = {
            event_type: eventType,
            event_name: eventName,
            event_data: eventData,
            visitor_id: this.visitorId,
            session_id: this.sessionId
        };

        fetch('/track-event', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            body: JSON.stringify(payload)
        }).catch(() => { /* সাইলেন্ট এরর যাতে লুপ না হয় */ });
    }

    setupEventListeners() {
        // ক্লিক ট্র্যাকিং (passive লিসেনার ব্যবহার করা হয়েছে)
        document.addEventListener('click', (e) => {
            const target = e.target.closest('[data-track]');
            if (target) {
                this.trackEvent('click', target.dataset.track, {
                    text: target.innerText.trim(),
                    href: target.href || null
                });
            }
        }, { passive: true });

        // ফর্ম ট্র্যাকিং
        document.addEventListener('submit', (e) => {
            const formData = {};
            const formInputs = e.target.querySelectorAll('input:not([type="password"]), select, textarea');
            formInputs.forEach(input => {
                if (input.name) formData[input.name] = input.value;
            });

            this.trackEvent('form', 'Form Submission', {
                form_id: e.target.id,
                form_action: e.target.action
            });
        }, { passive: true });
    }

    setupInactivityCheck() {
        // mousemove বাদ দেওয়া হয়েছে কারণ এটি স্ট্যাক এরর তৈরি করে
        const activityEvents = ['mousedown', 'keydown', 'scroll', 'touchstart'];
        
        const resetTimer = () => {
            this.lastActivity = Date.now();
        };

        activityEvents.forEach(event => {
            window.addEventListener(event, resetTimer, { passive: true });
        });

        // সেশন শেষ করার চেক
        setInterval(() => {
            const now = Date.now();
            if (now - this.lastActivity > this.inactivityTimeout) {
                // সেশন আইডি পরিবর্তন করে নতুন সেশন শুরু
                sessionStorage.removeItem('session_id');
                this.sessionId = this.getSessionId();
                this.lastActivity = Date.now();
                this.trackEvent('session', 'New Session After Timeout');
            }
        }, 30000); // প্রতি ৩০ সেকেন্ডে চেক করবে
    }
}

// Livewire v3 এর জন্য সেফ ইনিশিয়ালাইজেশন
if (!window.trackerLoaded) {
    document.addEventListener('livewire:navigated', () => {
        if (!window.visitorTracker) {
            window.visitorTracker = new VisitorTracker();
        } else {
            window.visitorTracker.trackInitialPageView();
        }
    });
    window.trackerLoaded = true;
}