@php
    /*
     * Reference-style footer: white ground, five compact link columns, then a bottom row with the logo,
     * copyright, legal links and social icons. Every link points at a registered route; items with no
     * destination in this project (careers, blog, cookie policy, accessibility) are left out rather than
     * linked to dead URLs. No postal address by design.
     */
    $socials = collect([
        ['label' => 'LinkedIn', 'url' => $site->get('social_linkedin'), 'icon' => '<path d="M6.94 8.5H3.56V20h3.38V8.5zM5.25 3a1.97 1.97 0 1 0 0 3.94 1.97 1.97 0 0 0 0-3.94zM20.44 13.03c0-3.28-1.75-4.8-4.09-4.8-1.89 0-2.73 1.04-3.2 1.77V8.5H9.78c.04.95 0 11.5 0 11.5h3.37v-6.42c0-.34.03-.69.13-.93.24-.6.8-1.23 1.73-1.23 1.22 0 1.71.93 1.71 2.3V20h3.37l.35-6.97z"/>'],
        ['label' => 'X', 'url' => $site->get('social_x'), 'icon' => '<path d="M17.75 3h3.07l-6.71 7.67L22 21h-6.18l-4.84-6.33L5.44 21H2.37l7.18-8.2L2 3h6.34l4.37 5.78L17.75 3zm-1.08 16.16h1.7L7.4 4.74H5.57l11.1 14.42z"/>'],
        ['label' => 'Facebook', 'url' => $site->get('social_facebook'), 'icon' => '<path d="M13.5 21v-7.5h2.52l.38-2.93H13.5V8.7c0-.85.24-1.43 1.45-1.43h1.55V4.65c-.27-.04-1.19-.12-2.26-.12-2.23 0-3.76 1.36-3.76 3.87v2.17H7.96v2.93h2.52V21h3.02z"/>'],
    ])->filter(fn (array $social) => filled($social['url']));

    $columns = [
        'Company' => [
            ['About VM Newswire', route('about')],
            ['Contact', route('contact')],
            ['Sign In', route('login')],
        ],
        'Services' => [
            ['Press Release Distribution', route('packages.index')],
            ['Media Network', route('media-network')],
            ['Reporting & Analytics', route('sample-reports.index')],
            ['Send an Enquiry', route('contact').'#enquire'],
        ],
        'Solutions' => [
            ['PR Professionals', route('packages.index')],
            ['IR Professionals', route('media-network', ['category' => 'Finance'])],
            ['Agencies', route('contact')],
            ['Public Companies', route('media-network', ['category' => 'Markets'])],
            ['Industry Solutions', route('media-network')],
        ],
        'Newsroom' => collect(config('vmnewswire.media_categories'))
            ->take(5)
            ->map(fn (string $category) => [$category, route('media-network', ['category' => $category])])
            ->values()
            ->all(),
        'Resources' => [
            ['For Journalists', route('home').'#journalists'],
            ['Sample Reports', route('sample-reports.index')],
            ['FAQ', route('faq')],
        ],
    ];
@endphp
<footer class="border-t border-line bg-white text-muted">
    <div class="container-site pt-14 pb-10 lg:pt-16">
        <div class="grid grid-cols-2 gap-x-8 gap-y-10 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($columns as $title => $links)
                <nav aria-labelledby="footer-{{ Str::slug($title) }}">
                    <h2 id="footer-{{ Str::slug($title) }}" class="mb-4 text-[13px] font-semibold text-muted-soft">{{ $title }}</h2>
                    <ul class="space-y-2.5">
                        @foreach ($links as [$label, $url])
                            <li><a href="{{ $url }}" class="text-[14px] text-ink transition hover:text-accent-ink">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </nav>
            @endforeach
        </div>
    </div>

    <div class="container-site">
        <div class="flex flex-col gap-6 border-t border-line py-8 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-col gap-3 lg:max-w-sm">
                <x-brand-logo :size="36" />
                @if ($site->get('footer_text'))
                    <p class="text-[13px] leading-relaxed">{{ $site->get('footer_text') }}</p>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-[13px]">
                <p>&copy; {{ now()->year }} {{ $site->get('company_name') }}. All rights reserved.</p>
                <a href="{{ route('privacy') }}" class="transition hover:text-accent-ink">Privacy Policy</a>
                <a href="{{ route('terms') }}" class="transition hover:text-accent-ink">Terms of Use</a>
            </div>
            @if ($socials->isNotEmpty())
                <ul class="flex items-center gap-3" aria-label="Social media">
                    @foreach ($socials as $social)
                        <li>
                            <a href="{{ $social['url'] }}" target="_blank" rel="noopener" aria-label="{{ $social['label'] }}"
                               class="flex size-9 items-center justify-center rounded-full text-heading transition hover:bg-teal-soft hover:text-accent-ink">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true">{!! $social['icon'] !!}</svg>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</footer>
