@props([
    'heading' => null,
    'text' => "Turn your press release into tomorrow's headlines. Tell us about your announcement and our team will recommend the right package.",
    'label' => 'Get Started',
    'href' => null,
    'secondaryLabel' => null,
    'secondaryHref' => null,
])
{{--
    Closing banner (reference: "Ready to Share Your News?"): a rounded deep navy → dark teal panel with the
    VM Newswire mark in a large circle on the left and the heading, copy and teal "Get Started" pill on the
    right. "Get Started" opens the enquiry modal (falls back to the contact page without JS).
--}}
<section class="bg-white pb-16 lg:pb-24">
    <div class="container-site">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#0b1628] via-[#0f2f45] to-[#0d4f55] px-6 py-12 sm:px-12 lg:px-16 lg:py-16">
            <svg class="pointer-events-none absolute right-0 bottom-0 left-0 h-28 w-full text-teal/15" viewBox="0 0 1200 120" preserveAspectRatio="none" fill="none" aria-hidden="true">
                <path d="M0 80C220 20 420 120 640 70S1020 10 1200 60V120H0Z" fill="currentColor" />
            </svg>
            <svg class="pointer-events-none absolute right-0 bottom-0 left-0 h-20 w-full text-teal/10" viewBox="0 0 1200 120" preserveAspectRatio="none" fill="none" aria-hidden="true">
                <path d="M0 100C260 60 480 120 760 90S1080 50 1200 80V120H0Z" fill="currentColor" />
            </svg>

            <div class="relative grid items-center gap-10 md:grid-cols-12" data-reveal-group>
                <div class="flex justify-center md:col-span-5" data-reveal>
                    <span class="flex size-44 items-center justify-center rounded-full bg-teal/25 ring-[14px] ring-teal/10 sm:size-56 lg:size-64">
                        <img src="{{ asset('images/logo-mark-white.webp') }}" alt="" width="947" height="429" loading="lazy" decoding="async" class="h-16 w-auto sm:h-20 lg:h-24">
                    </span>
                </div>
                <div class="md:col-span-7" data-reveal>
                    <span class="accent-bar"></span>
                    <h2 class="mt-5 max-w-lg text-[32px] leading-[1.12] font-bold tracking-[-0.02em] text-white sm:text-[40px]">@if ($heading){{ $heading }}@else Ready to Share <br class="hidden sm:block">Your News?@endif</h2>
                    <p class="mt-4 max-w-md text-[16px] leading-[1.7] text-[#b8c7d6]">
                        {{ $text }}
                    </p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                        @if ($href)
                            <a href="{{ $href }}" class="btn-pill btn-teal max-sm:justify-between" data-track="cta_click" data-track-label="Final CTA: {{ $label }}">
                                {{ $label }}
                                <span class="btn-pill-icon bg-white text-heading"><x-icon name="arrow-right" :size="15" :stroke="2.4" /></span>
                            </a>
                        @else
                            <a href="{{ route('contact') }}" x-data @click.prevent="$dispatch('open-enquiry')" aria-haspopup="dialog"
                               class="btn-pill btn-teal max-sm:justify-between" data-track="cta_click" data-track-label="Final CTA: {{ $label }}">
                                {{ $label }}
                                <span class="btn-pill-icon bg-white text-heading"><x-icon name="arrow-right" :size="15" :stroke="2.4" /></span>
                            </a>
                        @endif
                        @if ($secondaryLabel)
                            <a href="{{ $secondaryHref ?? route('contact') }}" class="btn-pill border-[1.5px] border-white/40 text-white hover:bg-white/10 max-sm:justify-between" data-track="cta_click" data-track-label="Final CTA: {{ $secondaryLabel }}">
                                {{ $secondaryLabel }}
                                <span class="btn-pill-icon bg-white/15 text-white"><x-icon name="arrow-right" :size="15" :stroke="2.4" /></span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
