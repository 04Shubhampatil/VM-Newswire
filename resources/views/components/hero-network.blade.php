@props(['outlets'])
{{--
    One press release → many outlets, using admin-uploaded posters.
    Sequence per outlet (staggered 120ms): node pops → connection line draws → poster rises in → status turns green.
    Positions are fixed by index (angles below), never random. Tablet shows 5 posters, mobile 4.
    Clicking a poster opens the outlet modal (<x-outlet-modal>).
--}}
@php
    $outlets = $outlets->take(7)->values();
    $n = max($outlets->count(), 1);
    $cx = 350; $cy = 360; $rx = 250; $ry = 275; $half = 84; // poster card 168×168
    $nodes = $outlets->map(function ($outlet, $i) use ($n, $cx, $cy, $rx, $ry) {
        $angle = deg2rad(-118 + $i * (360 / $n));
        return [
            'outlet' => $outlet,
            'x' => (int) round($cx + $rx * cos($angle)),
            'y' => (int) round($cy + $ry * sin($angle)),
            'd' => ($i * 120).'ms',
            'depth' => [4, 5, 3, 5, 4, 3, 5][$i % 7],
            'tabletHidden' => $i >= 5,
        ];
    });
    $mobile = $outlets->take(4)->values();
@endphp
{{-- x-data gives the poster buttons an Alpine scope so their @click → $dispatch works --}}
<div x-data aria-label="Media distribution network" {{ $attributes }}>
    {{-- Tablet / desktop --}}
    <div class="hn relative mx-auto hidden h-[740px] w-[700px] md:block" data-parallax>
        <svg width="700" height="740" viewBox="0 0 700 740" class="hn-orbit absolute inset-0 overflow-visible" aria-hidden="true">
            <ellipse cx="{{ $cx }}" cy="{{ $cy }}" rx="{{ $rx + 40 }}" ry="{{ $ry + 40 }}" fill="none" stroke="#E6E4EF"/>
            <ellipse cx="{{ $cx }}" cy="{{ $cy }}" rx="150" ry="180" fill="none" stroke="#EFEDF6" stroke-dasharray="2 5"/>
            @foreach ($nodes as $node)
                <g style="--d: {{ $node['d'] }}" class="{{ $node['tabletHidden'] ? 'max-xl:hidden' : '' }}">
                    <line class="hn-line" pathLength="1" x1="{{ $cx }}" y1="{{ $cy }}" x2="{{ $node['x'] }}" y2="{{ $node['y'] }}" stroke="#C4B5F5" stroke-width="1.5"/>
                    <line class="hn-pulse" pathLength="1" x1="{{ $cx }}" y1="{{ $cy }}" x2="{{ $node['x'] }}" y2="{{ $node['y'] }}" stroke="#7C3AED" stroke-width="2" stroke-linecap="round"/>
                    <circle class="hn-node" cx="{{ $cx + ($node['x'] - $cx) * 0.56 }}" cy="{{ $cy + ($node['y'] - $cy) * 0.56 }}" r="4" fill="#7C3AED"/>
                </g>
            @endforeach
        </svg>

        <div class="hn-center absolute z-10" style="left: {{ $cx - 100 }}px; top: {{ $cy - 124 }}px">
            <div class="hn-center-inner"><x-press-release-card /></div>
        </div>

        @foreach ($nodes as $node)
            <div class="hn-outlet absolute {{ $node['tabletHidden'] ? 'max-xl:hidden' : '' }}" style="left: {{ $node['x'] - $half }}px; top: {{ $node['y'] - $half }}px; --d: {{ $node['d'] }}; --depth: {{ $node['depth'] }}">
                <div class="hn-outlet-inner">
                    <x-poster-card :outlet="$node['outlet']" :eager="$loop->index < 4" />
                </div>
            </div>
        @endforeach
    </div>

    {{-- Mobile: press release above a simplified 2×2 poster grid, no parallax --}}
    <div class="hn relative mx-auto h-[590px] w-[350px] md:hidden">
        <svg width="350" height="590" viewBox="0 0 350 590" class="absolute inset-0" aria-hidden="true">
            @foreach ($mobile as $i => $outlet)
                @php $tx = ($i % 2) * 180 + 85; $ty = 214 + intdiv($i, 2) * 182; @endphp
                <g style="--d: {{ $i * 120 }}ms">
                    <path class="hn-line" pathLength="1" d="M175 178 C175 200, {{ $tx }} 196, {{ $tx }} {{ $ty }}" fill="none" stroke="#C4B5F5" stroke-width="1.5"/>
                    <path class="hn-pulse" pathLength="1" d="M175 178 C175 200, {{ $tx }} 196, {{ $tx }} {{ $ty }}" fill="none" stroke="#7C3AED" stroke-width="2" stroke-linecap="round"/>
                </g>
            @endforeach
            <circle cx="175" cy="178" r="4" fill="#7C3AED"/>
        </svg>
        <div class="absolute top-0 left-[95px]"><div class="hn-center-inner"><x-press-release-card small /></div></div>
        @foreach ($mobile as $i => $outlet)
            <div class="absolute" style="left: {{ ($i % 2) * 180 + 2 }}px; top: {{ 214 + intdiv($i, 2) * 182 }}px; --d: {{ $i * 120 }}ms">
                <div class="hn-outlet-inner"><x-poster-card :outlet="$outlet" size="sm" :eager="$i < 2" /></div>
            </div>
        @endforeach
    </div>

    <x-outlet-modal :outlets="$outlets" />
</div>
