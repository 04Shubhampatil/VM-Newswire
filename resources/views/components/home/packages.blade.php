@props(['packages', 'brands'])
{{--
    Pricing tier (24-7 "Pricing Plans to Choose From"): grey ground, centred heading, then a horizontal card slider.
    The frame shows five cards on wide screens (three on laptops, two on tablets, one-and-a-bit on phones) and the
    rest slide in with the prev/next arrows, a swipe, or a trackpad scroll. Without JS every card stays visible,
    the track scrolls natively and the brand chips are inert.
--}}
@php
    $filters = ['All Packages' => 'All', ...collect($brands)->filter()->unique()->mapWithKeys(fn ($b) => [$b => $b])->all()];
    $heading = (string) $site->get('packages_heading', 'Packages to choose from');
@endphp
<section id="packages" x-data="packageSlider()" class="mb-16 bg-[#e6edf4] py-16 lg:mb-24 lg:py-24">
    <div class="container-site">
        <div class="mb-10 flex flex-col items-center gap-3 text-center" data-reveal-group>
            <p class="flex items-center gap-3 text-[12px] font-semibold tracking-[0.14em] text-heading uppercase" data-reveal>
                <span class="accent-bar w-8"></span>{{ $site->get('packages_eyebrow') }}
            </p>
            <h2 class="home-h2 mt-2 [&_em]:text-accent-ink [&_em]:not-italic" data-reveal>{!! App\Support\Heading::render($heading) !!}</h2>
            @if ($site->get('packages_text'))
                <p class="max-w-2xl text-[16px] text-muted sm:text-[17px]" data-reveal>{{ $site->get('packages_text') }}</p>
            @endif
        </div>

        @if ($packages->isEmpty())
            <p class="text-center text-muted">Packages are being updated. <a href="{{ route('contact') }}" class="text-accent-ink underline">Contact us</a> for current options.</p>
        @else
            <div class="mb-8 flex items-center justify-between gap-4">
                @if (count($filters) > 2)
                    <div class="flex min-w-0 items-center gap-2 overflow-x-auto pb-1 text-[13px] font-medium [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" role="group" aria-label="Filter packages by network">
                        @foreach ($filters as $label => $value)
                            {{-- Active look comes only from aria-pressed, so static and Alpine classes can never conflict. --}}
                            <button type="button" @click="setBrand(@js($value))" :aria-pressed="(brand === @js($value)).toString()" aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                                    class="shrink-0 rounded-[4px] border border-transparent bg-white px-4 py-2 whitespace-nowrap text-muted transition hover:text-ink aria-pressed:bg-accent-fill aria-pressed:text-white">{{ $label }}</button>
                        @endforeach
                    </div>
                @else
                    <div></div>
                @endif

                {{-- Slider controls: only rendered once JS knows the track overflows the frame. --}}
                <div x-cloak x-show="canPrev || canNext" class="flex shrink-0 items-center gap-2">
                    <button type="button" @click="go(-1)" :disabled="!canPrev" aria-label="Previous packages"
                            class="flex size-10 items-center justify-center rounded-[4px] bg-white text-ink shadow-sm transition hover:bg-accent-fill hover:text-white disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-white disabled:hover:text-ink">
                        <x-icon name="chevron-left" :size="18" :stroke="2" />
                    </button>
                    <button type="button" @click="go(1)" :disabled="!canNext" aria-label="Next packages"
                            class="flex size-10 items-center justify-center rounded-[4px] bg-white text-ink shadow-sm transition hover:bg-accent-fill hover:text-white disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-white disabled:hover:text-ink">
                        <x-icon name="chevron-right" :size="18" :stroke="2" />
                    </button>
                </div>
            </div>

            {{-- The track is the only scroll container; top padding leaves room for the "Most Popular" tag. --}}
            <div x-ref="track" @scroll.passive="update()" @keydown.left.prevent="go(-1)" @keydown.right.prevent="go(1)" tabindex="0" role="region" aria-label="Package slider"
                 class="-mx-1 flex snap-x snap-mandatory gap-4 overflow-x-auto scroll-smooth scroll-px-1 px-1 pt-5 pb-4 [scrollbar-width:none] motion-reduce:scroll-auto focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent [&::-webkit-scrollbar]:hidden" data-reveal-group>
                @foreach ($packages as $i => $package)
                    <div x-show="brand === 'All' || brand === @js($package->brand)" data-reveal data-slide
                         class="flex shrink-0 snap-start basis-[88%] sm:basis-[calc((100%-1rem)/2)] lg:basis-[calc((100%-2rem)/3)] xl:basis-[calc((100%-4rem)/5)]">
                        <x-package-card :package="$package" :index="$i" class="w-full" />
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
