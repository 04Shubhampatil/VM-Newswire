@props(['eyebrow' => null, 'description' => null, 'dark' => false, 'as' => 'h2', 'center' => false])
{{-- Reveals in sequence: eyebrow → heading → description. --}}
<div data-reveal-group {{ $attributes->merge(['class' => 'flex max-w-3xl flex-col gap-4 '.($center ? 'mx-auto items-center text-center' : '')]) }}>
    @if ($eyebrow)
        <p data-reveal class="eyebrow {{ $dark ? 'eyebrow-dark' : '' }}">{{ $eyebrow }}</p>
    @endif
    <{{ $as }} data-reveal class="display text-4xl leading-[1.12] md:text-[38px] lg:text-[44px] {{ $dark ? 'text-canvas' : 'text-ink' }}">{{ $slot }}</{{ $as }}>
    @if ($description)
        <p data-reveal class="max-w-2xl text-base leading-relaxed md:text-[17px] {{ $dark ? 'text-muted-on-brand' : 'text-muted' }}">{{ $description }}</p>
    @endif
</div>
