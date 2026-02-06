const PWA_CORE = {
    deferredPrompt: null,
    dismissKey: 'pwa_interaction_status',

    init() {
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
        if (isStandalone) {
            localStorage.removeItem(this.dismissKey);
            return;
        }

        // ইভেন্ট লিসেনারটি একদম শুরুতে রাখুন
        window.addEventListener('beforeinstallprompt', (e) => {
            console.log('PWA: beforeinstallprompt event fired');
            e.preventDefault();
            this.deferredPrompt = e;

            if (!this.isDismissed()) {
                this.triggerUI();
            }
        });

        window.addEventListener('appinstalled', () => {
            this.hideUI();
            localStorage.setItem('pwa_is_installed', 'true');
            this.deferredPrompt = null;
        });

        // Event Delegation ব্যবহার করা ভালো যাতে এলিমেন্ট পরে লোড হলেও কাজ করে
        document.addEventListener('click', (e) => {
            if (e.target.id === 'btn-pwa-install' || e.target.closest('#btn-pwa-install')) {
                this.handleInstall();
            }
            if (e.target.id === 'btn-pwa-close' || e.target.closest('#btn-pwa-close')) {
                this.dismiss();
            }
        });
    },

    triggerUI() {
        const bar = document.getElementById('pwa-smart-bar');
        if (!bar) return;

        setTimeout(() => {
            bar.classList.remove('hidden');
            setTimeout(() => {
                bar.classList.replace('opacity-0', 'opacity-100');
                bar.classList.replace('translate-y-10', 'translate-y-0');
                bar.classList.replace('pointer-events-none', 'pointer-events-auto');
            }, 100);
        }, 2000);
    },

    async handleInstall() {
        if (!this.deferredPrompt) {
            // যদি প্রম্পট না থাকে তবে ইউজারকে একটি মেসেজ দিন বা কনসোলে চেক করুন
            console.log('PWA: Prompt not ready. Try interacting with the page first.');
            return;
        }

        const promptEvent = this.deferredPrompt;
        promptEvent.prompt(); // প্রম্পট দেখানো

        const { outcome } = await promptEvent.userChoice;
        console.log(`PWA: User response to the install prompt: ${outcome}`);

        if (outcome === 'accepted') {
            this.hideUI();
        }

        // প্রম্পট একবার ব্যবহারের পর নাল করে দিতে হয়
        this.deferredPrompt = null;
    },

    hideUI() {
        const bar = document.getElementById('pwa-smart-bar');
        if (bar) {
            bar.classList.replace('opacity-100', 'opacity-0');
            bar.classList.replace('translate-y-0', 'translate-y-10');
            bar.classList.replace('pointer-events-auto', 'pointer-events-none');
            setTimeout(() => bar.classList.add('hidden'), 500);
        }
    },

    dismiss() {
        this.hideUI();
        const expiry = new Date().getTime() + (24 * 60 * 60 * 1000);
        localStorage.setItem(this.dismissKey, expiry.toString());
    },

    isDismissed() {
        const status = localStorage.getItem(this.dismissKey);
        if (!status) return false;
        return new Date().getTime() < parseInt(status);
    }
};

PWA_CORE.init();
