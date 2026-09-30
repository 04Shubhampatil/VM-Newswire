@props(['package', 'index' => null])
@php
    $featured = $package->is_highlighted;
    $url = route('packages.show', $package->slug);
@endphp
<article @if ($featured) data-featured @endif
         {{ $attributes->merge(['class' => 'pkg-card group relative flex flex-col gap-5 rounded-card border p-6 md:p-8 '.($featured ? 'border-accent bg-[#F8F5FF] shadow-[0_24px_48px_-38px_rgba(27,27,47,0.45)]' : 'border-line bg-white')]) }}>
    <div class="flex h-6 items-center justify-between">
        <span class="pkg-num font-mono text-[13px] {{ $featured ? 'text-accent-ink' : 'text-muted' }}">{{ str_pad((string) ($index ?? $package->display_order), 2, '0', STR_PAD_LEFT) }}</span>
        @if ($featured)
            <span class="badge bg-accent-soft text-accent-ink">Most requested</span>
        @elseif ($package->brand)
            <span class="label-caps">{{ $package->brand }}</span>
        @endif
    </div>

    <div class="flex flex-col gap-2.5">
        <h3 class="flex items-end font-display text-2xl leading-[1.1] font-semibold tracking-[-0.005em] md:min-h-[53px]">
            <a href="{{ $url }}" class="after:absolute after:inset-0 after:rounded-card after:content-['']">{{ $package->name }}</a>
        </h3>
        <span class="pkg-rule" aria-hidden="true"></span>
        <p class="text-[15px] leading-relaxed text-muted">{{ $package->short_description }}</p>
    </div>

    @if ($package->featuredMedia->isNotEmpty())
        <ul class="relative flex flex-wrap gap-1.5" aria-label="Featured media">
            @foreach ($package->featuredMedia->take(3) as $outlet)
                <li><x-media-chip :name="$outlet->name" /></li>
            @endforeach
            @if ($package->featuredMedia->count() > 3)
                <li><span class="chip text-muted">+{{ $package->featuredMedia->count() - 3 }}</span></li>
            @endif
        </ul>
    @endif

    @if (! empty($package->features))
        <ul class="flex flex-col gap-2.5 {{ $featured ? '' : 'max-md:hidden' }}">
            @foreach (array_slice($package->features, 0, 4) as $feature)
                <li class="pkg-check flex items-center gap-2.5 text-[15px]" style="--i: {{ $loop->index }}"><x-icon name="check" :size="16" :stroke="2.2" class="text-accent" />{{ $feature }}</li>
            @endforeach
        </ul>
    @endif

    <x-price :package="$package" class="mt-auto border-t border-line-soft pt-5" />

    <div class="relative grid grid-cols-[1.3fr_1fr] gap-2.5">
        <x-button :href="$url" :variant="$featured ? 'primary' : 'dark'" size="sm" icon="arrow-right" class="h-12!" data-track="cta_click" data-track-label="Package card: View Package" data-track-package="{{ $package->name }}">View Package</x-button>
        @if ($package->currentReport)
            <x-button :href="route('reports.download', $package->slug)" variant="secondary" size="sm" class="h-12!" data-track="sample_report_click" data-track-package="{{ $package->name }}">Sample Report</x-button>
        @else
            <x-button :href="$url.'#enquire'" variant="secondary" size="sm" class="h-12!">Enquire</x-button>
        @endif
    </div>
</article>
