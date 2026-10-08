@props(['package', 'index' => 0])
{{--
    Packages-page card: line icon + name, short description, price, then "View Package Details" and
    "Enquire Now" pills. The highlighted package gets an amber "Most Popular" badge, a teal-tinted ground and
    border, and the amber enquiry button. Icons cycle by position (packages carry no icon field).
--}}
@php
    $featured = (bool) $package->is_highlighted;
    $url = route('packages.show', $package->slug);
    $icons = ['megaphone', 'globe', 'star', 'pin', 'users', 'file'];
    $icon = $icons[$index % count($icons)];
@endphp
<article {{ $attributes->merge(['class' => 'group relative flex flex-col rounded-2xl border p-6 transition duration-200 hover:-translate-y-1 sm:p-7 '.($featured
    ? 'border-teal/60 bg-[#f3fbf9] shadow-[var(--shadow-elevated)] hover:shadow-[var(--shadow-float)]'
    : 'border-line bg-white shadow-[var(--shadow-card)] hover:border-[#b9c7d4] hover:shadow-[var(--shadow-float)]')]) }}>
    @if ($featured)
        <span class="absolute -top-3 right-6 rounded-full bg-cta px-3 py-1 text-[11px] font-bold tracking-[0.08em] text-cta-ink uppercase shadow-sm">Most Popular</span>
    @endif

    <h3 class="flex items-center gap-3 text-[19px] leading-snug font-semibold text-heading">
        <x-icon :name="$icon" :size="24" :stroke="1.7" class="text-heading" />
        <a href="{{ $url }}" class="transition hover:text-accent-ink" data-track="cta_click" data-track-label="Package card: {{ $package->name }}" data-track-package="{{ $package->name }}">{{ $package->name }}</a>
    </h3>
    @if ($package->short_description)
        <p class="mt-3 text-[14px] leading-relaxed text-muted">{{ $package->short_description }}</p>
    @endif

    <div class="mt-auto pt-6">
        @if ($package->formatted_price)
            <p class="text-[36px] leading-none font-bold tracking-[-0.02em] text-heading">{{ $package->formatted_price }}</p>
            <p class="mt-2 text-[13px] text-muted">{{ $package->currency ?: 'USD' }} / Per Release</p>
        @else
            <p class="text-[24px] leading-none font-semibold text-heading">Price on request</p>
        @endif

        <div class="mt-6 grid gap-2.5 xl:grid-cols-2">
            <a href="{{ $url }}" class="flex h-11 items-center justify-center rounded-full border border-line bg-white px-4 text-[13px] font-semibold whitespace-nowrap text-heading transition hover:border-heading"
               data-track="cta_click" data-track-label="Package card: View Package" data-track-package="{{ $package->name }}">View Package Details</a>
            <a href="{{ route('contact', ['package' => $package->slug]) }}" x-data @click.prevent="$dispatch('open-enquiry', {{ $package->id }})" aria-haspopup="dialog"
               class="flex h-11 items-center justify-center gap-2 rounded-full px-4 text-[13px] font-semibold whitespace-nowrap transition {{ $featured ? 'bg-cta text-cta-ink hover:bg-cta-hover' : 'bg-navy-900 text-white hover:bg-brand' }}"
               data-track="cta_click" data-track-label="Package card: Enquire Now" data-track-package="{{ $package->name }}">
                Enquire Now <x-icon name="arrow-right" :size="14" :stroke="2.4" class="transition group-hover:translate-x-0.5" />
            </a>
        </div>
    </div>
</article>
