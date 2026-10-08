@use('App\Support\SafeMarkdown')
@php
    // Editorial About page (matches the approved reference). Serif display headings are scoped to this page.
    $company = $site->get('company_name');
    $network = (string) $site->get('network_size_label');
    $trustItems = collect(preg_split('/\R/', (string) $site->get('hero_trust_items')))
        ->map(fn ($item) => trim(str_replace('{network}', $network, $item)))
        ->filter()->values()->take(3);
    $trustIcons = ['globe', 'shield', 'clipboard'];

    $aboutImage = $site->get('about_image');
    $aboutImageUrl = $aboutImage ? Storage::disk(config('vmnewswire.posters.disk'))->url($aboutImage) : null;

    $steps = [
        ['Transparent packages', 'Each package names its headline platforms, its extended network and one price per press release.'],
        ['Proof before you commit', 'Every package has a downloadable sample report showing real placements and live links.'],
        ['People, not a checkout', 'Enquiries go straight to our team. We confirm details personally and no payment is taken online.'],
        ['Reporting you can share', 'After distribution you receive a report with every placement, ready to forward to clients or stakeholders.'],
    ];
    $audiences = [
        ['building', 'Companies & startups', 'Funding rounds, product launches, partnerships and company milestones.'],
        ['megaphone', 'PR & communications agencies', 'Reliable distribution for client announcements, with reports to share.'],
        ['users', 'Founders & executives', 'Leadership news, appointments and thought leadership announcements.'],
    ];
    $details = array_filter([
        ['globe', 'Website', parse_url(config('app.url'), PHP_URL_HOST)],
        ['mail', 'Email', $site->get('company_email')],
        ['phone', 'Phone', $site->get('phone')],
        ['phone', 'WhatsApp', $site->get('whatsapp')],
        ['pin', 'Address', $site->get('address')],
    ], fn ($row) => filled($row[2]));
@endphp
<x-layouts.public title="About" description="{{ $site->get('about_intro') }}">
    {{-- Intro: copy left, photo with floating network figure right --}}
    <section class="relative overflow-hidden bg-white">
        <div class="container-site grid items-start gap-14 pt-14 pb-16 lg:grid-cols-[minmax(0,1.22fr)_minmax(0,1fr)] lg:gap-12 lg:pt-20 lg:pb-24 xl:gap-16">
            <div class="flex max-w-[740px] flex-col" data-reveal-group>
                <p class="eyebrow" data-reveal>About {{ $company }}</p>
                <h1 class="mt-6 text-[40px] leading-[1.08] font-semibold tracking-[-0.015em] text-ink md:text-[52px] lg:text-[48px] xl:text-[54px] min-[90rem]:text-[60px]" data-reveal>
                    A clearer way to<br class="hidden xl:block"> <em class="text-accent-ink not-italic">distribute</em> press releases.
                </h1>
                <p class="mt-7 max-w-[520px] text-[17px] leading-[1.7] text-muted md:text-[18px]" data-reveal>{{ $site->get('about_intro') }}</p>

                @if ($trustItems->isNotEmpty())
                    <ul class="mt-12 grid grid-cols-3 divide-x divide-line" data-reveal aria-label="Why choose {{ $company }}">
                        @foreach ($trustItems as $i => $item)
                            <li class="flex flex-col gap-3 pr-4 {{ $loop->first ? '' : 'pl-5 md:pl-7' }}">
                                <x-icon :name="$trustIcons[$i % count($trustIcons)]" :size="24" :stroke="1.6" class="text-accent-ink" />
                                <span class=" text-[16px] leading-snug font-semibold text-ink md:text-[17px]">{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="flex flex-col" data-reveal-group>
                @if (filled($site->get('about_body')))
                    <div class="prose-vmn max-w-[330px] text-[15px] leading-[1.7] text-muted lg:ml-auto [&_p]:mb-0 [&_p]:text-[15px]" data-reveal>{{ SafeMarkdown::render($site->get('about_body')) }}</div>
                @endif

                <div class="relative mt-10 lg:mt-12" data-reveal="scale">
                    <figure class="relative mx-auto w-full max-w-[520px] overflow-hidden rounded-[14px] border border-line bg-white lg:ml-0" style="box-shadow: var(--shadow-elevated)">
                        @if ($aboutImageUrl)
                            <img src="{{ $aboutImageUrl }}" alt="{{ $company }} team at work" loading="eager" class="aspect-[4/3] w-full object-cover">
                        @else
                            {{-- Designed placeholder until a photo is uploaded in Admin → Website Content --}}
                            <div class="flex aspect-[4/3] w-full flex-col justify-between bg-canvas-deep p-6">
                                <div class="mx-auto mt-2 w-[82%] overflow-hidden rounded-[8px] border border-line bg-white" style="box-shadow: var(--shadow-card)">
                                    <span class="block h-[3px] w-full bg-accent"></span>
                                    <div class="flex flex-col gap-2.5 p-5">
                                        <span class="font-mono text-[9px] tracking-[0.1em] text-accent-ink uppercase">Press release</span>
                                        <span class=" text-[20px] leading-tight font-semibold text-ink">Your announcement, published.</span>
                                        @foreach ([100, 94, 82, 60] as $w)<span class="block h-1.5 rounded-sm bg-line-soft" style="width: {{ $w }}%"></span>@endforeach
                                    </div>
                                </div>
                                <span class="text-[12px] font-semibold text-muted">Add a photo in Admin → Website Content</span>
                            </div>
                        @endif
                    </figure>

                    {{-- Floating figure: the extended network size (admin setting) --}}
                    <a href="{{ route('media-network') }}" class="card-lift absolute -top-8 -right-2 flex flex-col gap-1 rounded-[12px] border border-line bg-white px-6 py-5 md:-right-6" style="box-shadow: var(--shadow-card)">
                        <span class="flex items-center justify-between gap-6">
                            <span class=" text-[30px] leading-none font-semibold text-ink">{{ $network }}</span>
                            <x-icon name="arrow-up-right" :size="16" class="text-accent-ink" />
                        </span>
                        <span class="text-[13px] text-muted">Media Outlets</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- How we work --}}
    <section id="how-it-works" class="scroll-mt-24 border-y border-line bg-lavender section-y">
        <div class="container-site grid gap-12 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.15fr)] lg:gap-20">
            <div class="flex max-w-[480px] flex-col" data-reveal-group>
                <p class="eyebrow" data-reveal>How we work</p>
                <h2 class="mt-5 text-[34px] leading-[1.1] font-semibold tracking-[-0.01em] text-ink md:text-[42px]" data-reveal>Built on transparency.</h2>
                <p class="mt-6 text-[17px] leading-[1.7] text-muted" data-reveal>Every press release is handled with care, from distribution to reporting. We keep you informed at every step, with clear communication and a report you can share.</p>
                <span aria-hidden="true" class="mt-8 h-0.5 w-12 bg-accent" data-reveal></span>
            </div>

            <ol class="flex flex-col gap-9" data-reveal-group>
                @foreach ($steps as [$title, $text])
                    <li class="grid grid-cols-[48px_1fr] items-start gap-5 md:gap-6" data-reveal>
                        <span class="flex size-12 items-center justify-center rounded-full border-2 border-accent/35 bg-white text-[17px] font-semibold text-accent-ink">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <div class="flex flex-col gap-1.5 pt-1.5">
                            <h3 class=" text-[19px] leading-snug font-semibold text-ink md:text-[20px]">{{ $title }}</h3>
                            <p class="max-w-[520px] text-[15px] leading-relaxed text-muted">{{ $text }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Who we work with --}}
    <section class="section-y">
        <div class="container-site flex flex-col gap-12 md:gap-14">
            <div class="flex flex-col" data-reveal-group>
                <p class="eyebrow" data-reveal>Who we work with</p>
                <h2 class="mt-5 text-[34px] leading-[1.1] font-semibold tracking-[-0.01em] text-ink md:text-[44px]" data-reveal>For anyone with news to share.</h2>
            </div>
            <ul class="grid gap-10 border-t border-line pt-10 md:grid-cols-3 md:gap-0 md:divide-x md:divide-line" data-reveal-group>
                @foreach ($audiences as [$icon, $title, $text])
                    <li class="flex items-start gap-5 {{ $loop->first ? '' : 'md:pl-8' }} {{ $loop->last ? '' : 'md:pr-8' }}" data-reveal>
                        <span class="flex size-14 shrink-0 items-center justify-center rounded-full border border-accent/15 bg-accent-soft/60 text-accent-ink" aria-hidden="true"><x-icon :name="$icon" :size="24" :stroke="1.5" /></span>
                        <div class="flex flex-col gap-2 pt-1">
                            <h3 class=" text-[19px] leading-snug font-semibold text-ink">{{ $title }}</h3>
                            <p class="text-[15px] leading-relaxed text-muted">{{ $text }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Company details + conversion card --}}
    <section class="pb-24 md:pb-28">
        <div class="container-site grid gap-6 border-t border-line pt-6 lg:grid-cols-2">
            <div class="card flex flex-col rounded-[12px] p-7 md:p-8" data-reveal>
                <span class="label-caps text-accent-ink">Company details</span>
                <h2 class="mt-3 text-[30px] leading-tight font-semibold text-ink">{{ $company }}</h2>
                <dl class="mt-5 border-t border-line">
                    @foreach ($details as [$icon, $label, $value])
                        <div class="grid grid-cols-[32px_96px_1fr] items-center gap-3 border-b border-line-soft py-4 text-[15px]">
                            <span class="text-accent-ink" aria-hidden="true"><x-icon :name="$icon" :size="20" :stroke="1.6" /></span>
                            <dt class="text-muted">{{ $label }}</dt>
                            <dd class="font-semibold text-ink [overflow-wrap:anywhere]">
                                @if ($label === 'Email')<a href="mailto:{{ $value }}" class="hover:text-accent-ink hover:underline">{{ $value }}</a>
                                @elseif ($label === 'Website')<a href="{{ config('app.url') }}" class="hover:text-accent-ink hover:underline">{{ $value }}</a>
                                @else{{ $value }}@endif
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            <div class="relative flex flex-col justify-center gap-6 overflow-hidden rounded-[12px] bg-navy-900 p-7 text-white md:p-10" data-reveal style="box-shadow: var(--shadow-elevated)">
                {{-- Soft purple light in the corner, as in the reference --}}
                <div aria-hidden="true" class="pointer-events-none absolute -top-24 -right-24 size-[360px] rounded-full" style="background: radial-gradient(circle, rgb(15 117 188 / 0.45) 0%, rgb(15 117 188 / 0.12) 45%, transparent 70%)"></div>
                <div aria-hidden="true" class="pointer-events-none absolute -right-28 -bottom-32 size-[300px] rounded-full border border-white/10"></div>
                <div class="relative flex flex-col gap-4">
                    <p class="eyebrow eyebrow-dark">Get started</p>
                    <h2 class="max-w-[420px] text-[30px] leading-[1.12] font-semibold text-white md:text-[36px]">Ready to distribute your next release?</h2>
                    <p class="max-w-[420px] text-[16px] leading-relaxed text-white/75">Compare packages, download a sample report and send us an enquiry.</p>
                </div>
                <div class="relative flex flex-col gap-3 sm:flex-row">
                    <x-button :href="route('contact')" icon="arrow-right" data-track="cta_click" data-track-label="About: Enquire Now">Enquire Now</x-button>
                    <x-button :href="route('packages.index')" variant="on-brand">View Packages</x-button>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
