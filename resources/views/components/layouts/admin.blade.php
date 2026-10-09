@props(['title', 'description' => null, 'breadcrumb' => null, 'breadcrumbs' => []])
{{--
    Admin shell: grouped navy sidebar, white top bar with breadcrumbs, then a page header (title, description,
    actions slot) above the page content. `breadcrumbs` is a list of [label, url|null]; the legacy `breadcrumb`
    string is still accepted.
--}}
@php
    if (! $breadcrumbs && $breadcrumb) {
        $breadcrumbs = collect(explode('/', $breadcrumb))->map(fn ($part) => [trim($part), null])->filter(fn ($p) => $p[0] !== 'Admin')->values()->all();
    }
    $breadcrumbs = array_merge([['Dashboard', route('admin.dashboard')]], $breadcrumbs);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · Admin · {{ $site->get('company_name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}?v=2" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon.ico') }}?v=2" sizes="16x16 32x32 48x48">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @include('partials.head-assets')
</head>
<body class="site-admin min-h-screen bg-canvas text-ink" x-data="{ nav: false }" @keydown.escape.window="nav = false">
    <div class="flex min-h-screen">
        <x-admin.sidebar />

        <div class="flex min-w-0 grow flex-col">
            <header class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b border-line bg-white/95 px-4 backdrop-blur md:px-8">
                <button type="button" @click="nav = true" aria-label="Open navigation" class="admin-icon-btn size-10 lg:hidden"><x-icon name="menu" :size="20" /></button>

                <nav aria-label="Breadcrumb" class="min-w-0 flex-1">
                    <ol class="flex items-center gap-1.5 text-[13px] text-muted">
                        @foreach ($breadcrumbs as [$label, $url])
                            <li class="flex min-w-0 items-center gap-1.5">
                                @if (! $loop->first)<x-icon name="chevron-right" :size="13" class="shrink-0 text-muted-soft" />@endif
                                @if ($url && ! $loop->last)
                                    <a href="{{ $url }}" class="truncate transition hover:text-heading">{{ $label }}</a>
                                @else
                                    <span class="truncate {{ $loop->last ? 'font-semibold text-heading' : '' }}" @if ($loop->last) aria-current="page" @endif>{{ $label }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>

                <div class="flex shrink-0 items-center gap-2">
                    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="hidden h-9 items-center gap-1.5 rounded-[8px] border border-line px-3 text-[13px] font-medium text-ink transition hover:border-[#b9c7d4] md:inline-flex">
                        <x-icon name="external" :size="14" /> View website
                    </a>
                    <a href="{{ route('admin.account.password') }}" class="flex size-9 items-center justify-center rounded-full bg-navy-900 text-[13px] font-bold text-white" aria-label="My account" title="{{ auth()->user()->name }}">
                        {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    </a>
                </div>
            </header>

            <main class="flex grow flex-col gap-6 p-4 md:p-8">
                <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div class="min-w-0">
                        <h1 class="text-[24px] leading-tight font-bold tracking-[-0.01em] text-heading">{{ $title }}</h1>
                        @if ($description)<p class="mt-1 max-w-2xl text-[14px] text-muted">{{ $description }}</p>@endif
                    </div>
                    @isset($actions)
                        <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $actions }}</div>
                    @endisset
                </div>

                {{ $slot }}
            </main>
        </div>
    </div>

    <x-admin.toast />
</body>
</html>
