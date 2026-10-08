@props(['outlet'])
{{-- Shows the uploaded/linked logo when available, otherwise a typographic wordmark. --}}
<div {{ $attributes->merge(['class' => 'flex h-24 items-center justify-center border-r border-b border-line bg-white px-4 text-center md:h-32']) }}>
    @if ($outlet->logo_src)
        <img src="{{ $outlet->logo_src }}" alt="{{ $outlet->name }}" loading="lazy" class="max-h-10 max-w-[70%] object-contain grayscale" />
    @else
        <span class="font-sans text-[22px] font-semibold text-ink md:text-[28px]">{{ $outlet->name }}</span>
    @endif
</div>
