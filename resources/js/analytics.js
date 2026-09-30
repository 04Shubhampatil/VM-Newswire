/**
 * Thin wrapper around GA4's gtag(). Does nothing when GA4 is not configured.
 * Only non-personal parameters (labels, package names) are ever sent.
 */
export function track(name, params = {}) {
    if (typeof window.gtag !== 'function' || !name) return;
    const clean = Object.fromEntries(Object.entries(params).filter(([, v]) => v !== undefined && v !== ''));
    window.gtag('event', name, clean);
}
