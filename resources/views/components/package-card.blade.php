@props(['package', 'index' => 0])
{{--
    Package card. Neutral navy/white treatment across every tier (no per-tier colour), so six packages read as
    one family. The highlighted package carries an amber "Most Popular" tag and an amber enquiry button; the
    rest use the solid navy button.
--}}
@php
    $featured = (bool) $package->is_highlighted;
    $url = route('packages.show', $package->slug);
@endphp
<article {{ $attributes->merge(['class' => 'relative flex flex-col rounded-2xl border border-line bg-white shadow-card transition duration-200 hover:-translate-y-0.5 hover:border-teal/40 hover:shadow-float']) }} @if ($featured) data-featured @endif>
    @if ($featured)
        <span class="absolute -top-3 left-5 z-10 rounded-full bg-cta px-3 py-1 text-[12px] font-semibold tracking-wide whitespace-nowrap text-cta-ink uppercase">Most Popular</span>
    @endif

    <div class="flex flex-col gap-1 px-6 pt-7">
        <h3 class="text-[20px] leading-tight font-semibold text-heading">
            <a href="{{ $url }}" class="transition hover:text-accent-ink" data-track="cta_click" data-track-label="Package card: {{ $package->name }}" data-track-package="{{ $package->name }}">{{ $package->name }}</a>
        </h3>
        @if ($package->short_description)
            <p class="mt-1 text-[14px] leading-relaxed text-muted">{{ $package->short_description }}</p>
        @endif
    </div>

    <div class="grow px-6 pt-6">
        @if ($package->formatted_price)
            <p class="text-[40px] leading-none font-bold tracking-tight text-heading">{{ $package->formatted_price }}</p>
            <p class="mt-2 text-[13px] font-medium text-muted">{{ $package->currency ?: 'USD' }} / Per Release</p>
        @else
            <p class="text-[26px] leading-none font-semibold text-heading">Price on request</p>
        @endif
    </div>

    <div class="mt-6 flex flex-col gap-3 border-t border-line px-6 py-6">
        <a href="{{ $url }}" class="text-[13px] font-medium text-muted transition hover:text-accent-ink hover:underline"
           data-track="cta_click" data-track-label="Package card: View Package" data-track-package="{{ $package->name }}">View Package Details</a>
        <a href="{{ route('contact', ['package' => $package->slug]) }}" x-data @click.prevent="$dispatch('open-enquiry', {{ $package->id }})" aria-haspopup="dialog"
           class="{{ $featured ? 'btn btn-cta' : 'btn btn-dark' }} h-11 w-full"
           data-track="cta_click" data-track-label="Package card: Enquire Now" data-track-package="{{ $package->name }}">Enquire Now</a>
    </div>
</article>
