@use('App\Support\SafeMarkdown')
@php
    $report = $package->currentReport;
    $features = $package->features ?? [];
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

    {{-- Hero + pricing card --}}
    <section class="pt-7 pb-14 md:pt-12 md:pb-20 lg:pt-14 lg:pb-24">
        <div class="container-site flex flex-col gap-10 lg:grid lg:grid-cols-[minmax(0,1fr)_400px] lg:items-start lg:gap-20">
            <div class="flex flex-col gap-6">
                <x-breadcrumb :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'Packages', 'url' => route('packages.index')], ['label' => $package->name]]" />
                <p class="eyebrow">Press release distribution</p>
                <h1 class="display text-[34px] leading-[1.1] tracking-[-0.02em] md:text-[36px] lg:text-[44px]">{{ $package->name }}</h1>
                <p class="max-w-[680px] text-base leading-relaxed text-muted md:text-lg">{{ $package->short_description }}</p>
                <dl class="mt-2 grid grid-cols-2 border-t border-line md:grid-cols-4">
                    @foreach (array_filter([
                        'Headline media' => $package->featuredMedia->take(2)->pluck('name')->join(', ') ?: null,
                        'Featured outlets' => $package->featuredMedia->count() ?: null,
                        'Extended network' => $package->network_media_count ? $package->network_media_count.' outlets' : null,
                        'Reporting' => 'Distribution report',
                    ]) as $label => $value)
                        <div class="flex flex-col gap-1.5 pt-[18px] pr-3 max-md:[&:nth-child(even)]:border-l max-md:[&:nth-child(even)]:border-line max-md:[&:nth-child(even)]:pl-3 md:px-5 md:first:pl-0 md:[&+div]:border-l md:[&+div]:border-line">
                            <dt class="label-caps">{{ $label }}</dt>
                            <dd class="text-[15px] font-semibold">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            <aside x-data aria-label="Price" class="flex flex-col gap-[22px] rounded-card border border-accent bg-white p-6 shadow-[0_24px_48px_-36px_rgba(27,27,47,0.45)] md:p-8 lg:sticky lg:top-28 lg:mt-14">
                <div class="flex items-center justify-between gap-3">
                    <span class="label-caps">Price per press release</span>
                    @if ($package->is_highlighted)<span class="badge bg-accent-soft text-accent-ink">Most requested</span>@endif
                </div>
                <x-price :package="$package" size="lg" class="border-b border-line-soft pb-5" />
                @if ($features)
                    <ul class="flex flex-col gap-3">
                        @foreach (array_slice($features, 0, 5) as $feature)
                            <li class="flex items-center gap-2.5 text-[15px]"><x-icon name="check" :size="16" :stroke="2.2" class="text-accent" />{{ $feature }}</li>
                        @endforeach
                    </ul>
                @endif
                <div class="flex flex-col gap-2.5">
                    <x-button href="#enquire" @click.prevent="$dispatch('open-enquiry', {{ $package->id }})" aria-haspopup="dialog" icon="arrow-right" class="w-full" data-track="cta_click" data-track-label="Package: Enquire Now" data-track-package="{{ $package->name }}">Enquire Now</x-button>
                    @if ($report)
                        <x-button :href="route('reports.view', $package->slug)" target="_blank" @click.prevent="$dispatch('open-report', {{ Js::from(['name' => $package->name, 'view' => route('reports.view', $package->slug), 'download' => route('reports.download', $package->slug)]) }})" aria-haspopup="dialog" variant="secondary" icon-left="file" class="w-full" data-track="sample_report_click" data-track-package="{{ $package->name }}">View Sample Report</x-button>
                        <span class="text-center font-mono text-xs text-muted">PDF · {{ $report->formatted_size }}</span>
                    @endif
                </div>
                <p class="flex items-center gap-2 text-[13px] text-muted"><x-icon name="lock" :size="15" class="text-success" />No payment required to enquire.</p>
            </aside>
        </div>
    </section>

    {{-- What's included --}}
    <section class="border-t border-line section-y">
        <div class="container-site flex flex-col gap-10 md:gap-14">
            <x-section-heading eyebrow="What's included">Everything in this package.</x-section-heading>
            <div class="grid gap-10 md:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)] md:gap-16">
                @if ($package->full_content)
                    <div class="prose-vmn text-muted [&_p]:text-[17px]">{{ SafeMarkdown::render($package->full_content) }}</div>
                @endif
                @if ($features)
                    <ol data-reveal-group class="grid gap-x-8 gap-y-6 sm:grid-cols-2 {{ $package->full_content ? '' : 'md:col-span-2 lg:grid-cols-3' }}">
                        @foreach ($features as $feature)
                            <li data-reveal class="flex flex-col gap-2 border-t border-ink pt-5">
                                <span class="font-mono text-xs text-accent-ink">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="font-display text-[24px] leading-tight font-semibold">{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </div>
    </section>

    {{-- Featured media --}}
    @if ($package->featuredMedia->isNotEmpty())
        <section class="border-t border-line bg-white section-y">
            <div class="container-site flex flex-col gap-8 md:gap-12">
                <x-section-heading eyebrow="Featured media platforms" description="The platforms below feature prominently in this package.">Headline names, included.</x-section-heading>
                <ul class="grid grid-cols-2 border-t border-l border-line md:grid-cols-4" data-reveal-group>
                    @foreach ($package->featuredMedia as $outlet)
                        <li data-reveal><x-media-logo :outlet="$outlet" /></li>
                    @endforeach
                    @if ($package->network_media_count)
                        <li class="flex h-24 flex-col items-center justify-center gap-0.5 border-r border-b border-line bg-brand text-canvas md:h-32">
                            <span class="font-display text-[34px] leading-none font-semibold md:text-[36px]">{{ $site->get('network_size_label') }}</span>
                            <span class="text-xs text-muted-on-brand">network outlets</span>
                        </li>
                    @endif
                </ul>
            </div>
        </section>
    @endif

    {{-- Extended network: loaded on demand, searchable, paginated --}}
    @if ($package->network_media_count)
        <section class="border-t border-line section-y">
            <div class="container-site">
                <div x-data="outletNetwork(@js(route('packages.network', $package->slug)))" class="flex flex-col gap-7 rounded-card border border-line bg-white p-6 md:p-10">
                    <div class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between md:gap-8">
                        <div class="flex max-w-[640px] flex-col gap-3">
                            <p class="eyebrow">Extended network</p>
                            <h2 class="display text-[30px] leading-[1.12] md:text-[38px]">{{ $site->get('network_size_label') }} additional outlets</h2>
                            <p class="text-base leading-relaxed text-muted">Your release can also be distributed across our extended network of digital publications. This package includes {{ number_format($package->network_media_count) }} listed outlets.</p>
                        </div>
                        <button type="button" @click="toggle()" :aria-expanded="open.toString()" aria-controls="network-list" class="btn btn-secondary shrink-0">
                            <span x-text="open ? 'Hide distribution network' : 'View full distribution network'">View full distribution network</span>
                            <x-icon name="chevron-down" class="transition-transform duration-300" x-bind:class="open && 'rotate-180'" />
                        </button>
                    </div>
                    <div id="network-list" x-show="open" x-collapse x-cloak>
                        <div class="flex flex-col gap-6 border-t border-line pt-7">
                            <label class="flex h-11 items-center gap-2.5 rounded-[6px] border border-line px-3.5 text-muted focus-within:border-accent">
                                <x-icon name="search" :size="17" />
                                <span class="sr-only">Search outlets</span>
                                <input type="search" x-model.debounce.350ms="search" @input.debounce.350ms="fetch(1)" placeholder="Search outlets" class="h-10 grow bg-transparent text-[15px] text-ink outline-none">
                            </label>
                            <p x-show="error" class="text-sm text-danger">The outlet list could not be loaded. Please try again.</p>
                            {{-- Skeleton rows while the first page loads --}}
                            <ul x-show="loading && outlets.length === 0" class="grid gap-x-6 sm:grid-cols-2 lg:grid-cols-3" aria-hidden="true">
                                @foreach (range(1, 9) as $i)
                                    <li class="flex items-center justify-between gap-3 border-b border-line-soft py-3">
                                        <span class="skeleton h-3.5" style="width: {{ [62, 48, 70, 55, 66, 44, 58, 72, 50][$i - 1] }}%"></span>
                                        <span class="skeleton h-2.5 w-14"></span>
                                    </li>
                                @endforeach
                            </ul>
                            <ul class="grid gap-x-6 sm:grid-cols-2 lg:grid-cols-3" aria-live="polite" :aria-busy="loading.toString()">
                                <template x-for="outlet in outlets" :key="outlet.name">
                                    <li class="flex items-center justify-between gap-3 border-b border-line-soft py-2 text-[15px]">
                                        <span x-text="outlet.name"></span>
                                        <span class="text-[11px] font-bold tracking-[0.12em] text-muted uppercase" x-text="outlet.category"></span>
                                    </li>
                                </template>
                            </ul>
                            <p x-show="loaded && outlets.length === 0" class="text-sm text-muted">No outlets match your search.</p>
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-[13px] text-muted" x-show="loaded" x-text="`Showing ${outlets.length} of ${total}`"></span>
                                <button type="button" x-show="page < lastPage" @click="more()" :disabled="loading" class="btn btn-secondary btn-sm">
                                    <span x-text="loading ? 'Loading…' : 'Load more'">Load more</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- How distribution works --}}
    <section class="border-t border-line bg-white section-y">
        <div class="container-site flex flex-col gap-10 md:gap-16">
            <x-section-heading eyebrow="How distribution works">Four steps, fully handled.</x-section-heading>
            <x-steps />
        </div>
    </section>

    {{-- Sample report --}}
    <section class="border-t border-line section-y">
        <div class="container-site flex flex-col-reverse gap-12 lg:grid lg:grid-cols-[auto_minmax(0,1fr)] lg:items-center lg:gap-24">
            <x-report-document :on-dark="false" :title="$package->name" :outlets="$package->featuredMedia->pluck('name')" />
            <div class="flex max-w-[560px] flex-col gap-5">
                <p class="eyebrow">Sample report</p>
                <h2 class="display text-4xl leading-[1.12] md:text-[36px] lg:text-[42px]">Preview the report before you enquire.</h2>
                <ul class="flex flex-col gap-3">
                    @foreach (['Every outlet where the release appeared', 'Live links to each publication', 'The extended network listing', 'Distribution date and package details'] as $item)
                        <li class="flex items-center gap-2.5 text-base"><x-icon name="check" :size="16" :stroke="2.2" class="text-accent" />{{ $item }}</li>
                    @endforeach
                </ul>
                <div class="pt-3">
                    @if ($report)
                        <span x-data class="contents"><x-button :href="route('reports.view', $package->slug)" target="_blank" @click.prevent="$dispatch('open-report', {{ Js::from(['name' => $package->name, 'view' => route('reports.view', $package->slug), 'download' => route('reports.download', $package->slug)]) }})" aria-haspopup="dialog" variant="secondary" icon-left="file" class="max-sm:w-full" data-track="sample_report_click" data-track-package="{{ $package->name }}">View Sample Report</x-button></span>
                    @else
                        <p class="text-[15px] text-muted">The sample report for this package is being prepared. <a href="#enquire" class="text-accent-ink underline">Ask us for a copy.</a></p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    @if ($faqs->isNotEmpty())
        <section class="border-t border-line section-y">
            <div class="container-site flex flex-col gap-9 lg:grid lg:grid-cols-[400px_minmax(0,1fr)] lg:items-start lg:gap-[120px]">
                <div class="flex flex-col gap-5">
                    <x-section-heading eyebrow="FAQ">Frequently asked questions</x-section-heading>
                    <a href="{{ route('faq') }}" class="link-arrow">All FAQs <x-icon name="arrow-right" :size="16" /></a>
                </div>
                <div class="border-b border-line">
                    @foreach ($faqs as $faq)
                        <x-faq-item :question="$faq->question" :open="$loop->first">{{ $faq->answer }}</x-faq-item>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-enquiry-section :packages="$packages" :selected="$packages->firstWhere('id', $package->id)" locked
                       heading="Ready to distribute your story?" />
    <x-enquiry-modal :packages="$packages" :selected="$packages->firstWhere('id', $package->id)" />
    <x-report-modal />
</x-layouts.public>
