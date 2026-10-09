@php
    $activeCategory = $filters['category'] ?? null;
    $query = $filters['q'] ?? null;
    $nextUrl = $releases->hasMorePages() ? $releases->nextPageUrl() : '';
    $isFirstPage = $releases->currentPage() === 1;
    $featured = $isFirstPage ? $releases->first() : null;
    $rows = $isFirstPage ? $releases->slice(1) : $releases;
@endphp
<x-layouts.public title="Newsroom" description="Latest press releases and company announcements distributed through VM Newswire." :canonical="route('newsroom.index')">
    {{-- Hero: heading + search left, photo right --}}
    <section class="nrx-hero">
        <div class="container-site nrx-hero-grid">
            <div>
                <span class="nrx-eyebrow">Newsroom</span>
                <h1 class="nrx-h1">Latest news<br>around the world</h1>
                <p class="nrx-lead">Press releases and announcements from companies distributing their story through {{ $site->get('company_name') }}.</p>
                <form method="GET" action="{{ route('newsroom.index') }}" role="search" class="nrx-search">
                    @if ($activeCategory)<input type="hidden" name="category" value="{{ $activeCategory }}">@endif
                    <label for="newsroom-search" class="sr-only">Search press releases</label>
                    <input id="newsroom-search" type="search" name="q" value="{{ $query }}" maxlength="100" placeholder="Search press releases...">
                    <button type="submit" aria-label="Search"><x-icon name="search" :size="16" :stroke="2.4" /></button>
                </form>
            </div>
            <img src="{{ asset('images/package-story.webp') }}" alt="Journalist working on a story in a newsroom" width="1200" height="896" fetchpriority="high" class="nrx-hero-photo">
        </div>
    </section>

    {{-- Category tabs --}}
    <div class="nrx-tabs-wrap">
        <div class="container-site">
            <nav class="nrx-tabs" aria-label="Filter by category">
                <a href="{{ route('newsroom.index', array_filter(['q' => $query])) }}" class="nrx-tab" @if (! $activeCategory) aria-current="true" @endif>All Categories</a>
                @foreach ($categories as $category)
                    <a href="{{ route('newsroom.index', array_filter(['category' => $category, 'q' => $query])) }}" class="nrx-tab" @if ($activeCategory === $category) aria-current="true" @endif>{{ $category }}</a>
                @endforeach
            </nav>
        </div>
    </div>

    <section class="nrx-main">
        <div class="container-site nrx-layout">
            <div class="nrx-content">
                @if ($releases->isEmpty())
                    <div class="nr-empty">
                        <h2>No press releases found</h2>
                        <p>Try another search term or category.</p>
                        <a href="{{ route('newsroom.index') }}" class="btn-pill btn-ghost">Show all press releases <span class="btn-pill-icon"><x-icon name="arrow-right" :size="15" :stroke="2.4" /></span></a>
                    </div>
                @else
                    <div x-data="{ next: @js($nextUrl), loading: false, error: false,
                                   async more() {
                                       if (!this.next || this.loading) return;
                                       this.loading = true; this.error = false;
                                       try {
                                           const html = await (await fetch(this.next, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })).text();
                                           const doc = new DOMParser().parseFromString(html, 'text/html');
                                           const list = doc.querySelector('#newsroom-grid');
                                           if (!list) throw new Error('missing');
                                           this.$refs.grid.append(...list.children);
                                           this.next = list.dataset.next || '';
                                       } catch (e) { this.error = true; } finally { this.loading = false; }
                                   } }">
                        <div class="nrx-section-head">
                            <h2 class="nrx-section-title">{{ $activeCategory ? $activeCategory.' Releases' : ($query ? 'Search Results' : 'Featured Releases') }}</h2>
                            <a href="{{ route('newsroom.index') }}" class="nrx-link">View all <x-icon name="arrow-right" :size="14" :stroke="2.2" /></a>
                        </div>

                        @if ($featured)
                            @php $url = route('newsroom.show', $featured->slug); @endphp
                            <article class="nrx-featured">
                                <a href="{{ $url }}" class="nrx-featured-media" tabindex="-1" aria-hidden="true">
                                    @if ($featured->image_url)<img src="{{ $featured->image_url }}" alt="" width="640" height="520">@else<span class="nr-card-placeholder"><x-icon name="file" :size="34" :stroke="1.5" /></span>@endif
                                </a>
                                <div>
                                    <span class="nrx-cat">{{ $featured->category }}</span>
                                    <h3 class="nrx-featured-title"><a href="{{ $url }}">{{ $featured->title }}</a></h3>
                                    <p class="nrx-meta"><span>By {{ $featured->author_name }}</span><time datetime="{{ $featured->published_at?->toDateString() }}">{{ $featured->published_at?->format('d/m/Y') }}</time></p>
                                    <p class="nrx-featured-text">{{ $featured->teaser }}</p>
                                    <a href="{{ $url }}" class="nrx-link">Read more <x-icon name="arrow-right" :size="14" :stroke="2.2" /></a>
                                </div>
                            </article>
                        @endif

                        <div id="newsroom-grid" x-ref="grid" data-next="{{ $nextUrl }}" class="nrx-rows">
                            @foreach ($rows as $release)
                                @include('public.newsroom.partials.card', ['release' => $release])
                            @endforeach
                        </div>

                        <div class="nr-more">
                            <p x-show="error" x-cloak class="text-sm text-danger">More press releases could not be loaded. <a href="{{ $nextUrl }}" class="underline">Open the next page</a>.</p>
                            <template x-if="next">
                                <button type="button" @click="more()" :disabled="loading" class="nr-more-btn">
                                    <span x-text="loading ? 'Loading…' : 'Load More'">Load More</span>
                                </button>
                            </template>
                            <noscript>
                                @if ($releases->hasMorePages())<a href="{{ $nextUrl }}" class="nr-more-btn">Load More</a>@endif
                            </noscript>
                        </div>
                    </div>
                @endif
            </div>

            <aside class="nrx-side" aria-labelledby="latest-heading">
                <h2 id="latest-heading" class="nrx-side-title">Latest Releases</h2>
                @foreach ($latest as $item)
                    <a href="{{ route('newsroom.show', $item->slug) }}" class="nrx-side-item">
                        <span class="nrx-cat">{{ $item->category }}</span>
                        <span class="nrx-side-item-title">{{ $item->title }}</span>
                        <time datetime="{{ $item->published_at?->toDateString() }}">{{ $item->published_at?->format('d/m/Y') }}</time>
                    </a>
                @endforeach
                <a href="{{ route('newsroom.index') }}" class="nrx-link">View all releases <x-icon name="arrow-right" :size="14" :stroke="2.2" /></a>
            </aside>
        </div>
    </section>

    {{-- Packages section --}}
    <x-home.packages :packages="$packages" :brands="$brands" />
    <x-enquiry-modal :packages="$packages" />
</x-layouts.public>
