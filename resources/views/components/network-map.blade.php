@props(['summary'])
{{--
    Category cards around the network: name, example outlets, a photo (the first poster in that category)
    and a short description. Data: CatalogCache::mediaSummary(); descriptions: config vmnewswire.media_category_descriptions.
--}}
@php
    $nodes = collect($summary)->take(6)->values();
    $n = max($nodes->count(), 1);
    $cx = 330; $cy = 330; $rx = 230; $ry = 220; $cw = 184; $ch = 206;
    $placed = $nodes->map(function ($node, $i) use ($n, $cx, $cy, $rx, $ry) {
        $angle = deg2rad(-90 + $i * (360 / $n));
        return $node + ['x' => (int) round($cx + $rx * cos($angle)), 'y' => (int) round($cy + $ry * sin($angle))];
    });
@endphp
<div {{ $attributes }}>
    <div class="relative mx-auto hidden h-[660px] w-[660px] md:block" data-reveal-group style="--reveal-base: 4">
        <svg aria-hidden="true" width="660" height="660" viewBox="0 0 660 660" class="absolute inset-0">
            <ellipse cx="{{ $cx }}" cy="{{ $cy }}" rx="{{ $rx }}" ry="{{ $ry }}" fill="none" stroke="#E6E4EF"/>
            @foreach ($placed as $node)
                <line x1="{{ $cx }}" y1="{{ $cy }}" x2="{{ $node['x'] }}" y2="{{ $node['y'] }}" stroke="#C4B5F5" stroke-dasharray="3 4"/>
            @endforeach
        </svg>
        <div aria-hidden="true" data-reveal="scale" class="absolute z-10 flex size-[156px] flex-col items-center justify-center gap-1.5 rounded-full bg-brand text-center text-white shadow-[0_0_0_10px_#FFFFFF,0_0_0_11px_#E6E4EF]"
             style="left: {{ $cx - 78 }}px; top: {{ $cy - 78 }}px">
            <span class="font-display text-[22px] leading-none font-bold">{{ $site->get('company_name') }}</span>
            <span class="px-4 text-[9px] font-bold tracking-[0.12em] text-accent-on-brand uppercase">Global press release distribution</span>
        </div>
        <ul>
            @foreach ($placed as $node)
                <li data-reveal="scale" class="absolute" style="left: {{ $node['x'] - $cw / 2 }}px; top: {{ $node['y'] - $ch / 2 }}px; width: {{ $cw }}px; height: {{ $ch }}px">
                    <x-category-card :node="$node" class="h-full" />
                </li>
            @endforeach
        </ul>
    </div>

    <div class="flex flex-col gap-6 md:hidden">
        <div class="flex h-[72px] items-center gap-2.5 self-center rounded-full bg-brand px-6 text-white">
            <span class="font-display text-[26px] font-bold">{{ $site->get('network_size_label') }}</span><span class="text-sm text-muted-on-brand">media outlets</span>
        </div>
        <ul class="grid grid-cols-2 gap-3" data-reveal-group>
            @foreach ($nodes as $node)
                <li data-reveal><x-category-card :node="$node" /></li>
            @endforeach
        </ul>
    </div>
</div>
