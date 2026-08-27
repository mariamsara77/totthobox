/**
 * resources/js/push-notifications.js
 *
 * Standard Vite module (imported from resources/js/app.js).
 * Reads the VAPID public key from a <meta> tag rendered by Blade
 * (see 02-edit-existing-files/app-layout-head.blade.php), since this
 * file itself is plain JS and can't call Laravel's config() helper.
 */

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

function getVapidPublicKey() {
    return document.querySelector('meta[name="vapid-public-key"]')?.content ?? null;
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? null;
}

const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
const isPWA = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;

async function subscribeUser() {
    try {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            console.warn('Push messaging not supported on this browser.');
            return;
        }

        if (isIOS && !isPWA) {
            console.log('iOS requires Add to Home Screen before push works.');
            return;
        }

        if (Notification.permission === 'denied') return;

        const permission = await Notification.requestPermission();
        if (permission !== 'granted') return;

        const registration = await navigator.serviceWorker.ready;
        let subscription = await registration.pushManager.getSubscription();

        if (!subscription) {
            const vapidPublicKey = getVapidPublicKey();
            if (!vapidPublicKey) {
                console.error('VAPID public key meta tag is missing.');
                return;
            }
            subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
            });
        }

        const csrfToken = getCsrfToken();
        if (!csrfToken) return;

        const subJson = subscription.toJSON();

        const response = await fetch('/push-subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                endpoint: subscription.endpoint,
                keys: subJson.keys,
                device_label: navigator.userAgent.slice(0, 255),
            }),
        });

        if (response.ok) {
            console.log('✅ Push subscription synced!');
            document.getElementById('enable-notifications-btn')?.style.setProperty('display', 'none');
        } else {
            console.error('❌ Failed to sync subscription:', await response.text());
        }
    } catch (error) {
        console.error('Push subscription error:', error);
    }
}

export function initPushNotifications() {
    if (isIOS) {
        if (isPWA && Notification.permission === 'default') {
            const btn = document.getElementById('enable-notifications-btn');
            if (btn) {
                btn.style.display = 'inline-block';
                btn.addEventListener('click', subscribeUser);
            }
        }
        return;
    }

    if (Notification.permission === 'default') {
        const lastAsked = localStorage.getItem('push_last_asked');
        const oneDay = 24 * 60 * 60 * 1000;
        if (lastAsked && (Date.now() - lastAsked < oneDay)) return;
        localStorage.setItem('push_last_asked', Date.now());
    }

    setTimeout(subscribeUser, 2000);
}