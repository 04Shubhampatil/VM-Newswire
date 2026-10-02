<x-layouts.public title="Compare Press Release Distribution Packages" description="Compare VM Newswire press release packages side by side: major media, distribution network, price and sample reports.">
    <section class="pt-9 pb-16 md:pt-16 md:pb-24 lg:pt-[72px]">
        <div class="container-site flex flex-col gap-8 md:gap-10">
            <x-breadcrumb :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'Packages']]" />
            <div class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
                <div class="flex max-w-[820px] flex-col gap-5">
                    <p class="eyebrow">All packages</p>
                    <h1 class="display text-[36px] leading-[1.1] tracking-[-0.02em] md:text-[48px] lg:text-[60px]">Compare packages <em class="text-accent italic">at a glance.</em></h1>
                    <p class="max-w-[620px] text-base leading-relaxed text-muted md:text-lg">Find the distribution package that matches your reach, media and reporting requirements.</p>
                </div>
                <a href="{{ route('contact') }}" class="link-arrow shrink-0">Not sure which? Ask us <x-icon name="arrow-right" :size="16" /></a>
            </div>

            @if ($packages->isEmpty())
                <p class="text-muted">Packages are being updated. <a href="{{ route('contact') }}" class="text-accent-ink underline">Contact us</a> for current options.</p>
            @else
                <x-comparison-table :packages="$packages" :brands="$brands" />
            @endif
        </div>
    </section>

    <section class="border-t border-line py-20 md:py-24">
        <div class="container-site grid gap-7 md:grid-cols-3 md:gap-10" data-reveal-group>
            @foreach ([
                ['Every package includes reporting', 'A professional distribution report shows where your release was published, with live links.'],
                ['Sample report before you choose', 'Download a sample report from any package page to see real placements.'],
                ['Not sure which package fits?', 'Choose "Not sure" on the enquiry form and our team will recommend one.'],
            ] as [$title, $text])
                <div data-reveal class="flex flex-col gap-2.5 border-t-2 border-ink pt-5">
                    <h2 class="font-display text-[26px] leading-tight font-semibold">{{ $title }}</h2>
                    <p class="text-[15px] leading-relaxed text-muted">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <x-cta-band />
    <x-enquiry-modal :packages="$packages" />
    <x-report-modal />
</x-layouts.public>
