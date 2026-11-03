@use('App\Support\SafeMarkdown')
@php
    $url = route('blogs.show', $post->slug);
    $date = $post->published_at?->format('j F Y');
    $shareText = rawurlencode($post->title);
    $shareUrl = rawurlencode($url);
@endphp
<x-layouts.public :title="$post->seo_title" :description="$post->seo_description" :canonical="$url" :og-image="$post->image_url">
    @push('schema')
        <script type="application/ld+json">{!! json_encode(array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->title,
            'description' => $post->seo_description,
            'datePublished' => $post->published_at?->toAtomString(),
            'dateModified' => $post->updated_at?->toAtomString(),
            'author' => ['@type' => 'Person', 'name' => $post->author_name],
            'publisher' => ['@type' => 'Organization', 'name' => $site->get('company_name'), 'url' => route('home')],
            'image' => $post->image_url,
            'articleSection' => $post->category,
            'mainEntityOfPage' => $url,
        ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endpush

    <section class="bl-article-wrap">
        <div class="container-site">
            <x-breadcrumb :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'Blogs', 'url' => route('blogs.index')], ['label' => $post->title]]" class="bl-crumb" />

            <div class="bl-article-grid">
                <article class="bl-article">
                    <a href="{{ route('blogs.index', ['category' => $post->category]) }}" class="bl-eyebrow">Blogs</a>
                    <h1 class="bl-article-title">{{ $post->title }}</h1>
                    @if ($post->excerpt)
                        <p class="bl-standfirst">{{ $post->excerpt }}</p>
                    @endif
                    <p class="bl-byline">
                        <span class="bl-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($post->author_name, 0, 1)) }}</span>
                        <span class="bl-byline-name">By {{ $post->author_name }}</span>
                        @if ($date)<span class="bl-dot" aria-hidden="true">·</span><time datetime="{{ $post->published_at->toDateString() }}">{{ $date }}</time>@endif
                        @if ($readingMinutes)<span class="bl-dot" aria-hidden="true">·</span><span>{{ $readingMinutes }} min read</span>@endif
                    </p>

                    @if ($post->image_url)
                        <figure class="bl-figure">
                            <img src="{{ $post->image_url }}" alt="{{ $post->title }}" width="1200" height="675" fetchpriority="high">
                        </figure>
                    @endif

                    <div class="bl-body">{{ $post->rendered_body }}</div>

                    <footer class="bl-article-foot">
                        <div class="bl-share">
                            <span>Share</span>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener" aria-label="Share on LinkedIn" title="LinkedIn"><svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor" aria-hidden="true"><path d="M6.94 8.5H3.56V20h3.38V8.5zM5.25 3a1.97 1.97 0 1 0 0 3.94 1.97 1.97 0 0 0 0-3.94zM20.44 13.03c0-3.28-1.75-4.8-4.09-4.8-1.89 0-2.73 1.04-3.2 1.77V8.5H9.78c.04.95 0 11.5 0 11.5h3.37v-6.42c0-.34.03-.69.13-.93.24-.6.8-1.23 1.73-1.23 1.22 0 1.71.93 1.71 2.3V20h3.37l.35-6.97z"/></svg></a>
                            <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareText }}" target="_blank" rel="noopener" aria-label="Share on X" title="X"><svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M17.75 3h3.07l-6.71 7.67L22 21h-6.18l-4.84-6.33L5.44 21H2.37l7.18-8.2L2 3h6.34l4.37 5.78L17.75 3zm-1.08 16.16h1.7L7.4 4.74H5.57l11.1 14.42z"/></svg></a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener" aria-label="Share on Facebook" title="Facebook"><svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor" aria-hidden="true"><path d="M13.5 21v-7.5h2.52l.38-2.93H13.5V8.7c0-.85.24-1.43 1.45-1.43h1.55V4.65c-.27-.04-1.19-.12-2.26-.12-2.23 0-3.76 1.36-3.76 3.87v2.17H7.96v2.93h2.52V21h3.02z"/></svg></a>
                            <a href="mailto:?subject={{ $shareText }}&body={{ $shareUrl }}" aria-label="Share by email" title="Email"><x-icon name="mail" :size="15" :stroke="2" /></a>
                        </div>
                        <a href="{{ route('blogs.index') }}" class="bl-link"><x-icon name="arrow-right" :size="15" :stroke="2.2" class="rotate-180" /> Back to Blogs</a>
                    </footer>

                    <div class="bl-cta">
                        <div>
                            <h2>Need help distributing your announcement?</h2>
                            <p>Tell us about your press release and our team will recommend the right package. No payment is required to enquire.</p>
                        </div>
                        <a href="{{ route('contact') }}#enquire" x-data @click="if (document.getElementById('enquiry-modal-title')) { $event.preventDefault(); $dispatch('open-enquiry'); }" class="bl-btn">Enquire now <x-icon name="arrow-right" :size="14" :stroke="2.4" /></a>
                    </div>

                    @if ($faqs !== [])
                        <section class="bl-faq" aria-labelledby="post-faq">
                            <span class="bl-eyebrow">FAQ</span>
                            <h2 id="post-faq" class="bl-faq-title">Frequently asked questions</h2>
                            <div class="pk-acc">
                                @foreach ($faqs as $i => $faq)
                                    <div class="pk-acc-item" x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }">
                                        <h3>
                                            <button type="button" class="pk-acc-btn bl-acc-btn" @click="open = !open" :aria-expanded="open.toString()" aria-controls="post-faq-{{ $i }}">
                                                <span>{{ $faq['question'] }}</span>
                                                <x-icon name="plus" :size="18" :stroke="2" class="pk-acc-icon" />
                                            </button>
                                        </h3>
                                        <div id="post-faq-{{ $i }}" x-show="open" x-collapse @unless ($loop->first) x-cloak @endunless>
                                            <div class="pk-acc-body">{!! nl2br(e($faq['answer'])) !!}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </article>

                <aside class="bl-side">
                    <section class="bl-details" aria-labelledby="article-details">
                        <h2 id="article-details">Article details</h2>
                        <dl>
                            <div><dt>Category</dt><dd><a href="{{ route('blogs.index', ['category' => $post->category]) }}">{{ $post->category }}</a></dd></div>
                            @if ($date)<div><dt>Published</dt><dd>{{ $date }}</dd></div>@endif
                            <div><dt>Author</dt><dd>{{ $post->author_name }}</dd></div>
                            @if ($readingMinutes)<div><dt>Estimated reading time</dt><dd>{{ $readingMinutes }} min read</dd></div>@endif
                        </dl>
                    </section>

                    @if ($related->isNotEmpty())
                        <section class="bl-side-block" aria-labelledby="related-posts">
                            <h2 id="related-posts" class="bl-side-title bl-side-title-lg">Related blog posts</h2>
                            @foreach ($related as $item)
                                <a href="{{ route('blogs.show', $item->slug) }}" class="bl-related">
                                    <span class="bl-thumb bl-thumb-wide">
                                        @if ($item->image_url)<img src="{{ $item->image_url }}" alt="" loading="lazy" width="160" height="110">@else<span class="bl-placeholder"><x-icon name="file" :size="16" :stroke="1.5" /></span>@endif
                                    </span>
                                    <span>
                                        <span class="bl-cat">{{ $item->category }}</span>
                                        <span class="bl-related-title">{{ $item->title }}</span>
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

    @if ($more->isNotEmpty())
        <section class="bl-more" aria-labelledby="more-posts">
            <div class="container-site">
                <div class="bl-more-head">
                    <h2 id="more-posts" class="bl-more-title">More from our blog</h2>
                    <a href="{{ route('blogs.index') }}" class="bl-link">View all blog posts <x-icon name="arrow-right" :size="14" :stroke="2.2" /></a>
                </div>
                <div class="bl-more-grid">
                    @foreach ($more as $item)
                        <a href="{{ route('blogs.show', $item->slug) }}" class="bl-related">
                            <span class="bl-thumb bl-thumb-wide">
                                @if ($item->image_url)<img src="{{ $item->image_url }}" alt="" loading="lazy" width="160" height="110">@else<span class="bl-placeholder"><x-icon name="file" :size="16" :stroke="1.5" /></span>@endif
                            </span>
                            <span>
                                <span class="bl-cat">{{ $item->category }}</span>
                                <span class="bl-related-title">{{ $item->title }}</span>
                                <time datetime="{{ $item->published_at?->toDateString() }}">{{ $item->published_at?->format('j F Y') }}</time>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-home.packages :packages="$packages" :brands="$brands" />
    <x-enquiry-modal :packages="$packages" />
</x-layouts.public>
