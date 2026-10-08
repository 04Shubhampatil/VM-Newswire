@php
    $steps = [
        ['Choose a package', 'Compare packages and pick the distribution network that fits your announcement.'],
        ['Submit your press release', 'Send an enquiry. Our team confirms the details and receives your release.'],
        ['Distribution across selected networks', 'Your release goes out across the platforms and outlets in your package.'],
        ['Receive your distribution report', 'A professional report shows where your release was published, with live links.'],
    ];
@endphp
{{-- Steps activate in sequence (01 → 02 → 03 → 04) as the timeline scrolls into view; see resources/js/motion.js. --}}
<ol data-steps {{ $attributes->merge(['class' => 'grid md:grid-cols-2 md:gap-x-10 md:gap-y-14 lg:grid-cols-4 lg:gap-8']) }}>
    @foreach ($steps as [$title, $text])
        <li class="step grid grid-cols-[48px_1fr] gap-4 md:flex md:flex-col md:gap-0">
            {{-- Mobile: vertical timeline --}}
            <div class="flex flex-col items-center md:hidden">
                <span class="step-dot flex size-12 shrink-0 items-center justify-center rounded-full border border-line bg-white font-sans text-[22px] font-semibold text-ink">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                @unless ($loop->last)
                    <span class="relative w-px grow bg-line"><span class="step-vfill absolute inset-0 bg-accent"></span></span>
                @endunless
            </div>

            {{-- Tablet / desktop: large numbers on a horizontal rule --}}
            <span class="step-num hidden font-sans text-[60px] leading-none font-semibold text-line md:block lg:text-[72px]">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
            <span class="relative my-7 hidden h-px bg-line md:block">
                <span class="step-fill absolute inset-0 bg-accent"></span>
                <span class="step-dot absolute -top-[5px] left-0 size-[11px] rounded-full border border-line bg-canvas"></span>
            </span>

            <div class="flex flex-col gap-2 pt-2 pb-8 md:gap-3 md:p-0">
                <h3 class="text-2xl leading-tight font-normal">{{ $title }}</h3>
                <p class="text-[15px] leading-relaxed text-muted">{{ $text }}</p>
            </div>
        </li>
    @endforeach
</ol>
