@props(['packages', 'selected' => null])
{{--
    Enquiry popover. Open it from any element inside an Alpine scope with `$dispatch('open-enquiry')`
    (or `$dispatch('open-enquiry', packageId)` to pre-select a package). Escape / backdrop closes it;
    focus is trapped inside and returned to the trigger afterwards.
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
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-6" role="dialog" aria-modal="true" aria-labelledby="enquiry-modal-title">
        <div class="absolute inset-0 bg-ink/60" @click="hide()" x-show="open" x-transition.opacity.duration.250ms></div>
        <div class="modal-pop relative flex max-h-[94vh] w-full max-w-[720px] flex-col overflow-hidden rounded-t-[10px] border border-line bg-white shadow-[0_40px_80px_-40px_rgba(27,27,47,0.6)] sm:rounded-[8px]"
             @keydown.tab="const f = [...$el.querySelectorAll('a[href],button:not([disabled]),input:not([type=hidden]),select,textarea,[tabindex]:not([tabindex=\'-1\'])')].filter(e => e.offsetParent); if (! f.length) return; const first = f[0], last = f[f.length - 1]; if ($event.shiftKey && document.activeElement === first) { $event.preventDefault(); last.focus(); } else if (! $event.shiftKey && document.activeElement === last) { $event.preventDefault(); first.focus(); }">
            <div class="flex items-start justify-between gap-4 border-b border-line px-6 py-5 sm:px-8">
                <div class="flex flex-col gap-1">
                    <p class="eyebrow">Enquire now</p>
                    <h2 id="enquiry-modal-title" class="display text-2xl leading-tight sm:text-[28px]">Tell us about your press release.</h2>
                </div>
                <button type="button" @click="hide()" aria-label="Close" class="flex size-11 shrink-0 items-center justify-center rounded-[6px] border border-line bg-white text-ink transition hover:border-ink"><x-icon name="x" :size="18" /></button>
            </div>
            <div class="relative overflow-y-auto px-6 py-6 sm:px-8">
                <x-enquiry-form :packages="$packages" :selected="$selected" prefix="menq" />
            </div>
        </div>
    </div>
</div>
