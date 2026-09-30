@props(['label', 'value', 'href' => null, 'accent' => false])
@php $tag = $href ? 'a' : 'div'; @endphp
<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'flex flex-col gap-2 rounded-[8px] border border-[#E6E4EF] bg-white px-6 py-5 '.($href ? 'transition hover:border-[#A98BF0]' : '')]) }}>
    <span class="text-[13px] text-muted">{{ $label }}</span>
    <span class="font-display text-4xl leading-none font-semibold {{ $accent ? 'text-accent-ink' : 'text-ink' }}">{{ $value }}</span>
    @isset($hint)<span class="text-xs text-muted">{{ $hint }}</span>@endisset
</{{ $tag }}>
