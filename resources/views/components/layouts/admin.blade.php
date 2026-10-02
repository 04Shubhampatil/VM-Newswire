@props(['title', 'breadcrumb' => null])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · Admin · {{ $site->get('company_name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}" sizes="16x16 32x32 48x48">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F6F5FA] text-ink" x-data="{ nav: false }">
    <div class="flex min-h-screen">
        <x-admin.sidebar />

        <div class="flex min-w-0 grow flex-col">
            <header class="sticky top-0 z-30 flex h-[72px] items-center justify-between gap-4 border-b border-[#E6E4EF] bg-white px-5 md:px-10">
                <div class="flex min-w-0 items-center gap-3">
                    <button type="button" @click="nav = true" aria-label="Open navigation" class="flex size-11 items-center justify-center rounded-[6px] border border-[#E6E4EF] lg:hidden"><x-icon name="menu" :size="20" /></button>
                    <div class="flex min-w-0 flex-col">
                        @if ($breadcrumb)<span class="truncate font-mono text-xs text-muted">{{ $breadcrumb }}</span>@endif
                        <h1 class="truncate text-xl font-semibold">{{ $title }}</h1>
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-2.5">
                    {{ $actions ?? '' }}
                </div>
            </header>

            <main class="flex flex-col gap-6 p-5 md:p-10">
                {{ $slot }}
            </main>
        </div>
    </div>

    <x-admin.toast />
</body>
</html>
