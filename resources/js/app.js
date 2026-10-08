import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import { track } from './analytics';
import { initMotion } from './motion';

Alpine.plugin(collapse);

/**
 * Full distribution network list on a package page.
 * Loads outlets lazily (paginated JSON) only when the visitor opens the section.
 */
Alpine.data('outletNetwork', (url) => ({
    open: false,
    loading: false,
    loaded: false,
    search: '',
    outlets: [],
    page: 1,
    lastPage: 1,
    total: 0,
    error: false,

    toggle() {
        this.open = !this.open;
        if (this.open && !this.loaded) {
            this.fetch(1);
        }
    },

    async fetch(page = 1) {
        this.loading = true;
        this.error = false;
        try {
            const params = new URLSearchParams({ page, search: this.search });
            const response = await window.fetch(`${url}?${params}`, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error(response.statusText);
            const json = await response.json();
            this.outlets = page === 1 ? json.data : this.outlets.concat(json.data);
            this.page = json.current_page;
            this.lastPage = json.last_page;
            this.total = json.total;
            this.loaded = true;
        } catch (e) {
            this.error = true;
        } finally {
            this.loading = false;
        }
    },

    more() {
        if (this.page < this.lastPage) this.fetch(this.page + 1);
    },
}));

/**
 * Enquiry form. Submits in the background and shows an inline success state; without JS it
 * falls back to a normal POST + redirect. Server-side validation is the source of truth.
 */
Alpine.data('enquiryForm', (packageName = '', initialErrors = {}) => ({
    started: false,
    submitting: false,
    sent: false,
    errors: initialErrors,
    general: '',
    sentPackage: '',

    start() {
        if (this.started) return;
        this.started = true;
        track('enquiry_form_started', { package_name: packageName || undefined });
    },

    err(field) {
        return this.errors[field]?.[0] ?? '';
    },

    async submit(event) {
        if (!window.fetch || !window.FormData) return; // let the browser submit normally
        event.preventDefault();

        const form = event.target;
        this.submitting = true;
        this.errors = {};
        this.general = '';

        try {
            const response = await window.fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form),
            });

            if (response.status === 422) {
                this.errors = (await response.json()).errors ?? {};
                this.general = 'Please check the highlighted fields and try again.';
                this.$nextTick(() => form.querySelector('[aria-invalid="true"]')?.focus());
            } else if (response.status === 429) {
                this.general = 'We received several enquiries from your connection. Please wait a minute and try again.';
            } else if (response.status === 419) {
                this.general = 'Your session expired. Please refresh the page and try again.';
            } else if (response.ok) {
                const json = await response.json();
                this.sentPackage = json.package ?? '';
                this.sent = true;
                form.reset();
                track('enquiry_submitted', { package_name: this.sentPackage || undefined });
                this.$nextTick(() => this.$refs.success?.focus());
            } else {
                throw new Error(String(response.status));
            }
        } catch (e) {
            this.general = 'Your enquiry could not be sent. Please try again, or email us directly.';
        } finally {
            this.submitting = false;
            window.turnstile?.reset?.();
        }
    },

    again() {
        this.sent = false;
        this.$nextTick(() => this.$el.querySelector('input[name="name"]')?.focus());
    },
}));

/** Admin: instant poster preview via FileReader. Nothing is uploaded until the form is submitted. */
Alpine.data('posterPicker', (current = null) => ({
    current,
    preview: current,
    error: '',
    remove: false,

    pick(event) {
        const file = event.target.files?.[0];
        this.error = '';
        if (!file) return this.cancel();
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            this.error = 'Please choose a JPG, PNG or WebP image.';
            return this.cancel();
        }
        if (file.size > 5 * 1024 * 1024) {
            this.error = 'The image is larger than 5 MB.';
            return this.cancel();
        }
        this.remove = false;
        const reader = new FileReader();
        reader.onload = () => (this.preview = reader.result);
        reader.readAsDataURL(file);
    },

    cancel(removing = false) {
        if (this.$refs.file) this.$refs.file.value = '';
        this.preview = removing ? null : this.current;
    },
}));

/**
 * Home: package pricing slider. The track is a native snap-scrolling flex row; this only drives the prev/next
 * buttons, keeps them in sync with the scroll position, and resets the frame when the brand filter changes.
 */
Alpine.data('packageSlider', () => ({
    brand: 'All',
    canPrev: false,
    canNext: false,

    init() {
        this.$nextTick(() => this.update());
        window.addEventListener('resize', () => this.update(), { passive: true });
    },

    setBrand(value) {
        this.brand = value;
        // x-show toggles display on the next animation frame, so wait two frames for the new layout
        // before resetting the frame (this also beats Chrome's re-snap to the previously snapped slide).
        this.$nextTick(() => requestAnimationFrame(() => requestAnimationFrame(() => {
            this.$refs.track.scrollTo({ left: 0, behavior: 'auto' });
            this.update();
        })));
    },

    /** Width of one visible slide plus the track gap; falls back to the frame width. */
    step() {
        const track = this.$refs.track;
        const slide = Array.from(track.querySelectorAll('[data-slide]')).find((el) => el.offsetParent !== null);
        if (!slide) return track.clientWidth;
        const gap = parseFloat(getComputedStyle(track).columnGap) || 0;
        return slide.getBoundingClientRect().width + gap;
    },

    go(direction) {
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        this.$refs.track.scrollBy({ left: direction * this.step(), behavior: reduce ? 'auto' : 'smooth' });
    },

    update() {
        const track = this.$refs.track;
        if (!track) return;
        this.canPrev = track.scrollLeft > 4;
        this.canNext = track.scrollLeft + track.clientWidth < track.scrollWidth - 4;
    },
}));

window.Alpine = Alpine;
Alpine.start();
initMotion();

// Declarative click tracking: <a data-track="cta_click" data-track-label="View Packages">
document.addEventListener('click', (event) => {
    const el = event.target.closest('[data-track]');
    if (!el) return;
    track(el.dataset.track, { label: el.dataset.trackLabel || undefined, package_name: el.dataset.trackPackage || undefined });
});

// Page-level events rendered by the server: <meta name="vmn-event" content='{"name":"package_view",...}'>
document.querySelectorAll('meta[name="vmn-event"]').forEach((meta) => {
    try {
        const { name, ...params } = JSON.parse(meta.content);
        track(name, params);
    } catch (e) {
        // ignore malformed event
    }
});
