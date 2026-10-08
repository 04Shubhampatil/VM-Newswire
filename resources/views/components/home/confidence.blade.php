{{--
    "Deliver Your News with Confidence" (reference: three testimonial cards). Heading, button label and the cards
    (quote, name, role) are edited under Website Content → Home page; the built-in cards quote VM Newswire's own
    service commitments until the admin replaces them.
--}}
@php
    $heading = (string) $site->get('confidence_heading');
    $button = (string) $site->get('confidence_button_label') ?: 'See Sample Reports';
    $cards = $site->confidenceCards();
@endphp
<section class="bg-white pb-12">
    <div class="container-site">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between" data-reveal-group>
            <div data-reveal>
                <span class="accent-bar"></span>
                <h2 class="home-h2 mt-5 [&_em]:text-accent-ink [&_em]:not-italic">{!! App\Support\Heading::render($heading) !!}</h2>
            </div>
            <a href="{{ route('sample-reports.index') }}" class="btn-pill btn-ghost h-11 self-start text-[14px] sm:self-auto" data-reveal
               data-track="cta_click" data-track-label="Confidence: {{ $button }}">
                {{ $button }}
                <span class="btn-pill-icon size-7"><x-icon name="arrow-right" :size="14" :stroke="2.4" /></span>
            </a>
        </div>

        <div class="-mx-4 mt-10 flex snap-x snap-mandatory gap-5 overflow-x-auto px-4 pb-4 [scrollbar-width:none] sm:mx-0 sm:px-0 lg:grid lg:grid-cols-3 lg:gap-6 lg:overflow-visible [&::-webkit-scrollbar]:hidden" data-reveal-group>
            @foreach ($cards as $card)
                <figure data-reveal class="flex shrink-0 basis-[85%] snap-start flex-col rounded-2xl border border-line bg-white p-7 shadow-[var(--shadow-card)] sm:basis-[calc((100%-1.25rem)/2)] lg:basis-auto">
                    <svg class="h-7 w-8 text-teal" viewBox="0 0 32 28" fill="currentColor" aria-hidden="true"><path d="M0 28V16.8C0 7.4 4.6 1.8 13.2 0l1.6 3.4C10 5 7.6 8.2 7.4 12.6H13V28zm18 0V16.8C18 7.4 22.6 1.8 31.2 0l1.6 3.4C28 5 25.6 8.2 25.4 12.6H31V28z"/></svg>
                    <blockquote class="mt-5 grow text-[15px] leading-[1.7] text-ink">{{ $card['quote'] ?? '' }}</blockquote>
                    <figcaption class="mt-7 flex items-center justify-between gap-4 border-t border-line pt-5">
                        <span>
                            <span class="block text-[14px] font-semibold text-heading">{{ $card['name'] ?? '' }}</span>
                            @if (! empty($card['role']))<span class="block text-[13px] text-muted">{{ $card['role'] }}</span>@endif
                        </span>
                        @if (! empty($card['logo']))
                            <img src="{{ App\Services\SiteSettings::logoUrl($card['logo']) }}" alt="{{ $card['name'] ?? '' }}" class="h-12 w-auto max-w-[160px] shrink-0 object-contain" loading="lazy">
                        @else
                            <img src="{{ asset('images/logo-mark.svg') }}" alt="" class="h-6 w-auto shrink-0" loading="lazy">
                        @endif
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
