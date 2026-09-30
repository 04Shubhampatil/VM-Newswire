@props([
    'outlet',            // object/array with name, category, poster_src, logo_src?, poster_width, poster_height
    'size' => 'md',      // md (hero desktop) | sm (hero mobile)
    'eager' => false,    // true for posters visible on first paint
    'button' => true,    // render as a <button> that opens the outlet modal
])
{{-- Poster card: outlet logo or wordmark + category on top, live "active" dot, photo below. --}}
@php
    $o = (object) $outlet;
    $w = $size === 'sm' ? 'w-[165px]' : 'w-[168px]';
    $tag = $button ? 'button' : 'div';
@endphp
<{{ $tag }} @if ($button) type="button" @click="$dispatch('open-outlet', @js($o->slug))" aria-haspopup="dialog" @endif
    {{ $attributes->merge(['class' => "poster-card group/poster flex {$w} flex-col rounded-[8px] border border-line bg-white p-3 text-left shadow-[0_12px_28px_-22px_rgba(27,27,47,0.45)]"]) }}>
    <span class="flex items-start justify-between gap-2">
        @if (! empty($o->logo_src))
            <img src="{{ $o->logo_src }}" alt="{{ $o->name }}" loading="{{ $eager ? 'eager' : 'lazy' }}" class="poster-logo h-6 max-w-[112px] object-contain object-left">
        @else
            <span class="poster-name truncate text-[15px] leading-6 font-bold tracking-[-0.01em] text-ink">{{ $o->name }}</span>
        @endif
        <span class="hn-status mt-2 size-2 shrink-0 rounded-full" aria-hidden="true"></span>
    </span>
    <span class="poster-label mt-1 text-[10px] font-bold tracking-[0.12em] text-muted uppercase">{{ $o->category }}</span>
    <span class="poster-media relative mt-2 block h-[92px] w-full overflow-hidden rounded-[5px] bg-canvas-deep">
        @if ($o->poster_src)
            <img src="{{ $o->poster_src }}" alt="" @if (! empty($o->poster_width)) width="{{ $o->poster_width }}" height="{{ $o->poster_height }}" @endif
                 loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async"
                 class="poster-img size-full object-cover"
                 onerror="this.remove()">
        @endif
        {{-- Designed fallback: shown when there is no poster, or if the image fails to load --}}
        <span class="poster-fallback absolute inset-0 -z-10 flex flex-col justify-between p-2.5">
            <span class="size-2 bg-accent"></span>
            <span class="text-[13px] leading-tight font-semibold text-ink">{{ $o->name }}</span>
        </span>
    </span>
</{{ $tag }}>
