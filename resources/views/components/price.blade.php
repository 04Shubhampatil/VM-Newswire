@props(['package', 'size' => 'md', 'from' => true])
@php
    $num = ['sm' => 'text-[28px] md:text-[32px]', 'md' => 'text-[40px]', 'lg' => 'text-[56px] md:text-[48px]'][$size];
@endphp
<div {{ $attributes->merge(['class' => 'flex items-baseline gap-1.5']) }}>
    @if ($package->formatted_price)
        @if ($from)<span class="text-[13px] text-muted">From</span>@endif
        <span class="font-sans {{ $num }} leading-none font-semibold">{{ $package->formatted_price }}</span>
        <span class="text-[13px] text-muted">/ PR</span>
    @else
        <span class="font-sans {{ $size === 'sm' ? 'text-2xl' : 'text-3xl' }} leading-none font-semibold">Price on request</span>
    @endif
</div>
