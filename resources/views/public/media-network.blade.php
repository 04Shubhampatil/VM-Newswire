<x-layouts.public title="Media Network" description="See the newswires, publications and categories where VM Newswire press releases can appear.">
    <section class="pt-9 pb-14 md:pt-16 md:pb-20 lg:pt-[72px] lg:pb-24">
        <div class="container-site flex flex-col gap-14 xl:grid xl:grid-cols-[minmax(0,1fr)_660px] xl:items-center xl:gap-20">
            <div class="flex flex-col gap-6">
                <x-breadcrumb :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'Media Network']]" />
                <p class="eyebrow">Media network</p>
                <h1 class="display text-[36px] leading-[1.1] tracking-[-0.02em] md:text-[48px] lg:text-[60px]">Where your press release <em class="text-accent italic">can appear.</em></h1>
                <p class="text-base leading-relaxed text-muted md:text-lg">VM Newswire packages combine recognised newswires and headline publications with an extended network of {{ $site->get('network_size_label') }} digital outlets across business, finance, news, technology, markets and digital media.</p>
                <dl class="mt-2 grid grid-cols-3 border-t border-line">
                    @foreach ([[$site->get('network_size_label'), 'media outlets'], [$summary->count(), 'publication categories'], [number_format($totalOutlets), 'outlets listed']] as [$value, $label])
                        <div class="flex flex-col-reverse gap-1 pt-5 pr-2 md:pr-5 [&+div]:border-l [&+div]:border-line [&+div]:pl-3 md:[&+div]:pl-5">
                            <dt class="text-[13px] text-muted">{{ $label }}</dt>
                            <dd class="font-display text-4xl leading-none font-semibold md:text-[38px]">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
            <x-network-map :summary="$summary" />
        </div>
    </section>

    <section id="directory" class="border-t border-line section-y">
        <div class="container-site flex flex-col gap-8">
            <x-section-heading eyebrow="Outlet directory" description="Filter by category to see which outlets appear in which packages.">Featured publications</x-section-heading>

            <form method="GET" action="{{ route('media-network') }}#directory" class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div role="group" aria-label="Filter by category" class="-mx-5 flex gap-2 overflow-x-auto px-5 md:mx-0 md:flex-wrap md:px-0">
                    @foreach (collect(['All' => null])->merge($summary->pluck('category')->mapWithKeys(fn ($c) => [$c => $c])) as $label => $value)
                        @php $active = ($filters['category'] ?? null) === $value; @endphp
                        <a href="{{ route('media-network', array_filter(['category' => $value, 'q' => $filters['q'] ?? null])) }}#directory"
                           @if ($active) aria-current="true" @endif
                           class="flex h-11 shrink-0 items-center rounded-[6px] border px-[18px] text-sm font-semibold transition {{ $active ? 'border-ink bg-ink text-white' : 'border-line bg-white hover:border-ink' }}">{{ $label }}</a>
                    @endforeach
                </div>
                <label class="flex h-11 items-center gap-2.5 rounded-[6px] border border-line bg-white px-3.5 text-muted focus-within:border-accent lg:w-72">
                    <x-icon name="search" :size="17" />
                    <span class="sr-only">Search outlets</span>
                    @if (! empty($filters['category']))<input type="hidden" name="category" value="{{ $filters['category'] }}">@endif
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search outlets" class="h-10 grow bg-transparent text-[15px] text-ink outline-none">
                </label>
            </form>

            @if ($outlets->isEmpty())
                <p class="rounded-card border border-line bg-white p-8 text-muted">No outlets match your filters.</p>
            @else
                <ul class="grid gap-4 md:grid-cols-2 xl:grid-cols-3" data-reveal-group>
                    @foreach ($outlets as $outlet)
                        <li data-reveal class="card-lift flex min-h-[150px] flex-col justify-between gap-4 rounded-card border border-line bg-white p-6">
                            <div class="flex items-start justify-between gap-3">
                                <span class="font-display text-[28px] leading-[1.12] font-semibold">{{ $outlet->name }}</span>
                                <span class="shrink-0 text-[11px] font-bold tracking-[0.12em] text-accent-ink uppercase">{{ $outlet->category }}</span>
                            </div>
                            <div class="flex flex-col gap-1 border-t border-line-soft pt-3">
                                <span class="label-caps">Included in</span>
                                @if ($outlet->packages->isNotEmpty())
                                    <span class="text-sm font-semibold">
                                        @foreach ($outlet->packages as $package)
                                            <a href="{{ route('packages.show', $package->slug) }}" class="hover:text-accent-ink hover:underline">{{ $package->name }}</a>@if (! $loop->last)<span class="text-muted"> · </span>@endif
                                        @endforeach
                                    </span>
                                @else
                                    <span class="text-sm text-muted">Available on request</span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
                <x-pagination :paginator="$outlets" />
            @endif

            <div class="flex flex-col gap-4 rounded-card bg-accent-soft p-6 md:flex-row md:items-center md:justify-between md:gap-8 md:px-8">
                <p class="max-w-[720px] text-base leading-relaxed"><strong>The network varies by package.</strong> The complete outlet list for each package is included in its sample report.</p>
                <x-button :href="route('packages.index')" size="sm" class="h-12! max-md:w-full">View Packages</x-button>
            </div>
        </div>
    </section>

    <x-cta-band />
</x-layouts.public>
