export function initGoogleTranslate() {
    if (!document.getElementById('google-translate-script')) {
        const script = document.createElement('script');
        script.id = 'google-translate-script';
        script.type = 'text/javascript';
        script.src = "//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit";
        script.onerror = () => console.error('Google Translate script failed to load.');
        document.body.appendChild(script);
    }

    window.googleTranslateElementInit = () => {
        new window.google.translate.TranslateElement({
            pageLanguage: 'bn',
            includedLanguages: 'bn,en,hi,ar,fr,es',
            autoDisplay: false
        }, 'google_translate_element');
    };
}

window.changeAppLanguage = function(val) {
    if (val === 'bn') {
        // সব কুকি ক্লিয়ার করে রিফ্রেশ
        document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=" + window.location.hostname;
        window.location.reload();
        return;
    }

    const triggerChange = () => {
        const select = document.querySelector('.goog-te-combo');
        if (select) {
            select.value = val;
            select.dispatchEvent(new Event('change'));
        } else {
            setTimeout(triggerChange, 300); // এলিমেন্ট না পাওয়া পর্যন্ত ট্রাই করবে
        }
    };

    triggerChange();
}