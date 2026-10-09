@use('App\Support\SafeMarkdown')
@php
    $features = $package->features ?? [];
    $checklist = $features ?: [
        'Major media distribution',
        'Digital publication network',
        'Professional distribution handling',
        'Distribution reporting',
        'Release coordination',
        'Post-distribution support',
    ];
    $selected = $packages->firstWhere('id', $package->id);

    $featured = $package->featuredMedia;
    $headline = $featured->first()?->name ?? $package->name;
    $additionalNames = $featured->skip(1)->pluck('name')->values();
    $additional = match (true) {
        $additionalNames->isEmpty() => 'Digital publication network',
        $additionalNames->count() <= 2 => $additionalNames->join(' + '),
        default => $additionalNames->take(2)->join(' + ').' + '.($additionalNames->count() - 2).' more',
    };
    $multiNetwork = $featured->count() > 1;
    $packageType = $multiNetwork ? 'Multi-network' : 'Single network';
    $summary = trim((string) $package->distribution_summary) ?: 'the media included in this package';
    $valuePoints = $featured->take(3)->pluck('name')->push('Distribution reporting');
@endphp
<x-layouts.public :title="$package->seo_title" :description="$package->seo_description" :canonical="route('packages.show', $package->slug)"
                  :event="['name' => 'package_view', 'package_name' => $package->name]">
    @push('schema')
        <script type="application/ld+json">{!! json_encode(array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $package->name,
            'serviceType' => 'Press release distribution',
            'description' => $package->short_description,
            'url' => route('packages.show', $package->slug),
            'provider' => ['@type' => 'Organization', 'name' => $site->get('company_name'), 'url' => route('home')],
            'offers' => $package->price !== null ? [
                '@type' => 'Offer',
                'price' => (string) $package->price,
                'priceCurrency' => $package->currency,
                'url' => route('packages.show', $package->slug),
            ] : null,
        ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endpush

    {{-- 01 Editorial hero: typography left, one photograph + flat specification panel right --}}
    <section class="pk-hero">
        <div class="container-site">
            <x-breadcrumb :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'Packages', 'url' => route('packages.index')], ['label' => $package->name]]" />
            <div class="pk-hero-grid">
                <div>
                    <span class="pk-eyebrow">Press release distribution</span>
                    <h1 class="pk-h1" style="margin-top:20px">{{ $package->name }}</h1>
                    <p class="pk-hero-sub">{{ $package->short_description }}</p>
                    <p class="pk-hero-text">
                        Distribution covers {{ $summary }}. Your announcement is prepared by our team, issued across the included media, and followed by a distribution report you can review and share.
                    </p>
                    <p class="pk-hero-price">
                        <span class="pk-price">{{ $package->formatted_price ?? 'On request' }}</span>
                        @if ($package->formatted_price)<span class="pk-price-unit">/ PR</span>@endif
                    </p>
                    <div class="pk-btn-row pk-hero-actions">
                        <button type="button" x-data @click="$dispatch('open-enquiry', {{ $package->id }})" aria-haspopup="dialog"
                                class="btn-pill btn-teal" data-track="cta_click" data-track-label="Package: Enquire About This Package" data-track-package="{{ $package->name }}">
                            Enquire About This Package
                            <span class="btn-pill-icon bg-white text-[#0e8c7e]"><x-icon name="arrow-right" :size="15" :stroke="2.4" /></span>
                        </button>
                        <a href="{{ route('packages.index') }}" class="btn-pill btn-ghost">
                            Compare Packages
                            <span class="btn-pill-icon"><x-icon name="arrow-right" :size="15" :stroke="2.4" /></span>
                        </a>
                    </div>
                </div>

                <div>
                    <img src="{{ asset('images/media-network-hero.webp') }}" alt="Newsroom team monitoring media coverage on large screens" width="960" height="715" fetchpriority="high" class="pk-photo pk-hero-photo">
                    <dl class="pk-hero-panel">
                        <div><dt class="pk-panel-label">Headline media</dt><dd class="pk-panel-value">{{ $headline }}</dd></div>
                        <div><dt class="pk-panel-label">Additional distribution</dt><dd class="pk-panel-value">{{ $additional }}</dd></div>
                        <div><dt class="pk-panel-label">Reporting</dt><dd class="pk-panel-value">Distribution report</dd></div>
                    </dl>
                </div>
            </div>
        </div>
    </section>

    {{-- 02 Specification bar: four columns, thin rules, no cards or icons --}}
    <section class="pk-spec pk-gray" aria-label="Package specification">
        <div class="container-site">
            <dl class="pk-spec-grid">
                <div><dt class="pk-panel-label">Headline media</dt><dd class="pk-spec-value">{{ $headline }}</dd></div>
                <div><dt class="pk-panel-label">Additional media</dt><dd class="pk-spec-value">{{ $additional }}</dd></div>
                <div><dt class="pk-panel-label">Reporting</dt><dd class="pk-spec-value">Distribution report</dd></div>
                <div><dt class="pk-panel-label">Package type</dt><dd class="pk-spec-value">{{ $packageType }}</dd></div>
            </dl>
        </div>
    </section>

    {{-- 03 Editorial introduction: asymmetric text layout, included items as plain rows --}}
    <section class="pk-section bg-white">
        <div class="container-site pk-split pk-split-40">
            <div>
                <span class="pk-eyebrow">Package overview</span>
                <h2 class="pk-h2-big">One package.<br>{{ $multiNetwork ? 'Multiple media networks.' : 'A complete distribution workflow.' }}</h2>
            </div>
            <div>
                <div class="pk-body">
                    @if ($package->full_content)
                        <div class="prose-vmn">{{ SafeMarkdown::render($package->full_content) }}</div>
                    @else
                        <p>{{ $package->name }} is designed for businesses that want professional press release distribution with a clear, managed process from submission through reporting.</p>
                        <p>Your announcement is prepared for distribution across the media included with this package. The focus is on getting your news in front of relevant audiences while keeping the process simple and transparent.</p>
                    @endif
                </div>
                <span class="pk-eyebrow" style="margin-top:40px">Included</span>
                <ul class="pk-rows">
                    @foreach ($checklist as $item)
                        <li><x-icon name="check" :size="17" :stroke="2.4" class="pk-check" />{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    {{-- 04 Media directory: publication / type / status rows, extended network loads into the same table --}}
    <section class="pk-section bg-white" style="padding-top:0">
        <div class="container-site" @if ($package->network_media_count) x-data="outletNetwork(@js(route('packages.network', $package->slug)))" @endif>
            <span class="pk-eyebrow">Media network</span>
            <h2 class="pk-h2">Where your story can appear.</h2>
            <table class="pk-dir">
                <thead>
                    <tr>
                        <th scope="col">Publication</th>
                        <th scope="col" class="pk-dir-col-type">Type</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($featured as $outlet)
                        <tr>
                            <td><span class="pk-dir-name">{{ $outlet->name }}</span><span class="pk-dir-type-m">{{ $outlet->category }}</span></td>
                            <td class="pk-dir-col-type"><span class="pk-dir-type">{{ $outlet->category }}</span></td>
                            <td><span class="pk-status">Included</span></td>
                        </tr>
                    @endforeach
                    @if ($featured->isEmpty() && ! $package->network_media_count)
                        <tr>
                            <td><span class="pk-dir-name">{{ $package->name }} network</span><span class="pk-dir-type-m">Digital media</span></td>
                            <td class="pk-dir-col-type"><span class="pk-dir-type">Digital media</span></td>
                            <td><span class="pk-status">Included</span></td>
                        </tr>
                    @endif
                    @if ($package->network_media_count)
                        <template x-for="outlet in outlets" :key="outlet.name">
                            <tr>
                                <td><span class="pk-dir-name" x-text="outlet.name"></span><span class="pk-dir-type-m" x-text="outlet.category"></span></td>
                                <td class="pk-dir-col-type"><span class="pk-dir-type" x-text="outlet.category"></span></td>
                                <td><span class="pk-status">Included</span></td>
                            </tr>
                        </template>
                    @endif
                </tbody>
            </table>

            @if ($package->network_media_count)
                <div class="pk-dir-tools">
                    <div id="network-list" x-show="open" x-collapse x-cloak>
                        <label class="pk-dir-search">
                            <x-icon name="search" :size="17" />
                            <span class="sr-only">Search outlets</span>
                            <input type="search" x-model.debounce.350ms="search" @input.debounce.350ms="fetch(1)" placeholder="Search the extended network">
                        </label>
                        <p x-show="error" class="text-sm text-danger" style="margin-top:12px">The outlet list could not be loaded. Please try again.</p>
                        <p x-show="loaded && outlets.length === 0" class="text-sm text-muted" style="margin-top:12px">No outlets match your search.</p>
                    </div>
                    <div class="pk-dir-foot">
                        <span x-show="loaded" x-text="`Showing ${outlets.length} of ${total} extended-network outlets`"></span>
                        <span x-show="! loaded">{{ number_format($package->network_media_count) }} additional outlets in the extended network</span>
                        <span class="pk-btn-row">
                            <button type="button" x-show="open && page < lastPage" x-cloak @click="more()" :disabled="loading" class="pk-link-btn">
                                <span x-text="loading ? 'Loading…' : 'Load more'">Load more</span>
                            </button>
                            <button type="button" @click="toggle()" :aria-expanded="open.toString()" aria-controls="network-list" class="pk-link-btn">
                                <span x-text="open ? 'Hide distribution network' : 'View full distribution network'">View full distribution network</span>
                                <x-icon name="chevron-down" :size="16" class="transition-transform duration-300" x-bind:class="open && 'rotate-180'" />
                            </button>
                        </span>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- 05 Photo + text story --}}
    <section class="pk-section pk-gray">
        <div class="container-site pk-split pk-split-55">
            <img src="{{ asset('images/package-story.webp') }}" alt="Communications professional reviewing a press release in a corporate office" width="1200" height="896" loading="lazy" class="pk-photo pk-story-photo">
            <div>
                <span class="pk-eyebrow">Built for important announcements</span>
                <h2 class="pk-h2">Put your announcement in front of the right audiences.</h2>
                <div class="pk-points">
                    <div class="pk-point"><h3>Broader distribution</h3><p>Multiple distribution channels through one package.</p></div>
                    <div class="pk-point"><h3>Recognizable media destinations</h3><p>Reach audiences through established business and news platforms.</p></div>
                    <div class="pk-point"><h3>Professional reporting</h3><p>Review distribution after publication.</p></div>
                </div>
            </div>
        </div>
    </section>

    {{-- 06 Process timeline: dark navy --}}
    <section class="pk-section pk-dark">
        <div class="container-site">
            <span class="pk-eyebrow">How distribution works</span>
            <h2 class="pk-h2">Four steps.<br>Fully handled.</h2>
            <ol class="pk-timeline">
                @foreach ([
                    ['Choose your package', 'Compare packages and pick the distribution network that fits your announcement.'],
                    ['Submit your release', 'Send an enquiry. Our team confirms the details and receives your release.'],
                    ['Distribution', 'Your release goes out across the platforms and outlets included in your package.'],
                    ['Receive your report', 'A distribution report shows where your release was published, with available live links.'],
                ] as $i => [$title, $text])
                    <li class="pk-step">
                        <span class="pk-step-num">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <h3>{{ $title }}</h3>
                        <p>{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- 07 Why VM Newswire: editorial statement + one row split by vertical rules --}}
    <section class="pk-section bg-white">
        <div class="container-site">
            <span class="pk-eyebrow">Why VM Newswire</span>
            <h2 class="pk-statement">A simpler way to distribute important news.</h2>
            <div class="pk-why-grid">
                @foreach ([
                    ['Professional coordination', 'Your announcement is prepared and distributed through a structured, managed workflow.'],
                    ['Clear communication', 'Know what happens next, from package selection to release submission and reporting.'],
                    ['Distribution reporting', 'Receive a report after publication so you can review where your announcement appeared.'],
                ] as $i => [$title, $text])
                    <div class="pk-why-item">
                        <span class="pk-why-num">0{{ $i + 1 }}</span>
                        <h3>{{ $title }}</h3>
                        <p>{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 08 Package value: compact, light grey, no pricing card --}}
    <section class="pk-section-sm pk-gray">
        <div class="container-site pk-split pk-split-50">
            <div>
                <span class="pk-eyebrow">Package value</span>
                <h2 class="pk-h2">Why choose this package?</h2>
                <p class="pk-body" style="margin-top:20px;max-width:520px">
                    {{ $package->name }} brings {{ $summary }} into one managed service, with no online payment required to enquire. Tell us about your announcement and our team confirms the details before anything goes out.
                </p>
            </div>
            <div>
                <p class="pk-value-price">{{ $package->formatted_price ?? 'On request' }}</p>
                <span class="pk-value-unit">{{ $package->formatted_price ? 'Per press release' : 'Pricing confirmed on enquiry' }}</span>
                <ul class="pk-value-list">
                    @foreach ($valuePoints as $point)
                        <li><x-icon name="check" :size="16" :stroke="2.4" class="pk-check" style="margin-top:0" />{{ $point }}</li>
                    @endforeach
                </ul>
                <div class="pk-btn-row" style="margin-top:28px">
                    <button type="button" x-data @click="$dispatch('open-enquiry', {{ $package->id }})" aria-haspopup="dialog" class="btn-pill btn-teal" data-track="cta_click" data-track-label="Package value: Start Your Enquiry" data-track-package="{{ $package->name }}">
                        Start Your Enquiry
                        <span class="btn-pill-icon bg-white text-[#0e8c7e]"><x-icon name="arrow-right" :size="15" :stroke="2.4" /></span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- 09 FAQ: heading left, plain accordion rows right --}}
    @if ($faqs->isNotEmpty())
        <section class="pk-section bg-white">
            <div class="container-site pk-split pk-split-35">
                <div>
                    <span class="pk-eyebrow">FAQ</span>
                    <h2 class="pk-faq-title">Frequently<br>asked<br>questions.</h2>
                    <a href="{{ route('faq') }}" class="link-arrow" style="margin-top:24px">All FAQs <x-icon name="arrow-right" :size="16" /></a>
                </div>
                <div class="pk-acc">
                    @foreach ($faqs as $faq)
                        <div class="pk-acc-item" x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }">
                            <h3>
                                <button type="button" class="pk-acc-btn" @click="open = !open" :aria-expanded="open.toString()" aria-controls="faq-{{ $faq->id }}">
                                    <span>{{ $faq->question }}</span>
                                    <x-icon name="plus" :size="18" :stroke="2" class="pk-acc-icon" />
                                </button>
                            </h3>
                            <div id="faq-{{ $faq->id }}" x-show="open" x-collapse @unless ($loop->first) x-cloak @endunless>
                                <div class="pk-acc-body">{{ $faq->answer }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- 10 Final conversion section: one navy block with the enquiry form --}}
    <x-enquiry-section :packages="$packages" :selected="$selected" locked
                       :eyebrow="$package->name"
                       heading="Ready to distribute your story?"
                       text="Tell us about your announcement and our team will confirm the details, timing and distribution for this package."
                       :points="['No payment required to enquire', 'Package recommendations available', 'Professional distribution support']" />
    <x-enquiry-modal :packages="$packages" :selected="$selected" />
</x-layouts.public>
