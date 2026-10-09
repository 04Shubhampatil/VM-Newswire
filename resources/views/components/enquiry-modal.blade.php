@props(['packages', 'selected' => null])
{{--
    Enquiry popover. Open it from any element inside an Alpine scope with `$dispatch('open-enquiry')`
    (or `$dispatch('open-enquiry', packageId)` to pre-select a package). Escape / backdrop closes it;
    focus is trapped inside and returned to the trigger afterwards. Styling lives in the `.enq-*` rules.
--}}
<div x-data="{
        open: false,
        returnFocus: null,
        show(packageId) {
            this.returnFocus = document.activeElement;
            this.open = true;
            document.body.style.overflow = 'hidden';
            this.$nextTick(() => {
                if (packageId) { const s = this.$el.querySelector('select[name=package_id]'); if (s) s.value = String(packageId); }
                this.$el.querySelector('input[name=name]')?.focus();
            });
        },
        hide() {
            this.open = false;
            document.body.style.overflow = '';
            this.returnFocus?.focus?.();
        },
    }"
    @open-enquiry.window="show($event.detail)"
    @keydown.escape.window="open && hide()">
    <div x-show="open" x-cloak class="enq-overlay" role="dialog" aria-modal="true" aria-labelledby="enquiry-modal-title">
        <div class="enq-backdrop" @click="hide()" x-show="open" x-transition.opacity.duration.250ms></div>
        <div class="modal-pop enq-modal enq-form"
             @keydown.tab="const f = [...$el.querySelectorAll('a[href],button:not([disabled]),input:not([type=hidden]),select,textarea,[tabindex]:not([tabindex=\'-1\'])')].filter(e => e.offsetParent); if (! f.length) return; const first = f[0], last = f[f.length - 1]; if ($event.shiftKey && document.activeElement === first) { $event.preventDefault(); last.focus(); } else if (! $event.shiftKey && document.activeElement === last) { $event.preventDefault(); first.focus(); }">
            <button type="button" @click="hide()" aria-label="Close" class="enq-close"><x-icon name="x" :size="18" :stroke="2" /></button>
            <div class="enq-body">
                <header class="enq-head">
                    <p class="enq-eyebrow">Enquire now</p>
                    <h2 id="enquiry-modal-title" class="enq-title">Tell us about your press release.</h2>
                    <p class="enq-text">Our team will review your announcement and get back to you with the best distribution options for your needs.</p>
                </header>
                <x-enquiry-form :packages="$packages" :selected="$selected" prefix="menq" name-label="Full name" />
            </div>
        </div>
    </div>
</div>
