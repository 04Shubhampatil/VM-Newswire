<x-layouts.public title="Sample Distribution Reports" description="Download a sample distribution report for each VM Newswire press release package before you enquire.">
    <section class="pt-9 pb-16 md:pt-16 md:pb-24 lg:pt-[72px]">
        <div class="container-site flex flex-col gap-10 md:gap-14">
            <div class="flex max-w-[820px] flex-col gap-6">
                <x-breadcrumb :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'Sample Reports']]" />
                <p class="eyebrow">Sample reports</p>
                <h1 class="display text-[36px] leading-[1.1] tracking-[-0.02em] md:text-[48px] lg:text-[60px]">See exactly what <em class="text-accent">you get.</em></h1>
                <p class="max-w-[620px] text-base leading-relaxed text-muted md:text-lg">Each report shows where a release in that package was published, with live links and the extended network listing.</p>
            </div>
            <ul class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($packages as $package)
                    @php $logo = $package->featuredMedia->first(fn ($outlet) => $outlet->logo_src); @endphp
                    <li class="card-lift flex flex-col gap-5 rounded-card border border-line bg-white p-6 md:p-8">
                        <div class="flex items-start gap-4">
                            @if ($logo)
                                <span class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-line bg-white p-2">
                                    <img src="{{ $logo->logo_src }}" alt="{{ $logo->name }}" loading="lazy" class="max-h-full max-w-full object-contain">
                                </span>
                            @else
                                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-accent-soft text-accent-ink"><x-icon name="file" :size="22" /></span>
                            @endif
                            <div class="flex min-w-0 flex-col gap-1">
                                <h2 class="font-sans text-[22px] leading-tight font-semibold sm:text-[26px]">{{ $package->name }}</h2>
                                <span class="font-mono text-xs text-muted">{{ $package->currentReport ? 'PDF · '.$package->currentReport->formatted_size : 'Coming soon' }}</span>
                            </div>
                        </div>
                        <div class="mt-auto flex flex-wrap gap-2">
                            @if ($package->currentReport)
                                <x-button :href="route('reports.download', $package->slug)" size="sm" icon-left="download" class="h-10! px-4! text-[13px]!" data-track="sample_report_click" data-track-package="{{ $package->name }}">Download</x-button>
                            @endif
                            <x-button :href="route('packages.show', $package->slug)" variant="secondary" size="sm" class="h-10! px-4! text-[13px]!">View Package</x-button>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
    <x-home.final-cta heading="Ready to share your news?"
                      text="Pick the package whose report matches the reach you need, or tell us about your announcement and we will recommend one."
                      label="View Packages" :href="route('packages.index')"
                      secondary-label="Send an Enquiry" :secondary-href="route('contact').'#enquire'" />
</x-layouts.public>
