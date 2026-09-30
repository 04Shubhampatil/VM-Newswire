@props(['outlets' => []])
<section class="section-y border-b border-brand-line bg-brand text-canvas">
    <div class="container-site flex flex-col gap-12 lg:grid lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center lg:gap-20">
        <div class="flex max-w-[620px] flex-col gap-5" data-reveal-group>
            <p data-reveal class="eyebrow eyebrow-dark">Sample reports</p>
            <h2 data-reveal class="display text-[32px] leading-[1.12] text-canvas md:text-[38px] lg:text-[44px]">See exactly what your distribution looks like.</h2>
            <p data-reveal class="text-[17px] leading-relaxed text-muted-on-brand">Download a sample distribution report before choosing your package.</p>
            <div data-reveal class="pt-3">
                <x-button :href="route('sample-reports.index')" icon-left="download" class="max-sm:w-full" data-track="cta_click" data-track-label="View Sample Reports">View Sample Reports</x-button>
            </div>
        </div>
        <div data-reveal="scale"><x-report-document :outlets="$outlets" /></div>
    </div>
</section>
