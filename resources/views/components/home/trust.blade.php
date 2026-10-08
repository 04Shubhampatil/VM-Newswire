@props(['outlets', 'summary'])
{{--
    Confidence band. Only figures the project actually holds: the CMS network-size label, the live category
    count and the existence of sample distribution reports. No invented country/industry/percentage stats.
--}}
@php
    $network = (string) $site->get('network_size_label');
    $categories = collect($summary)->count();
@endphp
<section class="band-navy py-16 lg:py-24">
    <div class="container-site">
        <div class="grid gap-10 lg:grid-cols-12 lg:gap-14">
            <div class="lg:col-span-5" data-reveal-group>
                <p class="eyebrow text-accent-on-brand">Confidence</p>
                <h2 class="headline mt-4 text-[30px] leading-[1.15] text-white sm:text-[38px]">Confidence in every distribution</h2>
                <p class="mt-5 max-w-md text-[16px] leading-[1.75] text-muted-on-brand">
                    A press release campaign should be measurable. See the network, the categories and the report you receive once your release goes out.
                </p>
                <div class="mt-7 flex flex-wrap gap-3" data-reveal>
                    <a href="{{ route('sample-reports.index') }}" class="btn btn-on-navy">See sample reports</a>
                    <a href="{{ route('media-network') }}" class="btn btn-outline">View the media network</a>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3 lg:col-span-7" data-reveal-group>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6" data-reveal>
                    <p class="text-[36px] leading-none font-bold text-white">{{ $network }}</p>
                    <p class="mt-3 text-[15px] font-semibold text-white">Media outlets</p>
                    <p class="mt-2 text-[14px] leading-relaxed text-muted-on-brand">A managed catalogue of news platforms, business publications and digital media outlets.</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6" data-reveal>
                    <p class="text-[36px] leading-none font-bold text-white">{{ $categories }}</p>
                    <p class="mt-3 text-[15px] font-semibold text-white">Publication categories</p>
                    <p class="mt-2 text-[14px] leading-relaxed text-muted-on-brand">Business, finance, technology, markets, general news and digital media.</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6" data-reveal>
                    <p class="flex size-9 items-center justify-center rounded-full bg-teal text-navy-900"><x-icon name="check" :size="17" :stroke="3" /></p>
                    <p class="mt-3 text-[15px] font-semibold text-white">Proof of delivery</p>
                    <p class="mt-2 text-[14px] leading-relaxed text-muted-on-brand">Every campaign is documented in a report you can read before you buy.</p>
                </div>
            </div>
        </div>

        @php
            $stripHeading = str_replace('{network}', $network, (string) $site->get('trust_strip_heading'));
            $stripLogos = collect($site->mediaStripLogos());
        @endphp
        <div class="mt-14 border-t border-white/10 pt-9">
            <p class="text-[12px] font-semibold tracking-[0.16em] text-muted-on-brand uppercase">{{ $stripHeading }}</p>
            <ul class="mt-5 flex flex-wrap items-center gap-3">
                @forelse ($stripLogos as $logo)
                    <li>
                        <a href="{{ $logo['link'] ?? route('media-network') }}" @if (! empty($logo['link'])) target="_blank" rel="noopener" @endif
                           class="flex items-center rounded-lg bg-white/5 px-4 py-2.5 transition hover:bg-white/10">
                            @if (! empty($logo['logo']))
                                <img src="{{ App\Services\SiteSettings::logoUrl($logo['logo']) }}" alt="{{ $logo['name'] ?? '' }}" loading="lazy" class="h-5 w-auto max-w-[130px] object-contain">
                            @else
                                <span class="text-[15px] font-semibold tracking-tight text-white/75">{{ $logo['name'] ?? '' }}</span>
                            @endif
                        </a>
                    </li>
                @empty
                    @foreach ($outlets as $outlet)
                        <li class="rounded-lg bg-white/5 px-4 py-2.5 text-[15px] font-semibold tracking-tight text-white/75 transition hover:bg-white/10 hover:text-white">
                            {{ $outlet->name }}
                        </li>
                    @endforeach
                @endforelse
            </ul>
        </div>
    </div>
</section>
