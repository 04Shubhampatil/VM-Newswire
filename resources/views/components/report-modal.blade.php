{{--
    Live sample-report preview. Open from any Alpine scope with
    `$dispatch('open-report', { name, view, download })` — `view` is the inline PDF URL, `download` the attachment URL.
--}}
<div x-data="{
        open: false, loading: false, name: '', view: '', download: '', returnFocus: null,
        show(d) {
            this.returnFocus = document.activeElement;
            this.name = d.name; this.download = d.download; this.view = d.view;
            this.loading = true; this.open = true;
            document.body.style.overflow = 'hidden';
            this.$nextTick(() => this.$refs.close.focus());
        },
        hide() {
            this.open = false; this.view = '';
            document.body.style.overflow = '';
            this.returnFocus?.focus?.();
        },
    }"
    @open-report.window="show($event.detail)"
    @keydown.escape.window="open && hide()">
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-6" role="dialog" aria-modal="true" aria-labelledby="report-modal-title">
        <div class="absolute inset-0 bg-navy-900/60" @click="hide()" x-show="open" x-transition.opacity.duration.250ms></div>
        <div class="modal-pop relative flex w-full flex-col overflow-hidden rounded-t-[10px] border border-line bg-white shadow-[0_40px_80px_-40px_rgba(27,27,47,0.6)] sm:rounded-[8px]" style="height: 92vh; max-width: 1100px">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-4 sm:px-6">
                <div class="flex min-w-0 flex-col">
                    <span class="label-caps">Sample distribution report</span>
                    <h2 id="report-modal-title" class="truncate text-lg font-semibold" x-text="name"></h2>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="download" class="btn btn-secondary btn-sm"><x-icon name="download" :size="16" />Download PDF</a>
                    <a :href="view" target="_blank" rel="noopener" class="btn btn-secondary btn-sm max-sm:hidden"><x-icon name="external" :size="16" />New tab</a>
                    <button type="button" x-ref="close" @click="hide()" aria-label="Close" class="flex size-11 items-center justify-center rounded-[6px] border border-line bg-white text-ink transition hover:border-navy-900"><x-icon name="x" :size="18" /></button>
                </div>
            </div>
            <div class="relative grow bg-canvas-deep">
                {{-- skeleton while the PDF viewer loads --}}
                <div x-show="loading" class="absolute inset-0 flex flex-col items-center gap-3 p-8" aria-hidden="true">
                    <div class="skeleton h-full w-full max-w-[720px] rounded-[6px]"></div>
                </div>
                <template x-if="open">
                    <iframe :src="view" @load="loading = false" title="Sample report preview" class="relative size-full border-0" :class="loading && 'opacity-0'"></iframe>
                </template>
            </div>
        </div>
    </div>
</div>
