/**
 * quill-editor.js
 *
 * Lazy-loads Quill once and caches it on window so multiple editor instances
 * on the same page never double-download the library.
 *
 * Import this in resources/js/app.js:
 *   import './quill-editor';
 */

window.initQuill = (() => {
    /** @type {Promise<typeof import('quill').default> | null} */
    let _pending = null;

    return async function initQuill() {
        // Already resolved — return the cached constructor immediately.
        if (window.Quill) return window.Quill;

        // In-flight — return the same promise so concurrent calls don't race.
        if (_pending) return _pending;

        _pending = (async () => {
            const [{ default: Quill }] = await Promise.all([
                import('quill'),
                import('quill/dist/quill.snow.css'),
            ]);

            window.Quill = Quill;
            return Quill;
        })();

        try {
            return await _pending;
        } catch (err) {
            _pending = null; // allow retry on next call
            console.error('[quill-editor] Failed to load Quill:', err);
            return null;
        }
    };
})();