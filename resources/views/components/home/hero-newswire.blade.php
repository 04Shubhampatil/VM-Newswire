{{--
    Hero (reference composition). Left: teal-ruled eyebrow, bold headline, copy, teal "Send a Press Release" pill
    (opens the enquiry modal) and teal-outlined "Learn More" pill. Right: the shared hero image block
    (x-hero-visual) over a soft mint glow. Copy and labels come from Website Content.
--}}
@php
    $highlight = (string) $site->get('hero_headline_highlight');
    $primaryLabel = (string) $site->get('hero_primary_label', 'Send a Press Release');
    $secondaryLabel = (string) $site->get('hero_secondary_label', 'Learn More');
@endphp
<section x-data class="relative overflow-hidden bg-white pt-10 pb-14 lg:pt-14 lg:pb-16" aria-labelledby="hero-heading">
    <span class="hero-glow" aria-hidden="true"></span>
    <div class="container-site relative">
        <div class="grid grid-cols-1 items-center gap-14 lg:grid-cols-12 lg:gap-8">
            {{-- Copy --}}
            <div class="lg:col-span-7">
                <p class="hero-in flex items-center gap-3 text-[12px] font-semibold tracking-[0.14em] text-heading uppercase" style="--i: 0">
                    <span class="accent-bar w-8"></span>{{ $site->get('hero_eyebrow') }}
                </p>
                <h1 id="hero-heading" class="hero-in mt-4 text-[40px] leading-[1.08] font-bold tracking-[-0.025em] text-heading sm:text-[52px] lg:text-[58px]" style="--i: 1">
                    {{ $site->get('hero_headline') }}@if (filled($highlight)) {{ $highlight }}@endif
                </h1>
                <p class="hero-in mt-5 max-w-[600px] text-[16px] leading-[1.7] text-muted sm:text-[17px]" style="--i: 2">
                    {{ $site->get('hero_text') }}
                </p>

                <div class="hero-in mt-7 flex flex-col gap-3 sm:flex-row sm:items-center" style="--i: 3">
                    <a href="{{ route('contact') }}" @click.prevent="$dispatch('open-enquiry')" aria-haspopup="dialog"
                       class="btn-pill btn-teal max-sm:justify-between" data-track="cta_click" data-track-label="Hero: {{ $primaryLabel }}">
                        {{ $primaryLabel }}
                        <span class="btn-pill-icon bg-white text-[#0e8c7e]"><x-icon name="arrow-right" :size="15" :stroke="2.4" /></span>
                    </a>
                    <a href="{{ route('media-network') }}" class="btn-pill border-[1.5px] border-teal bg-white text-heading hover:bg-teal-soft max-sm:justify-between"
                       data-track="cta_click" data-track-label="Hero: {{ $secondaryLabel }}">
                        {{ $secondaryLabel }}
                        <span class="btn-pill-icon bg-[#14a893] text-white"><svg width="11" height="12" viewBox="0 0 11 12" fill="currentColor" aria-hidden="true"><path d="M1 1.2v9.6a.8.8 0 0 0 1.2.7l8-4.8a.8.8 0 0 0 0-1.4l-8-4.8A.8.8 0 0 0 1 1.2z"/></svg></span>
                    </a>
                </div>
            </div>

            {{-- Image with floating stat cards --}}
            <div class="lg:col-span-5">
                <x-hero-visual />
            </div>
        </div>
    </div>
</section>
