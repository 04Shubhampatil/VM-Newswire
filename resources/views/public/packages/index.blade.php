<x-layouts.public title="Compare Press Release Distribution Packages" description="Compare VM Newswire press release packages side by side: major media, distribution network, price and sample reports.">
    {{-- Hero: same composition as the homepage hero --}}
    <section x-data class="relative overflow-hidden bg-white pt-8 pb-14 lg:pb-16" aria-labelledby="packages-heading">
        <span class="hero-glow" aria-hidden="true"></span>
        <div class="container-site relative">
            <div class="flex items-center justify-between gap-4">
                <x-breadcrumb :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'Packages']]" />
                <a href="{{ route('contact') }}" class="hidden items-center gap-1.5 text-[13px] font-semibold text-accent-ink transition hover:text-brand sm:inline-flex">Not sure which? Ask us <x-icon name="arrow-right" :size="15" :stroke="2" /></a>
            </div>

            <div class="mt-10 grid grid-cols-1 items-center gap-14 lg:grid-cols-12 lg:gap-8">
                <div class="lg:col-span-7">
                    <p class="hero-in flex items-center gap-3 text-[12px] font-semibold tracking-[0.14em] text-heading uppercase" style="--i: 0">
                        <span class="accent-bar w-8"></span>All Packages
                    </p>
                    <h1 id="packages-heading" class="hero-in mt-5 text-[40px] leading-[1.08] font-bold tracking-[-0.025em] text-heading sm:text-[52px] lg:text-[56px]" style="--i: 1">
                        Compare packages <br class="hidden sm:block">at a glance.
                    </h1>
                    <p class="hero-in mt-5 max-w-[500px] text-[17px] leading-[1.7] text-muted" style="--i: 2">
                        Find the distribution package that matches your reach, media and reporting requirements.
                    </p>
                    <div class="hero-in mt-8 flex flex-col gap-3 sm:flex-row sm:items-center" style="--i: 3">
                        <a href="{{ route('contact') }}" @click.prevent="$dispatch('open-enquiry')" aria-haspopup="dialog"
                           class="btn-pill btn-teal max-sm:justify-between" data-track="cta_click" data-track-label="Packages hero: Send a Press Release">
                            Send a Press Release
                            <span class="btn-pill-icon bg-white text-[#0e8c7e]"><x-icon name="arrow-right" :size="15" :stroke="2.4" /></span>
                        </a>
                        <a href="#compare" class="btn-pill border-[1.5px] border-teal bg-white text-heading hover:bg-teal-soft max-sm:justify-between">
                            Learn More
                            <span class="btn-pill-icon bg-[#14a893] text-white"><svg width="11" height="12" viewBox="0 0 11 12" fill="currentColor" aria-hidden="true"><path d="M1 1.2v9.6a.8.8 0 0 0 1.2.7l8-4.8a.8.8 0 0 0 0-1.4l-8-4.8A.8.8 0 0 0 1 1.2z"/></svg></span>
                        </a>
                    </div>
                    <a href="{{ route('contact') }}" class="mt-6 inline-flex items-center gap-1.5 text-[13px] font-semibold text-accent-ink sm:hidden">Not sure which? Ask us <x-icon name="arrow-right" :size="15" :stroke="2" /></a>
                </div>

                <div class="lg:col-span-5">
                    <x-hero-visual />
                </div>
            </div>
        </div>
    </section>

    {{-- Package cards (pulled up over the hero edge, like the homepage news cards) --}}
    <section class="relative z-10 bg-white pb-16 lg:pb-20" aria-label="Packages">
        <div class="container-site">
            @if ($packages->isEmpty())
                <p class="rounded-2xl border border-line bg-white p-8 text-center text-muted">Packages are being updated. <a href="{{ route('contact') }}" class="text-accent-ink underline">Contact us</a> for current options.</p>
            @else
                <ul class="grid grid-cols-1 gap-6 pt-4 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
                    @foreach ($packages as $i => $package)
                        <li class="flex" data-reveal><x-package-tile :package="$package" :index="$i" class="w-full" /></li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>

    @if ($packages->isNotEmpty())
        {{-- Side-by-side comparison --}}
        <section id="compare" class="bg-[#f5f9fc] py-16 lg:py-24" aria-labelledby="compare-heading">
            <div class="container-site">
                <p class="flex items-center gap-3 text-[12px] font-semibold tracking-[0.14em] text-accent-ink uppercase">
                    <span class="accent-bar w-8"></span>Compare in detail
                </p>
                <h2 id="compare-heading" class="home-h2 mt-4">Side by side: media, reports and price.</h2>
                <p class="mt-3 mb-8 max-w-2xl text-[16px] text-muted">Filter by network to see each package's sample report, distribution and price in one place.</p>
                <x-comparison-table :packages="$packages" :brands="$brands" />
            </div>
        </section>
    @endif

    {{-- Decision support --}}
    <section class="bg-white pt-16 pb-16 lg:pt-20" aria-label="What every package includes">
        <div class="container-site">
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-3 lg:gap-6" data-reveal-group>
                @foreach ([
                    ['clipboard', 'Every package includes reporting', 'A professional distribution report shows where your release was published, with live links.'],
                    ['file', 'Sample report before you choose', 'Download a sample report from any package page to see real placements.'],
                    ['info', 'Not sure which package fits?', 'Choose "Not sure" on the enquiry form and our team will recommend one.'],
                ] as [$icon, $title, $text])
                    <article data-reveal class="flex gap-4 rounded-2xl border border-line bg-white p-6 transition hover:border-[#b9c7d4]">
                        <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-teal-soft text-accent-ink"><x-icon :name="$icon" :size="20" :stroke="1.8" /></span>
                        <div>
                            <h2 class="text-[16px] leading-snug font-semibold text-heading">{{ $title }}</h2>
                            <p class="mt-2 text-[14px] leading-relaxed text-muted">{{ $text }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <x-home.final-cta text="Choose the package that fits your distribution goals, or tell us about your announcement and we will recommend one." label="Send a Press Release" />
    <x-enquiry-modal :packages="$packages" />
    <x-report-modal />
</x-layouts.public>
