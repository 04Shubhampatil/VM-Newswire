{{--
    Hero image block shared by the homepage and inner-page heroes (reference composition): the hero photo in a
    rounded card on a mint panel, two floating stat cards over its left edge, and a curved teal line running
    down-left from the lower card. Figures come from Website Content (network size, reach label).
--}}
@php
    $network = (string) $site->get('network_size_label');
    $reach = (string) $site->get('hero_reach_label');
@endphp
<div {{ $attributes->merge(['class' => 'relative mx-auto w-full max-w-[470px] lg:mr-0 lg:ml-auto']) }}>
    <span class="pointer-events-none absolute -inset-x-4 -top-6 -bottom-6 rounded-[32px] bg-gradient-to-br from-[#eef9f6] via-[#e1f5ef] to-[#d0eee7] lg:-top-14 lg:-right-[40vw] lg:-bottom-6 lg:-left-16 lg:rounded-[56px]" aria-hidden="true"></span>

    <div class="relative overflow-hidden rounded-2xl shadow-[var(--shadow-float)]">
        <img src="{{ asset('images/hero-skyline.webp') }}" alt="City skyline with modern office towers"
             width="1100" height="619" fetchpriority="high" decoding="async"
             class="aspect-[4/3] w-full object-cover sm:aspect-video">
    </div>

    <div class="absolute top-[6%] left-0 flex items-center gap-3 rounded-xl bg-white px-4 py-3 shadow-[var(--shadow-float)]">
        <span class="flex size-10 items-center justify-center rounded-full bg-teal-soft text-accent-ink"><x-icon name="users" :size="20" :stroke="1.8" /></span>
        <span>
            <span class="block text-[18px] leading-tight font-bold text-heading">{{ $network }}</span>
            <span class="block text-[12px] font-medium text-muted">Media Outlets</span>
        </span>
    </div>

    <div class="absolute top-[54%] -left-3 flex items-center gap-3 rounded-xl bg-white px-4 py-3 shadow-[var(--shadow-float)]">
        <span class="flex size-10 items-center justify-center rounded-full bg-teal-soft text-accent-ink"><x-icon name="globe" :size="20" :stroke="1.8" /></span>
        <span>
            <span class="block text-[15px] leading-tight font-bold text-heading">Global Reach</span>
            <span class="block text-[12px] font-medium text-muted">{{ $reach }}</span>
        </span>
        <svg class="pointer-events-none absolute top-[calc(100%-4px)] -left-[76px] hidden h-[84px] w-[90px] text-teal lg:block" viewBox="0 0 90 84" fill="none" aria-hidden="true">
            <path d="M86 2C88 34 66 66 4 81" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
        </svg>
    </div>
</div>
