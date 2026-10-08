@props(['eyebrow' => null, 'description' => null, 'dark' => false, 'as' => 'h2', 'center' => false])
{{-- Reveals in sequence: eyebrow → heading → description. --}}
<div data-reveal-group {{ $attributes->merge(['class' => 'flex max-w-3xl flex-col gap-5 '.($center ? 'mx-auto items-center text-center' : '')]) }}>
    @if ($eyebrow)
        <p data-reveal class="eyebrow {{ $dark ? 'eyebrow-dark' : '' }}">{{ $eyebrow }}</p>
    @endif
    <{{ $as }} data-reveal class="display text-[34px] leading-[1.1] md:text-[40px] lg:text-[46px] {{ $dark ? 'text-canvas' : 'text-ink' }}">{{ $slot }}</{{ $as }}>
    @if ($description)
        <p data-reveal class="max-w-2xl text-[17px] leading-relaxed md:text-[18px] {{ $dark ? 'text-muted-on-brand' : 'text-muted' }}">{{ $description }}</p>
    @endif
</div>
