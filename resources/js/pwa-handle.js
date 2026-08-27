const PWA = {
    deferredPrompt: null,
    dismissKey: 'pwa_dismiss_until',
    installedKey: 'pwa_installed',

    init() {
        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
            localStorage.setItem(this.installedKey, 'true');
            return;
        }

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            this.maybeShow();
        });

        window.addEventListener('appinstalled', () => {
            localStorage.setItem(this.installedKey, 'true');
            this.hide();
            this.deferredPrompt = null;
        });

        this.isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;

        document.addEventListener('click', (e) => {
            if (e.target.closest('#pwa-install-btn')) this.install();
            if (e.target.closest('#pwa-close-btn')) this.dismiss();
        });

        // Soft engagement triggers
        setTimeout(() => this.maybeShow(), 4500);

        let scrolled = false;
        window.addEventListener('scroll', () => {
            if (!scrolled && window.scrollY > 200) {
                scrolled = true;
                this.maybeShow();
            }
        }, { passive: true });
    },

    maybeShow() {
        if (this.isDismissed() || localStorage.getItem(this.installedKey)) return;

        if (this.isIOS) {
            this.showIOS();
            return;
        }

        if (this.deferredPrompt) {
            this.showAndroid();
        }
    },

    showAndroid() {
        const bar = document.getElementById('pwa-bar');
        if (!bar || !bar.classList.contains('hidden')) return;

        bar.classList.remove('hidden');
        // Force reflow
        bar.offsetHeight;
        bar.classList.remove('translate-y-full');
    },

    showIOS() {
        const bar = document.getElementById('pwa-bar-ios');
        if (!bar || !bar.classList.contains('hidden')) return;

        bar.classList.remove('hidden');
        requestAnimationFrame(() => {
            bar.classList.remove('opacity-0', 'translate-y-8', 'pointer-events-none');
            bar.classList.add('opacity-100', 'translate-y-0', 'pointer-events-auto');
        });
    },

    async install() {
        if (!this.deferredPrompt) return;

        this.deferredPrompt.prompt();
        const { outcome } = await this.deferredPrompt.userChoice;

        if (outcome === 'accepted') {
            this.hide();
        }
        this.deferredPrompt = null;
    },

    hide() {
        // Android
        const android = document.getElementById('pwa-bar');
        if (android && !android.classList.contains('hidden')) {
            android.classList.add('translate-y-full');
            setTimeout(() => android.classList.add('hidden'), 300);
        }

        // iOS
        const ios = document.getElementById('pwa-bar-ios');
        if (ios && !ios.classList.contains('hidden')) {
            ios.classList.remove('opacity-100', 'translate-y-0', 'pointer-events-auto');
            ios.classList.add('opacity-0', 'translate-y-8', 'pointer-events-none');
            setTimeout(() => ios.classList.add('hidden'), 300);
        }
    },

    dismiss() {
        this.hide();
        // 7 days
        localStorage.setItem(this.dismissKey, Date.now() + 7 * 24 * 60 * 60 * 1000);
    },

    isDismissed() {
        const until = localStorage.getItem(this.dismissKey);
        return until && Date.now() < parseInt(until);
    }
};

document.addEventListener('DOMContentLoaded', () => PWA.init());