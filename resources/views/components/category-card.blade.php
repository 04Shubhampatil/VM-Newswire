@props(['node'])
{{-- Category card: name + "Active", example outlets, photo (first poster in the category), one-line description. --}}
@php
    $desc = $node['description'] ?? config('vmnewswire.media_category_descriptions.'.$node['category'], '');
@endphp
<div {{ $attributes->merge(['class' => 'card-lift flex flex-col rounded-[8px] border border-line bg-white p-3']) }}>
    <span class="flex items-center justify-between gap-2">
        <span class="flex items-center gap-2 text-[17px] leading-6 font-bold text-ink"><span class="size-1.5 bg-accent"></span>{{ $node['category'] }}</span>
        <span class="flex items-center gap-1.5 text-[11px] font-semibold text-success-ink"><span class="size-1.5 rounded-full bg-success"></span>Active</span>
    </span>
    <span class="mt-0.5 truncate text-[10px] font-bold tracking-[0.12em] text-muted uppercase">{{ implode(' · ', $node['examples']) }}</span>
    <span class="relative mt-2 block h-[84px] w-full overflow-hidden rounded-[5px] bg-canvas-deep">
        @if (! empty($node['poster_src']))
            <img src="{{ $node['poster_src'] }}" alt="" loading="lazy" class="size-full object-cover">
        @else
            <span class="absolute inset-0 flex items-end p-2.5 text-[12px] font-semibold text-muted">{{ $node['count'] }} outlets</span>
        @endif
    </span>
    <span class="mt-2 line-clamp-2 text-[12px] leading-[1.35] text-muted">{{ $desc }}</span>
</div>
