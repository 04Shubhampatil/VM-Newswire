@php
    /*
     * Reference-style footer: white ground, five compact link columns, then a bottom row with the logo,
     * copyright, legal links and social icons. Every link points at a registered route; items with no
     * destination in this project (careers, blog, cookie policy, accessibility) are left out rather than
     * linked to dead URLs. No postal address by design.
     */
    // Social icons are always shown; the URLs come from Admin → Settings (an unset one links to the contact page).
    $socials = collect([
        ['label' => 'LinkedIn', 'url' => $site->get('social_linkedin'), 'icon' => '<path d="M6.94 8.5H3.56V20h3.38V8.5zM5.25 3a1.97 1.97 0 1 0 0 3.94 1.97 1.97 0 0 0 0-3.94zM20.44 13.03c0-3.28-1.75-4.8-4.09-4.8-1.89 0-2.73 1.04-3.2 1.77V8.5H9.78c.04.95 0 11.5 0 11.5h3.37v-6.42c0-.34.03-.69.13-.93.24-.6.8-1.23 1.73-1.23 1.22 0 1.71.93 1.71 2.3V20h3.37l.35-6.97z"/>'],
        ['label' => 'X', 'url' => $site->get('social_x'), 'icon' => '<path d="M17.75 3h3.07l-6.71 7.67L22 21h-6.18l-4.84-6.33L5.44 21H2.37l7.18-8.2L2 3h6.34l4.37 5.78L17.75 3zm-1.08 16.16h1.7L7.4 4.74H5.57l11.1 14.42z"/>'],
        ['label' => 'Facebook', 'url' => $site->get('social_facebook'), 'icon' => '<path d="M13.5 21v-7.5h2.52l.38-2.93H13.5V8.7c0-.85.24-1.43 1.45-1.43h1.55V4.65c-.27-.04-1.19-.12-2.26-.12-2.23 0-3.76 1.36-3.76 3.87v2.17H7.96v2.93h2.52V21h3.02z"/>'],
        ['label' => 'Instagram', 'url' => $site->get('social_instagram'), 'icon' => '<path d="M12 7.3a4.7 4.7 0 1 0 0 9.4 4.7 4.7 0 0 0 0-9.4zm0 7.75a3.05 3.05 0 1 1 0-6.1 3.05 3.05 0 0 1 0 6.1zM17.9 7.1a1.1 1.1 0 1 1-2.2 0 1.1 1.1 0 0 1 2.2 0zM12 3.6c2.73 0 3.06.01 4.13.06 1 .05 1.54.21 1.9.35.48.19.82.41 1.18.77.36.36.58.7.77 1.18.14.36.3.9.35 1.9.05 1.07.06 1.4.06 4.13s-.01 3.06-.06 4.13c-.05 1-.21 1.54-.35 1.9-.19.48-.41.82-.77 1.18-.36.36-.7.58-1.18.77-.36.14-.9.3-1.9.35-1.07.05-1.4.06-4.13.06s-3.06-.01-4.13-.06c-1-.05-1.54-.21-1.9-.35a3.17 3.17 0 0 1-1.18-.77 3.17 3.17 0 0 1-.77-1.18c-.14-.36-.3-.9-.35-1.9C3.61 15.06 3.6 14.73 3.6 12s.01-3.06.06-4.13c.05-1 .21-1.54.35-1.9.19-.48.41-.82.77-1.18.36-.36.7-.58 1.18-.77.36-.14.9-.3 1.9-.35C8.94 3.61 9.27 3.6 12 3.6zM12 2c-2.78 0-3.13.01-4.22.06-1.09.05-1.83.22-2.48.48a5 5 0 0 0-1.81 1.18 5 5 0 0 0-1.18 1.81c-.26.65-.43 1.39-.48 2.48C2.01 8.87 2 9.22 2 12s.01 3.13.06 4.22c.05 1.09.22 1.83.48 2.48a5 5 0 0 0 1.18 1.81 5 5 0 0 0 1.81 1.18c.65.26 1.39.43 2.48.48C8.87 21.99 9.22 22 12 22s3.13-.01 4.22-.06c1.09-.05 1.83-.22 2.48-.48a5 5 0 0 0 1.81-1.18 5 5 0 0 0 1.18-1.81c.26-.65.43-1.39.48-2.48.05-1.09.06-1.44.06-4.22s-.01-3.13-.06-4.22c-.05-1.09-.22-1.83-.48-2.48a5 5 0 0 0-1.18-1.81 5 5 0 0 0-1.81-1.18c-.65-.26-1.39-.43-2.48-.48C15.13 2.01 14.78 2 12 2z"/>'],
        ['label' => 'YouTube', 'url' => $site->get('social_youtube'), 'icon' => '<path d="M23.5 7.2a3 3 0 0 0-2.1-2.1C19.5 4.6 12 4.6 12 4.6s-7.5 0-9.4.5A3 3 0 0 0 .5 7.2 31 31 0 0 0 0 12a31 31 0 0 0 .5 4.8 3 3 0 0 0 2.1 2.1c1.9.5 9.4.5 9.4.5s7.5 0 9.4-.5a3 3 0 0 0 2.1-2.1A31 31 0 0 0 24 12a31 31 0 0 0-.5-4.8zM9.6 15.6V8.4l6.2 3.6-6.2 3.6z"/>'],
    ])->map(fn (array $social) => $social + ['href' => filled($social['url']) ? $social['url'] : route('contact')]);

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
<footer class="site-footer">
    <div class="container-site pt-14 pb-10 lg:pt-16">
        <div class="grid grid-cols-2 gap-x-8 gap-y-10 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($columns as $title => $links)
                <nav aria-labelledby="footer-{{ Str::slug($title) }}">
                    <h2 id="footer-{{ Str::slug($title) }}" class="site-footer-title mb-4 text-[13px] font-semibold">{{ $title }}</h2>
                    <ul class="space-y-2.5">
                        @foreach ($links as [$label, $url])
                            <li><a href="{{ $url }}" class="site-footer-link text-[14px] transition">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </nav>
            @endforeach
        </div>
    </div>

    <div class="container-site">
        <div class="site-footer-bottom flex flex-col gap-6 py-8 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-col gap-3 lg:max-w-sm">
                <x-brand-logo white :size="36" />
                @if ($site->get('footer_text'))
                    <p class="text-[13px] leading-relaxed">{{ $site->get('footer_text') }}</p>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-[13px]">
                <p>&copy; {{ now()->year }} {{ $site->get('company_name') }}. All rights reserved.</p>
                <a href="{{ route('privacy') }}" class="site-footer-link transition">Privacy Policy</a>
                <a href="{{ route('terms') }}" class="site-footer-link transition">Terms of Use</a>
            </div>
            <ul class="flex items-center gap-2.5" aria-label="Social media">
                @foreach ($socials as $social)
                    <li>
                        <a href="{{ $social['href'] }}" @if (filled($social['url'])) target="_blank" rel="noopener" @endif aria-label="{{ $social['label'] }}" title="{{ $social['label'] }}" class="site-footer-social">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="17" height="17" fill="currentColor" aria-hidden="true">{!! $social['icon'] !!}</svg>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</footer>
