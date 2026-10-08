<x-layouts.public title="Media Network" description="See the newswires, publications and categories where VM Newswire press releases can appear.">
    @php
        // Hero photo: uploaded in Admin → Website Content, or the default newsroom photo that ships with the site.
        $photo = $site->get('media_network_image')
            ? Storage::disk(config('vmnewswire.posters.disk'))->url($site->get('media_network_image'))
            : asset('images/media-network-hero.webp');
        $categories = collect(['All' => null])->merge($summary->pluck('category')->mapWithKeys(fn ($c) => [$c => $c]));
        $activeFilters = array_filter($filters);
    @endphp

    {{-- Hero: copy and stats on the left (5/12), the newsroom photo on the right (7/12) --}}
    <section class="relative overflow-hidden bg-white pt-10 pb-14 lg:pt-14 lg:pb-20" aria-labelledby="network-heading">
        <span class="hero-glow" aria-hidden="true"></span>
        <div class="container-site relative">
            <x-breadcrumb :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'Media Network']]" class="sr-only" />
            <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-12 lg:gap-12">
                <div class="flex flex-col items-start lg:col-span-5">
                    <p class="hero-in flex items-center gap-3 text-[12px] font-semibold tracking-[0.14em] text-heading uppercase" style="--i: 0">
                        <span class="accent-bar w-8"></span>{{ $site->get('media_network_eyebrow') }}
                    </p>
                    <h1 id="network-heading" class="hero-in mt-5 text-[40px] leading-[1.08] font-bold tracking-[-0.025em] text-heading sm:text-[50px] lg:text-[54px] [&_em]:text-accent-ink [&_em]:not-italic" style="--i: 1">
                        {!! App\Support\Heading::render($site->get('media_network_heading')) !!}
                    </h1>
                    <p class="hero-in mt-5 max-w-[520px] text-[16px] leading-[1.7] text-muted sm:text-[17px]" style="--i: 2">
                        {{ str_replace(['{company}', '{network}'], [$site->get('company_name'), $site->get('network_size_label')], (string) $site->get('media_network_text')) }}
                    </p>

                    <dl class="hero-in mt-8 grid w-full max-w-[520px] grid-cols-3 divide-x divide-line rounded-2xl border border-line bg-white px-2 py-5 shadow-[var(--shadow-card)] sm:px-4" style="--i: 3">
                        <div class="flex flex-col-reverse px-3 sm:px-4">
                            <dt class="mt-1 text-[12px] leading-snug font-medium text-muted">media outlets</dt>
                            <dd class="text-[26px] leading-none font-bold tracking-[-0.02em] text-heading sm:text-[30px]">{{ $site->get('network_size_label') }}</dd>
                        </div>
                        <div class="flex flex-col-reverse px-3 sm:px-4">
                            <dt class="mt-1 text-[12px] leading-snug font-medium text-muted">publication categories</dt>
                            <dd class="text-[26px] leading-none font-bold tracking-[-0.02em] text-accent-ink sm:text-[30px]">{{ $summary->count() }}</dd>
                        </div>
                        <div class="flex flex-col-reverse px-3 sm:px-4">
                            <dt class="mt-1 text-[12px] leading-snug font-medium text-muted">outlets listed</dt>
                            <dd class="text-[26px] leading-none font-bold tracking-[-0.02em] text-heading sm:text-[30px]">{{ number_format($totalOutlets) }}</dd>
                        </div>
                    </dl>
                </div>

                <figure class="lg:col-span-7">
                    <img src="{{ $photo }}" alt="{{ $site->get('company_name') }} newsroom monitoring distribution across media dashboards" width="960" height="715" fetchpriority="high" decoding="async"
                         class="aspect-[4/3] w-full rounded-2xl object-cover shadow-[var(--shadow-float)]">
                </figure>
            </div>
        </div>
    </section>

    {{-- Outlet directory --}}
    <section id="directory" class="scroll-mt-24 bg-[#f5f9fc] py-16 lg:py-24">
        <div class="container-site">
            <div class="flex flex-col justify-between gap-6 lg:flex-row lg:items-end" data-reveal-group>
                <div data-reveal>
                    <p class="flex items-center gap-3 text-[12px] font-semibold tracking-[0.14em] text-accent-ink uppercase">
                        <span class="accent-bar w-8"></span>{{ $site->get('directory_eyebrow') }}
                    </p>
                    <h2 class="home-h2 mt-4 [&_em]:text-accent-ink [&_em]:not-italic">{!! App\Support\Heading::render($site->get('directory_heading')) !!}</h2>
                    @if ($site->get('directory_text'))
                        <p class="mt-2 text-[16px] text-muted">{{ $site->get('directory_text') }}</p>
                    @endif
                </div>
                <form method="GET" action="{{ route('media-network') }}#directory" class="relative w-full lg:w-80" role="search" data-reveal>
                    @if (! empty($filters['category']))<input type="hidden" name="category" value="{{ $filters['category'] }}">@endif
                    <label for="outlet-search" class="sr-only">Search media outlets</label>
                    <x-icon name="search" :size="18" class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-muted-soft" />
                    <input id="outlet-search" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search media outlets..."
                           class="h-12 w-full rounded-xl border border-line bg-white pr-4 pl-11 text-[15px] text-ink shadow-[var(--shadow-card)] transition placeholder:text-muted-soft hover:border-[#b9c7d4] focus:border-teal focus:ring-4 focus:ring-teal/15 focus:outline-none">
                </form>
            </div>

            <div class="mt-8 flex items-center gap-2 overflow-x-auto pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" role="group" aria-label="Filter by category">
                @foreach ($categories as $label => $value)
                    @php $active = ($filters['category'] ?? null) === $value; @endphp
                    <a href="{{ route('media-network', array_filter(['category' => $value, 'q' => $filters['q'] ?? null])) }}#directory" @if ($active) aria-current="true" @endif
                       class="flex h-9 shrink-0 items-center rounded-full border px-4 text-[13px] font-semibold whitespace-nowrap transition {{ $active ? 'border-navy-900 bg-navy-900 text-white' : 'border-line bg-white text-heading hover:border-teal hover:text-accent-ink' }}">{{ $label }}</a>
                @endforeach
            </div>

            @if ($outlets->isEmpty())
                <div class="mt-8 flex flex-col items-center justify-center rounded-2xl border border-line bg-white px-6 py-14 text-center shadow-[var(--shadow-card)]">
                    <span class="flex size-12 items-center justify-center rounded-full bg-teal-soft text-accent-ink"><x-icon name="search" :size="22" :stroke="1.8" /></span>
                    <h3 class="mt-4 text-[18px] font-semibold text-heading">No media outlets match your search</h3>
                    <p class="mt-1 max-w-sm text-[14px] text-muted">Try another term or clear the filters.</p>
                    <a href="{{ route('media-network') }}#directory" class="btn-pill mt-6 h-11 bg-navy-900 text-[14px] text-white hover:bg-brand">
                        Reset filters
                        <span class="btn-pill-icon size-7 bg-white text-heading"><x-icon name="arrow-right" :size="14" :stroke="2.4" /></span>
                    </a>
                </div>
            @else
                <ul class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3" data-reveal-group>
                    @foreach ($outlets as $outlet)
                        <li data-reveal>
                            <article class="flex h-full flex-col rounded-2xl border border-line bg-white p-6 shadow-[var(--shadow-card)] transition duration-200 hover:-translate-y-0.5 hover:border-[#b9c7d4] hover:shadow-[var(--shadow-float)]">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex min-w-0 items-center gap-3">
                                        @if ($outlet->logo_src)
                                            <img src="{{ $outlet->logo_src }}" alt="" loading="lazy" class="h-8 w-auto max-w-[96px] shrink-0 object-contain">
                                        @endif
                                        <h3 class="text-[18px] leading-snug font-semibold text-heading">{{ $outlet->name }}</h3>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-[#ddf7f2] px-2.5 py-1 text-[10px] font-bold tracking-[0.08em] text-[#0c7c6e] uppercase">{{ $outlet->category }}</span>
                                </div>
                                @if ($outlet->short_description)
                                    <p class="mt-3 text-[14px] leading-relaxed text-muted">{{ $outlet->short_description }}</p>
                                @endif
                                <div class="mt-auto border-t border-line-soft pt-4 {{ $outlet->short_description ? 'mt-5' : 'mt-6' }}">
                                    <p class="text-[11px] font-semibold tracking-[0.12em] text-muted-soft uppercase">Included in</p>
                                    @if ($outlet->packages->isNotEmpty())
                                        <p class="mt-1.5 text-[13px] font-medium leading-relaxed text-heading">
                                            @foreach ($outlet->packages as $package)
                                                <a href="{{ route('packages.show', $package->slug) }}" class="transition hover:text-accent-ink hover:underline">{{ $package->name }}</a>@if (! $loop->last)<span class="text-muted-soft"> &bull; </span>@endif
                                            @endforeach
                                        </p>
                                    @else
                                        <p class="mt-1.5 text-[13px] font-medium text-muted">Available on request</p>
                                    @endif
                                </div>
                            </article>
                        </li>
                    @endforeach
                </ul>
                <x-pagination :paginator="$outlets" />
            @endif
        </div>
    </section>

    {{-- Network notice --}}
    <section class="bg-white pt-16 pb-6 lg:pt-20">
        <div class="container-site">
            <div class="flex flex-col items-center justify-between gap-6 rounded-2xl border border-teal/30 bg-[#e3f7f4] p-6 sm:p-8 md:flex-row">
                <div class="flex items-center gap-4">
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-white text-accent-ink shadow-[var(--shadow-card)]" aria-hidden="true"><x-icon name="info" :size="20" :stroke="1.8" /></span>
                    <p class="text-[15px] leading-relaxed font-medium text-heading sm:text-[16px]">The network varies by package. The complete outlet list for each package is included in its sample report.</p>
                </div>
                <a href="{{ route('packages.index') }}" class="btn-pill w-full bg-navy-900 text-white hover:bg-brand max-sm:justify-between md:w-auto" data-track="cta_click" data-track-label="Media network: View Packages">
                    View Packages
                    <span class="btn-pill-icon bg-white text-heading"><x-icon name="arrow-right" :size="15" :stroke="2.4" /></span>
                </a>
            </div>
        </div>
    </section>

    <x-home.final-cta heading="Ready to reach the right audience?"
                      text="Choose a VM Newswire distribution package and get your story in front of the publications that matter."
                      label="View Packages" :href="route('packages.index')"
                      secondary-label="Send an Enquiry" :secondary-href="route('contact').'#enquire'" />
</x-layouts.public>
