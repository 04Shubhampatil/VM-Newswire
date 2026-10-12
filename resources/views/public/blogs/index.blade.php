@php
    $activeCategory = $filters['category'] ?? null;
    $query = $filters['q'] ?? null;
    $isFiltered = $activeCategory || $query;
@endphp
<x-layouts.public title="Blog" description="Insights on press release writing, media relations, PR strategy and distribution reporting from the VM Newswire team." :canonical="route('blogs.index')">
    {{-- Hero: intro left, featured article right --}}
    <section class="bl-hero">
        <div class="container-site bl-hero-grid">
            <div>
                <span class="bl-eyebrow">Blogs</span>
                <h1 class="bl-h1">Insights on PR, media and communication</h1>
                <p class="bl-lead">Practical guidance on writing press releases, working with journalists, planning distribution and reading the results, written by the {{ $site->get('company_name') }} team.</p>
            </div>
            @if ($featured)
                <article class="bl-featured">
                    <a href="{{ route('blogs.show', $featured->slug) }}" class="bl-featured-media" tabindex="-1" aria-hidden="true">
                        @if ($featured->image_url)
                            <img src="{{ $featured->image_url }}" alt="" width="1200" height="750" fetchpriority="high">
                        @else
                            <span class="bl-placeholder"><x-icon name="file" :size="30" :stroke="1.5" /></span>
                        @endif
                    </a>
                    <div class="bl-featured-body">
                        <p class="bl-meta"><span class="bl-cat">{{ $featured->category }}</span><time datetime="{{ $featured->published_at?->toDateString() }}">{{ $featured->published_at?->format('j F Y') }}</time></p>
                        <h2 class="bl-featured-title"><a href="{{ route('blogs.show', $featured->slug) }}">{{ $featured->title }}</a></h2>
                        <a href="{{ route('blogs.show', $featured->slug) }}" class="bl-link">Read article <x-icon name="arrow-right" :size="14" :stroke="2.2" /></a>
                    </div>
                </article>
            @else
                <img src="{{ asset('images/package-story.webp') }}" alt="Communications professional writing at a desk" width="1200" height="896" fetchpriority="high" class="bl-hero-photo">
            @endif
        </div>
    </section>

    <section class="bl-main">
        <div class="container-site">
            {{-- Category tabs --}}
            <nav class="bl-tabs" aria-label="Filter by category">
                <a href="{{ route('blogs.index', array_filter(['q' => $query])) }}" class="bl-tab" @if (! $activeCategory) aria-current="true" @endif>All articles</a>
                @foreach ($categories as $category => $count)
                    <a href="{{ route('blogs.index', array_filter(['category' => $category, 'q' => $query])) }}" class="bl-tab" @if ($activeCategory === $category) aria-current="true" @endif>{{ $category }}</a>
                @endforeach
            </nav>

            <div class="bl-layout">
                <div class="bl-content">
                    @if ($isFiltered)
                        <p class="bl-result">
                            {{ $posts->total() }} {{ Str::plural('article', $posts->total()) }}@if ($activeCategory) in <strong>{{ $activeCategory }}</strong>@endif @if ($query) matching “<strong>{{ $query }}</strong>”@endif
                            · <a href="{{ route('blogs.index') }}" class="bl-link">Clear</a>
                        </p>
                    @endif

                    @if ($posts->isEmpty())
                        <div class="bl-empty">
                            <h2>No articles found</h2>
                            <p>Try another search term or category.</p>
                            <a href="{{ route('blogs.index') }}" class="bl-link">Show all articles <x-icon name="arrow-right" :size="14" :stroke="2.2" /></a>
                        </div>
                    @else
                        <div class="bl-grid">
                            @foreach ($posts as $post)
                                @php $url = route('blogs.show', $post->slug); @endphp
                                <article class="bl-card">
                                    <a href="{{ $url }}" class="bl-card-media" tabindex="-1" aria-hidden="true">
                                        @if ($post->image_url)
                                            <img src="{{ $post->image_url }}" alt="" loading="lazy" width="640" height="400">
                                        @else
                                            <span class="bl-placeholder"><x-icon name="file" :size="26" :stroke="1.5" /></span>
                                        @endif
                                    </a>
                                    <p class="bl-meta"><span class="bl-cat">{{ $post->category }}</span><time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->format('j F Y') }}</time></p>
                                    <h2 class="bl-card-title"><a href="{{ $url }}">{{ $post->title }}</a></h2>
                                    <p class="bl-card-text">{{ $post->teaser }}</p>
                                    <p class="bl-card-foot"><span class="bl-author">By {{ $post->author_name }}</span><a href="{{ $url }}" class="bl-link">Read article <x-icon name="arrow-right" :size="14" :stroke="2.2" /></a></p>
                                </article>
                            @endforeach
                        </div>

                        @if ($posts->hasPages())
                            <nav class="bl-pager" aria-label="Pagination">
                                @if ($posts->onFirstPage())
                                    <span class="bl-page is-disabled" aria-hidden="true"><x-icon name="chevron-left" :size="16" :stroke="2" /></span>
                                @else
                                    <a href="{{ $posts->previousPageUrl() }}" rel="prev" class="bl-page" aria-label="Previous page"><x-icon name="chevron-left" :size="16" :stroke="2" /></a>
                                @endif
                                @foreach ($posts->getUrlRange(1, $posts->lastPage()) as $page => $url)
                                    @if ($page === $posts->currentPage())
                                        <span class="bl-page is-current" aria-current="page">{{ $page }}</span>
                                    @else
                                        <a href="{{ $url }}" class="bl-page">{{ $page }}</a>
                                    @endif
                                @endforeach
                                @if ($posts->hasMorePages())
                                    <a href="{{ $posts->nextPageUrl() }}" rel="next" class="bl-page" aria-label="Next page"><x-icon name="chevron-right" :size="16" :stroke="2" /></a>
                                @else
                                    <span class="bl-page is-disabled" aria-hidden="true"><x-icon name="chevron-right" :size="16" :stroke="2" /></span>
                                @endif
                            </nav>
                        @endif
                    @endif
                </div>

                <aside class="bl-side">
                    <form method="GET" action="{{ route('blogs.index') }}" role="search" class="bl-search">
                        @if ($activeCategory)<input type="hidden" name="category" value="{{ $activeCategory }}">@endif
                        <label for="blog-search" class="sr-only">Search blog posts</label>
                        <input id="blog-search" type="search" name="q" value="{{ $query }}" maxlength="100" placeholder="Search blog posts...">
                        <button type="submit" aria-label="Search"><x-icon name="search" :size="16" :stroke="2.4" /></button>
                    </form>

                    @if ($categories)
                        <section class="bl-side-block" aria-labelledby="blog-categories">
                            <h2 id="blog-categories" class="bl-side-title">Categories</h2>
                            <ul class="bl-cats">
                                @foreach ($categories as $category => $count)
                                    <li><a href="{{ route('blogs.index', ['category' => $category]) }}" @if ($activeCategory === $category) aria-current="true" @endif><span>{{ $category }}</span><span class="bl-count">{{ $count }}</span></a></li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    @if ($recent->isNotEmpty())
                        <section class="bl-side-block" aria-labelledby="blog-recent">
                            <h2 id="blog-recent" class="bl-side-title">Recent posts</h2>
                            @foreach ($recent as $item)
                                <a href="{{ route('blogs.show', $item->slug) }}" class="bl-recent">
                                    <span class="bl-thumb">
                                        @if ($item->image_url)<img src="{{ $item->image_url }}" alt="" loading="lazy" width="160" height="160">@else<span class="bl-placeholder"><x-icon name="file" :size="16" :stroke="1.5" /></span>@endif
                                    </span>
                                    <span>
                                        <span class="bl-recent-title">{{ $item->title }}</span>
                                        <time datetime="{{ $item->published_at?->toDateString() }}">{{ $item->published_at?->format('j F Y') }}</time>
                                    </span>
                                </a>
                            @endforeach
                        </section>
                    @endif

                    <section class="bl-help">
                        <h2>Need help with your press release?</h2>
                        <p>Our team is here to guide you through the distribution process and help you get the best results.</p>
                        <a href="{{ route('contact') }}#enquire" x-data @click="if (document.getElementById('enquiry-modal-title')) { $event.preventDefault(); $dispatch('open-enquiry'); }" class="bl-link">Contact us <x-icon name="arrow-right" :size="14" :stroke="2.2" /></a>
                    </section>
                </aside>
            </div>
        </div>
    </section>

    <x-home.packages :packages="$packages" :brands="$brands" />
    <x-enquiry-modal :packages="$packages" />
</x-layouts.public>
