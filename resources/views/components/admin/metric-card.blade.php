@props(['label', 'value', 'href' => null, 'icon' => null, 'accent' => false, 'delta' => null, 'deltaUp' => null])
{{-- Dashboard figure. `delta` is a short comparison line; `deltaUp` colours it (true green, false grey, null neutral). --}}
@php $tag = $href ? 'a' : 'div'; @endphp
<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'flex flex-col gap-4 rounded-card border border-line bg-white p-5 shadow-card '.($href ? 'transition hover:border-[#b9c7d4] hover:shadow-[var(--shadow-elevated)]' : '')]) }}>
    <div class="flex items-start justify-between gap-3">
        <span class="text-[13px] font-medium text-muted">{{ $label }}</span>
        @if ($icon)
            <span class="flex size-9 shrink-0 items-center justify-center rounded-[10px] {{ $accent ? 'bg-teal-soft text-accent-ink' : 'bg-canvas-deep text-heading' }}"><x-icon :name="$icon" :size="18" :stroke="1.8" /></span>
        @endif
    </div>
    <div class="flex flex-col gap-1">
        <span class="text-[32px] leading-none font-bold tracking-[-0.02em] {{ $accent ? 'text-accent-ink' : 'text-heading' }}">{{ $value }}</span>
        @if ($delta)
            <span class="text-[12px] font-medium {{ $deltaUp === true ? 'text-success-ink' : ($deltaUp === false ? 'text-muted' : 'text-muted') }}">{{ $delta }}</span>
        @endif
        @isset($hint)<span class="text-[12px] text-muted">{{ $hint }}</span>@endisset
    </div>
</{{ $tag }}>
