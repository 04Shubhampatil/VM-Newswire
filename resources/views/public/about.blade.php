@use('App\Support\SafeMarkdown')
<x-layouts.public title="About" description="{{ $site->get('about_intro') }}">
    <section class="pt-11 pb-14 md:pt-20 md:pb-20 lg:py-24">
        <div class="container-site flex flex-col gap-8 lg:grid lg:grid-cols-[1.15fr_1fr] lg:items-end lg:gap-20">
            <div class="flex flex-col gap-6">
                <x-breadcrumb :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'About']]" />
                <p class="eyebrow">About {{ $site->get('company_name') }}</p>
                <h1 class="display text-[36px] leading-[1.1] tracking-[-0.02em] md:text-[50px] lg:text-[60px]">A clearer way to <em class="text-accent italic">distribute</em> press releases.</h1>
            </div>
            <div class="flex flex-col gap-5">
                <p class="text-lg leading-relaxed md:text-[19px]">{{ $site->get('about_intro') }}</p>
                <div class="prose-vmn text-muted">{{ SafeMarkdown::render($site->get('about_body')) }}</div>
            </div>
        </div>
    </section>

    {{-- Photo (admin-editable in Website Content) with floating stat cards, beside the numbered "how we work" list --}}
    <section class="border-t border-line bg-white section-y">
        <div class="container-site flex flex-col gap-12 lg:grid lg:grid-cols-[minmax(0,1fr)_minmax(0,1.05fr)] lg:items-center lg:gap-20">
            <div class="relative mx-auto w-full max-w-[520px] lg:mx-0" data-reveal="scale">
                <div class="aspect-[4/3] overflow-hidden rounded-[10px] border border-line bg-canvas-deep">
                    @if ($aboutImage = $site->get('about_image'))
                        <img src="{{ Storage::disk(config('vmnewswire.posters.disk'))->url($aboutImage) }}" alt="{{ $site->get('company_name') }} team at work" loading="lazy" class="size-full object-cover">
                    @else
                        <div class="flex size-full flex-col justify-between p-6"><span class="size-2.5 bg-accent"></span><span class="text-lg font-semibold text-muted">Add a photo in Admin → Website Content</span></div>
                    @endif
                </div>
                {{-- floating cards, like the reference --}}
                <div aria-hidden="true" class="absolute -top-5 -left-3 flex flex-col gap-1 rounded-[8px] border border-line bg-white px-4 py-3 shadow-[0_16px_36px_-24px_rgba(27,27,47,0.45)] md:-left-8">
                    <span class="text-[10px] font-bold tracking-[0.12em] text-muted uppercase">Media outlets</span>
                    <span class="font-display text-2xl leading-none font-bold text-ink">{{ $site->get('network_size_label') }}</span>
                    <span class="mt-1 flex items-center gap-1.5 text-[11px] font-semibold text-success-ink"><span class="size-1.5 rounded-full bg-success"></span>Distributing</span>
                </div>
                <div aria-hidden="true" class="absolute -right-3 -bottom-6 flex w-[200px] flex-col gap-2 rounded-[8px] border border-line bg-white p-4 shadow-[0_16px_36px_-24px_rgba(27,27,47,0.45)] md:-right-8">
                    <span class="text-[10px] font-bold tracking-[0.12em] text-muted uppercase">Distribution report</span>
                    <span class="flex items-end gap-1">
                        @foreach ([40, 55, 48, 70, 62, 84, 76, 96] as $h)<span class="w-3 rounded-sm bg-accent" style="height: {{ $h * 0.4 }}px; opacity: {{ 0.45 + $loop->index * 0.07 }}"></span>@endforeach
                    </span>
                    <span class="text-[12px] font-semibold text-ink">Live links, every outlet</span>
                </div>
            </div>

            <div class="flex flex-col gap-8">
                <x-section-heading eyebrow="How we work">Built on transparency.</x-section-heading>
                <ol class="flex flex-col gap-7" data-reveal-group>
                    @foreach ([
                        ['Transparent packages', 'Each package names its headline platforms, its extended network and one price per press release.'],
                        ['Proof before you commit', 'Every package has a downloadable sample report showing real placements and live links.'],
                        ['People, not a checkout', 'Enquiries go straight to our team. We confirm details personally — no payment is taken online.'],
                        ['Reporting you can share', 'After distribution you receive a report with every placement, ready to forward to clients or stakeholders.'],
                    ] as [$title, $text])
                        <li data-reveal class="grid grid-cols-[36px_1fr] gap-4">
                            <span class="flex size-9 items-center justify-center rounded-full border-2 border-accent bg-white text-sm font-bold text-accent-ink">{{ $loop->iteration }}</span>
                            <div class="flex flex-col gap-1.5 pt-1">
                                <h3 class="text-[17px] leading-snug font-bold text-accent-ink">{{ $title }}</h3>
                                <p class="text-[15px] leading-relaxed text-muted">{{ $text }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>

    <section class="border-t border-line section-y">
        <div class="container-site flex flex-col gap-8 md:gap-12">
            <x-section-heading eyebrow="Who we work with">For anyone with news to share.</x-section-heading>
            <ul class="border-b border-line">
                @foreach ([
                    ['Companies & startups', 'Funding rounds, product launches, partnerships and company milestones.'],
                    ['PR & communications agencies', 'Reliable distribution for client announcements, with reports to share.'],
                    ['Founders & executives', 'Leadership news, appointments and thought leadership announcements.'],
                ] as [$title, $text])
                    <li class="flex flex-col gap-2 border-t border-line py-7 md:grid md:grid-cols-[1fr_1.4fr] md:items-baseline md:gap-10">
                        <h3 class="display text-[28px] leading-tight md:text-4xl">{{ $title }}</h3>
                        <p class="text-base leading-relaxed text-muted">{{ $text }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    <section class="border-t border-line section-y">
        <div class="container-site grid gap-4 lg:grid-cols-2 lg:gap-6">
            <div class="flex flex-col rounded-card border border-line bg-white p-6 md:p-10">
                <h2 class="display mb-5 text-4xl">Company details</h2>
                <dl>
                    @foreach (array_filter([
                        'Company' => $site->get('company_name'),
                        'Website' => parse_url(config('app.url'), PHP_URL_HOST),
                        'Email' => $site->get('company_email'),
                        'Phone' => $site->get('phone'),
                        'WhatsApp' => $site->get('whatsapp'),
                        'Address' => $site->get('address'),
                    ]) as $label => $value)
                        <div class="flex flex-col gap-1 border-t border-line-soft py-3.5 text-[15px] md:grid md:grid-cols-[180px_1fr] md:gap-4">
                            <dt class="text-muted">{{ $label }}</dt>
                            <dd class="font-semibold">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
            <div class="flex flex-col justify-between gap-8 rounded-card bg-brand p-6 text-canvas md:p-10">
                <div class="flex flex-col gap-4">
                    <p class="eyebrow eyebrow-dark">Get started</p>
                    <h2 class="display text-[30px] leading-[1.12] text-canvas md:text-[36px]">Ready to distribute your next release?</h2>
                    <p class="text-base leading-relaxed text-muted-on-brand">Compare packages, download a sample report and send us an enquiry.</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <x-button :href="route('contact')" icon="arrow-right">Enquire Now</x-button>
                    <x-button :href="route('packages.index')" variant="on-brand">View Packages</x-button>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
