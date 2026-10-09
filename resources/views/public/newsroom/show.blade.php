@use('App\Support\SafeMarkdown')
@php
    $url = route('newsroom.show', $release->slug);
    $date = $release->published_at?->format('j F Y');
    $shareText = rawurlencode($release->title);
    $shareUrl = rawurlencode($url);
@endphp
<x-layouts.public :title="$release->title" :description="\Illuminate\Support\Str::limit($release->teaser, 155)" :canonical="$url" :og-image="$release->image_url">
    @push('schema')
        <script type="application/ld+json">{!! json_encode(array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $release->title,
            'datePublished' => $release->published_at?->toAtomString(),
            'dateModified' => $release->updated_at?->toAtomString(),
            'author' => ['@type' => 'Person', 'name' => $release->author_name],
            'publisher' => ['@type' => 'Organization', 'name' => $site->get('company_name'), 'url' => route('home')],
            'image' => $release->image_url,
            'articleSection' => $release->category,
            'mainEntityOfPage' => $url,
        ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endpush

    <section class="nra-wrap">
        <div class="container-site">
            <x-breadcrumb :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'Newsroom', 'url' => route('newsroom.index')], ['label' => $release->title]]" class="nra-crumb" />

            <div class="nra-grid">
                {{-- Main column --}}
                <article class="nra-article">
                    <a href="{{ route('newsroom.index', ['category' => $release->category]) }}" class="nra-cat">{{ $release->category }}</a>
                    <h1 class="nra-title">{{ $release->title }}</h1>
                    <p class="nra-meta">By {{ $release->author_name }}@if ($date) <span aria-hidden="true">·</span> <time datetime="{{ $release->published_at->toDateString() }}">{{ $date }}</time>@endif</p>

                    @if ($release->excerpt)
                        <p class="nra-standfirst">{{ $release->excerpt }}</p>
                    @endif

                    @if ($release->image_url)
                        <figure class="nra-figure">
                            <img src="{{ $release->image_url }}" alt="" width="1200" height="675" fetchpriority="high">
                        </figure>
                    @endif

                    <div class="nra-body">{{ SafeMarkdown::render($release->body) }}</div>

                    <footer class="nra-foot">
                        <a href="{{ route('newsroom.index') }}" class="nra-back"><x-icon name="arrow-right" :size="15" :stroke="2.2" class="rotate-180" /> Back to Newsroom</a>
                        <div class="nra-foot-actions">
                            <div class="nra-share" x-data="{ copied: false }">
                                <span>Share this article</span>
                                <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener" aria-label="Share on LinkedIn" title="LinkedIn">in</a>
                                <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareText }}" target="_blank" rel="noopener" aria-label="Share on X" title="X">𝕏</a>
                                <a href="mailto:?subject={{ $shareText }}&body={{ $shareUrl }}" aria-label="Share by email" title="Email"><x-icon name="mail" :size="14" :stroke="2" /></a>
                                <button type="button" @click="navigator.clipboard?.writeText(@js($url)).then(() => { copied = true; setTimeout(() => copied = false, 2000) })" :aria-label="copied ? 'Link copied' : 'Copy link'" :title="copied ? 'Copied' : 'Copy link'">
                                    <span x-show="!copied"><x-icon name="link" :size="14" :stroke="2" /></span>
                                    <span x-show="copied" x-cloak><x-icon name="check" :size="14" :stroke="2.4" /></span>
                                </button>
                            </div>
                            <a href="{{ route('contact') }}#enquire" x-data @click="if (document.getElementById('enquiry-modal-title')) { $event.preventDefault(); $dispatch('open-enquiry'); }" class="nra-cta">
                                Distribute your press release <x-icon name="arrow-right" :size="14" :stroke="2.4" />
                            </a>
                        </div>
                    </footer>
                </article>

                {{-- Supporting column --}}
                <aside class="nra-side">
                    <dl class="nra-info">
                        <div><dt>Category</dt><dd>{{ $release->category }}</dd></div>
                        @if ($date)<div><dt>Published</dt><dd>{{ $date }}</dd></div>@endif
                        @if ($release->author)<div><dt>Author</dt><dd>{{ $release->author }}</dd></div>@endif
                        @if ($readingMinutes)<div><dt>Est. reading time</dt><dd>{{ $readingMinutes }} min read</dd></div>@endif
                    </dl>

                    @if ($similar->isNotEmpty())
                        <section aria-labelledby="related-heading" class="nra-related">
                            <h2 id="related-heading" class="nra-side-heading">Related press releases</h2>
                            @foreach ($similar as $item)
                                <a href="{{ route('newsroom.show', $item->slug) }}" class="nra-related-row">
                                    <span class="nra-thumb">
                                        @if ($item->image_url)<img src="{{ $item->image_url }}" alt="" loading="lazy" width="160" height="110">@else<span class="nr-card-placeholder"><x-icon name="file" :size="18" :stroke="1.5" /></span>@endif
                                    </span>
                                    <span>
                                        <span class="nra-cat">{{ $item->category }}</span>
                                        <span class="nra-related-title">{{ $item->title }}</span>
                                        <time datetime="{{ $item->published_at?->toDateString() }}">{{ $item->published_at?->format('j F Y') }}</time>
                                    </span>
                                </a>
                            @endforeach
                        </section>
                    @endif
                </aside>
            </div>
        </div>
    </section>

    {{-- More from the newsroom --}}
    @if ($more->isNotEmpty())
        <section class="nra-more" aria-labelledby="more-heading">
            <div class="container-site">
                <div class="nra-more-head">
                    <div>
                        <span class="nra-cat">Explore more from our newsroom</span>
                        <h2 id="more-heading" class="nra-more-title">More from {{ $site->get('company_name') }}</h2>
                    </div>
                    <a href="{{ route('newsroom.index') }}" class="nra-back">View all newsroom articles <x-icon name="arrow-right" :size="15" :stroke="2.2" /></a>
                </div>
                <div class="nra-more-grid">
                    @foreach ($more as $item)
                        <a href="{{ route('newsroom.show', $item->slug) }}" class="nra-more-item">
                            <span class="nra-thumb">
                                @if ($item->image_url)<img src="{{ $item->image_url }}" alt="" loading="lazy" width="160" height="110">@else<span class="nr-card-placeholder"><x-icon name="file" :size="18" :stroke="1.5" /></span>@endif
                            </span>
                            <span>
                                <span class="nra-cat">{{ $item->category }}</span>
                                <span class="nra-related-title">{{ $item->title }}</span>
                                <time datetime="{{ $item->published_at?->toDateString() }}">{{ $item->published_at?->format('j F Y') }}</time>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Packages section --}}
    <x-home.packages :packages="$packages" :brands="$brands" />
    <x-enquiry-modal :packages="$packages" />
</x-layouts.public>
