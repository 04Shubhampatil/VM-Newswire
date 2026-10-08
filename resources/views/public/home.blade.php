<x-layouts.public>
    @push('schema')
        <script type="application/ld+json">{!! json_encode([
            '@context' => 'https://schema.org',
            '@graph' => [
                array_filter([
                    '@type' => 'Organization',
                    'name' => $site->get('company_name'),
                    'url' => route('home'),
                    'email' => $site->get('company_email'),
                    'telephone' => $site->get('phone') ?: null,
                    'logo' => asset('favicon.svg'),
                    'sameAs' => array_values(array_filter([$site->get('social_linkedin'), $site->get('social_x'), $site->get('social_facebook')])) ?: null,
                ]),
                ['@type' => 'WebSite', 'name' => $site->get('company_name'), 'url' => route('home')],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endpush

    <x-home.hero-newswire />
    <x-home.news-preview :outlets="$highlighted" />
    <x-home.value-props />
    <x-home.packages :packages="$packages" :brands="$brands" />
    <x-home.confidence />
    <x-home.brands :outlets="$highlighted" />
    <x-home.solutions />
    <x-home.for-journalists />
    <x-home.final-cta />
    <x-enquiry-modal :packages="$packages" />
</x-layouts.public>
