@props(['dark' => false, 'size' => 30, 'mark' => false])
{{--
    Brand logo (traced SVG in public/images): full lockup by default, or the "VM" mark alone with `mark`.
    `dark` switches to the white version for dark/purple backgrounds. `size` is the rendered height in px.
--}}
@php
    $file = ($mark ? 'logo-mark' : 'logo').($dark ? '-white' : '').'.svg';
    $height = (int) round($size * 1.3);
@endphp
<a href="{{ route('home') }}" aria-label="{{ $site->get('company_name') }} home" {{ $attributes->merge(['class' => 'inline-flex items-center']) }}>
    <img src="{{ asset('images/'.$file) }}" alt="{{ $site->get('company_name') }}" height="{{ $height }}" style="height: {{ $height }}px; width: auto" decoding="async" @if ($size > 24) fetchpriority="high" @endif>
</a>
