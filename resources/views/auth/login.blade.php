<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in · {{ $site->get('company_name') }} Admin</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}?v=2" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon.ico') }}?v=2" sizes="16x16 32x32 48x48">
    @include('partials.head-assets')
</head>
<body class="site-admin min-h-screen bg-canvas text-ink">
    <div class="grid min-h-screen lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)]">
        <aside class="relative hidden flex-col justify-between overflow-hidden bg-navy-900 p-10 text-white lg:flex">
            <svg class="pointer-events-none absolute -right-24 -bottom-24 h-[420px] w-[420px] text-teal/20" viewBox="0 0 400 400" fill="none" aria-hidden="true">
                <circle cx="200" cy="200" r="180" stroke="currentColor" stroke-width="1.5" />
                <circle cx="200" cy="200" r="120" stroke="currentColor" stroke-width="1.5" />
            </svg>
            <x-brand-logo dark :size="40" />
            <div class="relative flex flex-col gap-6">
                <h2 class="text-[32px] leading-[1.15] font-bold tracking-[-0.02em]">Manage {{ $site->get('company_name') }} from one place.</h2>
                <ul class="flex flex-col gap-3 text-[15px] text-white/75">
                    @foreach (['Packages, pricing and sample reports', 'The media network and homepage logos', 'Enquiries, with email delivery and status tracking', 'Website copy, photos, FAQs and legal pages'] as $line)
                        <li class="flex items-start gap-3"><span class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-teal text-navy-900"><x-icon name="check" :size="12" :stroke="3" /></span>{{ $line }}</li>
                    @endforeach
                </ul>
            </div>
            <a href="{{ route('home') }}" class="relative text-[13px] text-white/60 transition hover:text-white">← Back to website</a>
        </aside>

        <main class="flex items-center justify-center px-5 py-12">
            <div class="w-full max-w-[400px]">
                <x-brand-logo :size="36" class="mb-8 lg:hidden" />
                <h1 class="text-[26px] font-bold tracking-[-0.01em] text-heading">Sign in</h1>
                <p class="mt-1 mb-7 text-[14px] text-muted">Use your admin username or email.</p>

                @if (session('status'))
                    <x-admin.alert type="success" class="mb-5">{{ session('status') }}</x-admin.alert>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
                    @csrf
                    <div>
                        <label for="login" class="admin-label">Username or email</label>
                        <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus autocomplete="username"
                               class="admin-input h-11 @error('login') border-danger @enderror" @error('login') aria-invalid="true" aria-describedby="login-error" @enderror>
                        @error('login')<p id="login-error" class="admin-help text-danger">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password" class="admin-label">Password</label>
                        <input id="password" name="password" type="password" required autocomplete="current-password" class="admin-input h-11">
                        @error('password')<p class="admin-help text-danger">{{ $message }}</p>@enderror
                    </div>
                    <label class="flex items-center gap-2.5 text-[14px]">
                        <input type="checkbox" name="remember" value="1" class="size-4 accent-navy-900"> Keep me signed in
                    </label>
                    <button type="submit" class="btn btn-primary h-11 w-full">Sign in</button>
                </form>
                <a href="{{ route('home') }}" class="mt-8 block text-center text-[13px] text-muted transition hover:text-heading lg:hidden">← Back to website</a>
            </div>
        </main>
    </div>
</body>
</html>
