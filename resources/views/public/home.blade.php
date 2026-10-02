<x-layouts.public>
    @push('schema')
        <script type="application/ld+json">{!! json_encode([
            '@context' => 'https://schema.org',
            '@graph' => [
                array_filter([
                    '@type' => 'Organization',
                    'name' => $site->get('company_name'),
                    'url' => route('home'),
                    'email' => $site->get('company_email'),
                    'telephone' => $site->get('phone') ?: null,
                    'logo' => asset('favicon.svg'),
                    'sameAs' => array_values(array_filter([$site->get('social_linkedin'), $site->get('social_x'), $site->get('social_facebook')])) ?: null,
                ]),
                ['@type' => 'WebSite', 'name' => $site->get('company_name'), 'url' => route('home')],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endpush

    {{-- Hero: copy first (eyebrow → headline → text → CTA → trust), then the distribution network --}}
    <section x-data class="pt-10 pb-20 md:pt-16 md:pb-24 lg:pt-20 lg:pb-28">
        <div class="container-site flex flex-col gap-12 xl:grid xl:grid-cols-[minmax(0,1fr)_700px] xl:items-center xl:gap-12">
            <div class="flex max-w-[640px] flex-col gap-6 md:gap-8">
                <p class="eyebrow">{{ $site->get('hero_eyebrow') }}</p>
                <h1 class="display text-[36px] leading-[1.1] tracking-[-0.02em] sm:text-[34px] md:text-[50px] lg:text-[60px]">
                    {{ $site->get('hero_headline') }} <em class="font-medium text-accent italic">{{ $site->get('hero_headline_highlight') }}</em>
                </h1>
                <p class="max-w-[540px] text-base leading-relaxed text-muted md:text-lg">{{ $site->get('hero_text') }}</p>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <x-button :href="route('packages.index')" icon="arrow-right" data-track="cta_click" data-track-label="Hero: View Packages">{{ $site->get('hero_primary_label') }}</x-button>
                    {{-- Opens the enquiry popover; the href is the no-JS fallback --}}
                    <x-button :href="route('contact')" variant="secondary" @click.prevent="$dispatch('open-enquiry')" aria-haspopup="dialog" data-track="cta_click" data-track-label="Hero: Submit Your Enquiry">{{ $site->get('hero_secondary_label') }}</x-button>
                </div>
                <ul class="flex flex-col gap-3 border-t border-line pt-5 sm:flex-row sm:gap-0 md:pt-7">
                    @foreach (array_filter(array_map('trim', preg_split('/\R/', str_replace('{network}', $site->get('network_size_label'), $site->get('hero_trust_items'))))) as $item)
                        <li class="flex items-center gap-2.5 text-[13px] font-semibold sm:px-6 sm:first:pl-0 sm:[&+li]:border-l sm:[&+li]:border-line">
                            <span class="size-1.5 rounded-full bg-accent"></span>{{ $item }}
                        </li>
                    @endforeach
                </ul>
            </div>
            @if ($highlighted->isNotEmpty())
                <x-hero-network :outlets="$highlighted" />
            @endif
        </div>
    </section>

    {{-- Media credibility strip: slow marquee that pauses on hover (static under reduced motion) --}}
    @if ($highlighted->isNotEmpty())
        <section aria-labelledby="trust-heading" class="border-y border-line bg-white py-10 md:py-14">
            <div class="container-site flex flex-col gap-6 md:items-center">
                <h2 id="trust-heading" class="text-xs font-bold tracking-[0.12em] text-muted uppercase">{{ $site->get('trust_strip_heading') }}</h2>
                <div class="marquee w-full overflow-hidden">
                    <div class="marquee-track">
                        @foreach ([false, true] as $clone)
                            <ul class="flex shrink-0 items-center {{ $clone ? 'marquee-clone' : '' }}" @if ($clone) aria-hidden="true" @endif>
                                @foreach ($highlighted as $outlet)
                                    <li class="flex items-center px-6 font-display text-[22px] font-bold whitespace-nowrap text-ink opacity-55 transition-opacity duration-300 hover:opacity-100 md:px-10 md:text-[26px]">
                                        @if ($outlet->logo_src)
                                            {{-- Brand logo (uploaded or URL in Admin → Media Network); shown greyscale until hovered --}}
                                            <img src="{{ $outlet->logo_src }}" alt="{{ $outlet->name }}" loading="lazy" decoding="async" class="h-8 w-auto max-w-[160px] object-contain grayscale transition duration-300 hover:grayscale-0 md:h-10 md:max-w-[200px]">
                                        @else
                                            {{ $outlet->name }}
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Packages --}}
    <section id="packages" class="section-y border-t border-line">
        <div class="container-site flex flex-col gap-10 md:gap-14 lg:gap-16">
            <div class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between md:gap-10">
                <x-section-heading :eyebrow="$site->get('packages_eyebrow')" :description="$site->get('packages_text')">
                    {!! collect(preg_split('/\R/', $site->get('packages_heading')))->map(fn ($line) => e(trim($line)))->filter()->join('<br class="hidden md:block"> ') !!}
                </x-section-heading>
                <a href="{{ route('packages.index') }}" data-reveal class="link-arrow shrink-0 self-start md:self-end">Compare all packages <x-icon name="arrow-right" :size="16" /></a>
            </div>
            @if ($packages->isEmpty())
                <p class="text-muted">Packages are being updated. <a href="{{ route('contact') }}" class="text-accent-ink underline">Contact us</a> for current options.</p>
            @else
                <div class="grid gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-3 xl:items-center" data-reveal-group>
                    @foreach ($packages as $package)
                        <x-package-card :package="$package" :index="$loop->iteration" data-reveal />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Media reach: "200+" → label → copy → network nodes --}}
    <section class="section-y border-t border-line">
        <div class="container-site flex flex-col gap-14 xl:grid xl:grid-cols-[minmax(0,1fr)_660px] xl:items-center xl:gap-20">
            <div class="flex max-w-[520px] flex-col gap-5" data-reveal-group>
                <p data-reveal class="eyebrow">Media reach</p>
                <p class="flex flex-col">
                    <span data-reveal="clip" class="font-display text-[96px] leading-[0.9] font-medium tracking-[-0.04em] md:text-[136px] lg:text-[160px]">{{ rtrim($site->get('network_size_label'), '+') }}<span class="text-accent">{{ str_ends_with($site->get('network_size_label'), '+') ? '+' : '' }}</span></span>
                    <span data-reveal class="font-display text-[34px] leading-tight font-medium italic md:text-[38px]">media outlets</span>
                </p>
                <p data-reveal class="text-[17px] leading-relaxed text-muted">Beyond the headline platforms in each package, your release can extend across a wider network of business, finance, news, technology, markets and digital media publications. The exact network depends on the package you choose.</p>
                <a data-reveal href="{{ route('media-network') }}" class="link-arrow mt-2 self-start">Explore Media Network <x-icon name="arrow-right" :size="16" /></a>
            </div>
            <x-network-map :summary="$mediaSummary" />
        </div>
    </section>

    {{-- How it works --}}
    <section id="how-it-works" class="section-y border-t border-line bg-white">
        <div class="container-site flex flex-col gap-12 md:gap-16">
            <x-section-heading eyebrow="How it works">From enquiry to published.</x-section-heading>
            <x-steps />
        </div>
    </section>

    <x-enquiry-section :packages="$packages" />
    <x-enquiry-modal :packages="$packages" />
</x-layouts.public>
