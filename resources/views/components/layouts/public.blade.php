@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'ogImage' => null,
    'noindex' => false,
    'event' => null, // ['name' => 'package_view', ...] analytics event fired on load
])
@php
    $company = $site->get('company_name');
    $fullTitle = $title ? (str_contains($title, $company) ? $title : "{$title} | {$company}") : "{$company} — Global Press Release Distribution";
    $metaDescription = $description ?: 'Distribute your press release across leading news platforms, business publications and digital media networks with transparent packages and professional reporting.';
    $canonicalUrl = $canonical ?: url()->current();
    $ogImageUrl = $ogImage ?: asset('images/og-default.png');
    $ga = config('vmnewswire.ga4_measurement_id');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Enables scroll reveals only when JS runs, so content is never hidden without it. --}}
    <script>document.documentElement.classList.add('js')</script>

    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    @if ($noindex)<meta name="robots" content="noindex, nofollow">@endif

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $company }}">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $ogImageUrl }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $fullTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $ogImageUrl }}">

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <meta name="theme-color" content="#7C3AED">

    @if ($event)
        <meta name="vmn-event" content="{{ json_encode($event) }}">
    @endif

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if ($ga)
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($ga) }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', @json($ga));
        </script>
    @endif

    @if (config('vmnewswire.turnstile.site_key'))
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif

    @stack('schema')
</head>
<body class="min-h-screen bg-canvas">
    <div class="scroll-progress" aria-hidden="true"></div>
    <a href="#main" class="sr-only z-50 rounded bg-ink px-4 py-2 text-white focus:not-sr-only focus:fixed focus:top-3 focus:left-3">Skip to content</a>

    <x-header />

    <main id="main">
        {{ $slot }}
    </main>

    <x-footer />
</body>
</html>
