@props(['outlets'])
{{--
    Media logo marquee under the confidence cards. Shows the admin-managed media strip logos in full colour,
    scrolling continuously (CSS only; pauses on hover, static wrapped row under prefers-reduced-motion), falling
    back to the highlighted outlets as wordmarks. The list is rendered twice for a seamless loop; the second copy
    is hidden from assistive tech. The label is the Website Content trust heading, not a customer claim.
--}}
@php
    $label = str_replace('{network}', (string) $site->get('network_size_label'), (string) $site->get('trust_strip_heading'));
    $logos = collect($site->mediaStripLogos());
    $items = $logos->isNotEmpty()
        ? $logos->map(fn (array $logo) => ['name' => $logo['name'] ?? '', 'src' => ! empty($logo['logo']) ? App\Services\SiteSettings::logoUrl($logo['logo']) : null, 'link' => $logo['link'] ?? null])
        : $outlets->map(fn ($outlet) => ['name' => $outlet->name, 'src' => null, 'link' => null]);
    $duration = max(24, $items->count() * 6);
@endphp
<section class="bg-white pt-6 pb-10 lg:pb-14" aria-label="Media in the network">
    <div class="container-site text-center">
        <p class="text-[11px] font-semibold tracking-[0.18em] text-muted-soft uppercase">{{ $label }}</p>
    </div>

    <div class="marquee mt-8" style="--marquee-duration: {{ $duration }}s">
        @foreach ([false, true] as $copy)
            <ul class="marquee-track" @if ($copy) aria-hidden="true" @endif>
                @foreach ($items as $item)
                    <li class="flex shrink-0 items-center">
                        <a href="{{ $item['link'] ?: route('media-network') }}" @if ($item['link']) target="_blank" rel="noopener" @endif
                           class="flex items-center transition hover:opacity-80" @if ($copy) tabindex="-1" @endif>
                            @if ($item['src'])
                                <img src="{{ $item['src'] }}" alt="{{ $item['name'] }}" loading="lazy" decoding="async" class="h-8 w-auto max-w-[150px] object-contain sm:h-9 sm:max-w-[170px] lg:h-11 lg:max-w-[190px]">
                            @else
                                <span class="text-[24px] font-bold tracking-tight whitespace-nowrap text-heading">{{ $item['name'] }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        @endforeach
    </div>
</section>
