@props(['outlets'])
{{-- Media outlet detail dialog. Opened by `$dispatch('open-outlet', slug)` from a poster card. --}}
@php
    $data = collect($outlets)->map(fn ($o) => (array) $o)->keyBy('slug')->all();
@endphp
<div x-data="{
        outlets: @js($data),
        current: null,
        returnFocus: null,
        open(slug) {
            if (! this.outlets[slug]) return;
            this.returnFocus = document.activeElement;
            this.current = this.outlets[slug];
            document.body.style.overflow = 'hidden';
            this.$nextTick(() => this.$refs.close.focus());
        },
        close() {
            this.current = null;
            document.body.style.overflow = '';
            this.returnFocus?.focus?.();
        },
    }"
    @open-outlet.window="open($event.detail)"
    @keydown.escape.window="current && close()">
    <template x-if="current">
        <div class="fixed inset-0 z-[60] flex items-end justify-center p-0 sm:items-center sm:p-6" role="dialog" aria-modal="true" :aria-labelledby="'outlet-title-' + current.id">
            <div class="absolute inset-0 bg-navy-900/60 backdrop-blur-[2px]" @click="close()" x-transition.opacity.duration.250ms></div>
            <div class="relative flex max-h-[92vh] w-full max-w-[560px] flex-col overflow-hidden rounded-t-2xl bg-white shadow-[var(--shadow-float)] sm:rounded-2xl"
                 x-transition:enter="transition duration-350 ease-[cubic-bezier(0.22,1,0.36,1)]" x-transition:enter-start="translate-y-4 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
                 @keydown.tab="/* keep focus inside */ const f = $el.querySelectorAll('a[href],button,[tabindex]:not([tabindex=\'-1\'])'); if (! f.length) return; const first = f[0], last = f[f.length - 1]; if ($event.shiftKey && document.activeElement === first) { $event.preventDefault(); last.focus(); } else if (! $event.shiftKey && document.activeElement === last) { $event.preventDefault(); first.focus(); }">
                <button type="button" x-ref="close" @click="close()" aria-label="Close" class="absolute top-4 right-4 z-10 flex size-10 items-center justify-center rounded-full bg-white text-heading shadow-[var(--shadow-card)] transition hover:bg-teal-soft hover:text-accent-ink"><x-icon name="x" :size="18" :stroke="2.2" /></button>

                <div class="relative h-[190px] shrink-0 bg-canvas-deep sm:h-[230px]">
                    <template x-if="current.poster_src">
                        <img :src="current.poster_src" :alt="current.name + ' poster'" class="size-full object-cover">
                    </template>
                    <template x-if="! current.poster_src">
                        <div class="dot-grid flex size-full items-center justify-center"><span class="text-[40px] leading-none font-bold text-heading" x-text="current.name"></span></div>
                    </template>
                    <template x-if="current.logo_src">
                        <img :src="current.logo_src" :alt="current.name" class="absolute bottom-4 left-6 h-12 w-auto max-w-[150px] object-contain drop-shadow-[0_2px_6px_rgba(11,22,40,0.35)]">
                    </template>
                </div>

                <div class="flex flex-col gap-4 overflow-y-auto p-6 [scrollbar-width:none] sm:p-8 [&::-webkit-scrollbar]:hidden">
                    <p class="flex items-center gap-3 text-[12px] font-semibold tracking-[0.14em] text-accent-ink uppercase"><span class="accent-bar w-8"></span><span x-text="current.category"></span></p>
                    <h2 :id="'outlet-title-' + current.id" class="text-[28px] leading-[1.15] font-bold tracking-[-0.02em] text-heading sm:text-[32px]" x-text="current.name"></h2>
                    <p class="text-[15px] leading-[1.7] text-muted" x-text="current.description || current.short_description || 'Part of the VM Newswire distribution network.'"></p>
                    <dl class="grid grid-cols-2 gap-4 border-t border-line pt-4">
                        <div><dt class="label-caps">Distribution</dt><dd class="mt-1 text-[14px] font-semibold text-heading">Included in selected packages</dd></div>
                        <div><dt class="label-caps">Status</dt><dd class="mt-1 flex items-center gap-2 text-[14px] font-semibold text-heading"><span class="size-1.5 rounded-full bg-success"></span>Active outlet</dd></div>
                    </dl>
                    <div class="flex flex-col gap-3 pt-1 sm:flex-row">
                        <a href="{{ route('packages.index') }}" class="btn-pill bg-navy-900 text-white hover:bg-brand max-sm:justify-between">
                            View Packages
                            <span class="btn-pill-icon bg-white text-heading"><x-icon name="arrow-right" :size="15" :stroke="2.4" /></span>
                        </a>
                        <a href="{{ route('media-network') }}" class="btn-pill btn-ghost max-sm:justify-between">
                            Full media network
                            <span class="btn-pill-icon"><x-icon name="arrow-right" :size="15" :stroke="2.4" /></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
