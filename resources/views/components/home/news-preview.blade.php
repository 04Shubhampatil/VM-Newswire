@props(['outlets'])
{{--
    News card carousel (reference: three release cards with side arrows and a centred "See All News" pill).
    Cards are the highlighted outlets from the catalogue — real poster, wordmark/logo, category and description,
    no invented headlines or dates. A card opens the existing outlet detail dialog. The track is a native
    scroll-snap row (swipe on touch); the arrows only render once JS detects an overflow.
--}}
@php
    $newsHeading = (string) $site->get('news_heading');
    $newsButton = (string) $site->get('news_button_label') ?: 'See All News';
@endphp
<section id="news" x-data="packageSlider()" class="bg-white pb-16 lg:pb-20">
    <div class="container-site">
        @if (filled($newsHeading))
            <div class="mb-8 flex flex-col items-center text-center" data-reveal-group>
                <span class="accent-bar" data-reveal></span>
                <h2 class="home-h2 mt-4 [&_em]:text-accent-ink [&_em]:not-italic" data-reveal>{!! App\Support\Heading::render($newsHeading) !!}</h2>
            </div>
        @else
            <h2 class="sr-only">Featured media outlets</h2>
        @endif

        @if ($outlets->isEmpty())
            <p class="text-center text-muted">The media catalogue is being updated. <a href="{{ route('media-network') }}" class="text-accent-ink underline">View the media network</a>.</p>
        @else
            <div class="relative">
                <button type="button" x-cloak x-show="canPrev || canNext" @click="go(-1)" :disabled="!canPrev" aria-label="Previous outlets"
                        class="absolute top-[38%] -left-5 z-10 hidden size-11 items-center justify-center rounded-full border border-line bg-white text-heading shadow-[var(--shadow-card)] transition hover:border-teal hover:text-accent-ink disabled:opacity-40 lg:flex xl:-left-14">
                    <x-icon name="chevron-left" :size="20" :stroke="2" />
                </button>
                <button type="button" x-cloak x-show="canPrev || canNext" @click="go(1)" :disabled="!canNext" aria-label="Next outlets"
                        class="absolute top-[38%] -right-5 z-10 hidden size-11 items-center justify-center rounded-full border border-line bg-white text-heading shadow-[var(--shadow-card)] transition hover:border-teal hover:text-accent-ink disabled:opacity-40 lg:flex xl:-right-14">
                    <x-icon name="chevron-right" :size="20" :stroke="2" />
                </button>

                <div x-ref="track" @scroll.passive="update()" @keydown.left.prevent="go(-1)" @keydown.right.prevent="go(1)" tabindex="0" role="region" aria-label="Featured media outlets"
                     class="-mx-2 flex snap-x snap-mandatory gap-6 overflow-x-auto scroll-smooth scroll-px-2 px-2 pt-2 pb-6 [scrollbar-width:none] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent [&::-webkit-scrollbar]:hidden"
                     data-reveal-group>
                    @foreach ($outlets as $outlet)
                        <article data-reveal data-slide
                                 class="group flex shrink-0 snap-start basis-[84%] sm:basis-[calc((100%-1.5rem)/2)] lg:basis-[calc((100%-3rem)/3)]">
                            <button type="button" @click="$dispatch('open-outlet', @js($outlet->slug))" aria-haspopup="dialog"
                                    class="flex w-full flex-col rounded-2xl border border-line/70 bg-white p-3 text-left shadow-[0_10px_28px_-14px_rgba(11,22,40,0.22)] transition duration-200 hover:-translate-y-0.5 hover:shadow-[var(--shadow-float)]"
                                    data-track="outlet_click" data-track-label="{{ $outlet->name }}">
                                <span class="relative block aspect-[16/10] w-full overflow-hidden rounded-xl bg-canvas-deep">
                                    @if ($outlet->poster_src)
                                        <img src="{{ $outlet->poster_src }}" alt="" loading="lazy" decoding="async" width="800" height="500"
                                             class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]">
                                    @endif
                                    @if ($outlet->logo_src)
                                        <img src="{{ $outlet->logo_src }}" alt="{{ $outlet->name }}" loading="lazy" class="absolute top-3 left-3 h-12 w-auto max-w-[150px] object-contain drop-shadow-[0_2px_6px_rgba(11,22,40,0.35)]">
                                    @else
                                        <span class="absolute top-3 left-3 flex h-8 items-center rounded-md bg-white px-2.5 text-[13px] font-bold tracking-tight text-heading shadow-sm">{{ $outlet->name }}</span>
                                    @endif
                                </span>
                                <span class="flex grow flex-col px-2 pt-4 pb-2">
                                    <span class="text-[12px] font-medium text-muted-soft">{{ $outlet->category }}</span>
                                    <span class="mt-1.5 line-clamp-3 text-[16px] leading-snug font-semibold text-heading">{{ $outlet->short_description ?: $outlet->name }}</span>
                                    <span class="mt-auto pt-4 text-heading transition group-hover:translate-x-1 group-hover:text-accent-ink">
                                        <x-icon name="arrow-right" :size="18" :stroke="2" />
                                    </span>
                                </span>
                            </button>
                        </article>
                    @endforeach
                </div>
            </div>

            <div class="mt-4 flex justify-center">
                <a href="{{ route('media-network') }}" class="btn-pill btn-ghost h-11 text-[14px]" data-track="cta_click" data-track-label="News: {{ $newsButton }}">
                    {{ $newsButton }}
                    <span class="btn-pill-icon size-7"><x-icon name="arrow-right" :size="14" :stroke="2.4" /></span>
                </a>
            </div>
        @endif
    </div>

    <x-outlet-modal :outlets="$outlets" />
</section>
