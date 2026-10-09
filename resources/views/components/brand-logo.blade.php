@props(['size' => 40, 'dark' => false, 'white' => false, 'href' => null])
{{--
    VM Newswire lockup in the round-monogram style: a disc holding a lowercase "vm", then the lowercase
    "vmnews" + bold "wire" wordmark with the tagline beneath. `size` is the disc diameter in px; the wordmark
    scales with it. `dark` is the version for navy backgrounds.
--}}
@php
    $disc = (int) $size;
    // `white`: all-white lockup (white disc with blue monogram) for the blue footer.
    $dark = $dark || $white;
@endphp
<a href="{{ $href ?? route('home') }}" aria-label="{{ $site->get('company_name') }} home" {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center gap-2.5 '.($dark ? 'text-white' : 'text-heading')]) }}>
    <span class="flex shrink-0 items-center justify-center rounded-full font-bold tracking-[-0.04em] {{ $white ? 'bg-white text-[#123b5d]' : ($dark ? 'bg-teal text-navy-900' : 'bg-[#123b5d] text-white') }}"
          style="width: {{ $disc }}px; height: {{ $disc }}px; font-size: {{ round($disc * 0.46) }}px; line-height: 1">vm</span>
    <span class="flex flex-col">
        <span class="leading-none tracking-[-0.035em]" style="font-size: {{ round($disc * 0.66) }}px"><span class="font-normal">vmnews</span><span class="font-bold">wire</span></span>
        <span class="mt-[3px] leading-none font-semibold tracking-[0.12em] whitespace-nowrap uppercase {{ $dark ? 'text-white/50' : 'text-muted' }}" style="font-size: {{ max(6, round($disc * 0.17)) }}px">Global Press Release Distribution</span>
    </span>
</a>
