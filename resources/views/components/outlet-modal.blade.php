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
        <div class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-6" role="dialog" aria-modal="true" :aria-labelledby="'outlet-title-' + current.id">
            <div class="absolute inset-0 bg-ink/60" @click="close()" x-transition.opacity.duration.250ms></div>
            <div class="relative flex max-h-[92vh] w-full max-w-[560px] flex-col overflow-hidden rounded-t-[10px] border border-line bg-canvas shadow-[0_40px_80px_-40px_rgba(27,27,47,0.6)] sm:rounded-[8px]"
                 x-transition:enter="transition duration-350 ease-[cubic-bezier(0.22,1,0.36,1)]" x-transition:enter-start="translate-y-4 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
                 @keydown.tab="/* keep focus inside */ const f = $el.querySelectorAll('a[href],button,[tabindex]:not([tabindex=\'-1\'])'); if (! f.length) return; const first = f[0], last = f[f.length - 1]; if ($event.shiftKey && document.activeElement === first) { $event.preventDefault(); last.focus(); } else if (! $event.shiftKey && document.activeElement === last) { $event.preventDefault(); first.focus(); }">
                <button type="button" x-ref="close" @click="close()" aria-label="Close" class="absolute top-3 right-3 z-10 flex size-11 items-center justify-center rounded-[6px] border border-line bg-white text-ink transition hover:border-ink"><x-icon name="x" :size="18" /></button>

                <div class="relative h-[220px] shrink-0 border-b border-line bg-white sm:h-[260px]">
                    <template x-if="current.poster_src">
                        <img :src="current.poster_src" :alt="current.name + ' poster'" class="size-full object-cover">
                    </template>
                    <template x-if="! current.poster_src">
                        <div class="flex size-full flex-col justify-between p-6"><span class="size-2.5 bg-accent"></span><span class="font-display text-4xl font-semibold" x-text="current.name"></span></div>
                    </template>
                </div>

                <div class="flex flex-col gap-4 overflow-y-auto p-6 sm:p-8">
                    <p class="eyebrow" x-text="current.category"></p>
                    <h2 :id="'outlet-title-' + current.id" class="display text-3xl leading-tight sm:text-4xl" x-text="current.name"></h2>
                    <p class="text-base leading-relaxed text-muted" x-text="current.description || current.short_description || 'Part of the VM Newswire distribution network.'"></p>
                    <dl class="grid grid-cols-2 gap-4 border-t border-line pt-4 text-sm">
                        <div><dt class="label-caps">Distribution</dt><dd class="mt-1 font-semibold">Included in selected packages</dd></div>
                        <div><dt class="label-caps">Status</dt><dd class="mt-1 flex items-center gap-2 font-semibold"><span class="size-1.5 rounded-full bg-success"></span>Active outlet</dd></div>
                    </dl>
                    <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                        <x-button :href="route('packages.index')" size="sm" icon="arrow-right" class="h-12!">View Packages</x-button>
                        <x-button :href="route('media-network')" variant="secondary" size="sm" class="h-12!">Full media network</x-button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
