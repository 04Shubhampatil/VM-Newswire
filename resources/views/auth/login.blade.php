<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in · {{ $site->get('company_name') }} Admin</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-brand px-5 py-12">
    <main class="flex w-full max-w-[420px] flex-col gap-8">
        <x-logo dark class="self-center" />
        <div class="rounded-card bg-white p-8 md:p-10">
            <h1 class="display mb-1 text-4xl">Admin sign in</h1>
            <p class="mb-7 text-sm text-muted">Manage packages, media, reports and enquiries.</p>

            @if (session('status'))
                <p role="status" class="mb-5 rounded-[6px] bg-success-soft px-4 py-3 text-sm text-success-ink">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
                @csrf
                <div>
                    <label for="email" class="field-label">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                           class="field-input @error('email') border-danger @enderror" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                    @error('email')<p id="email-error" class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" class="field-label">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" class="field-input">
                    @error('password')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <label class="flex items-center gap-2.5 text-sm">
                    <input type="checkbox" name="remember" value="1" class="size-4 accent-accent-fill"> Keep me signed in
                </label>
                <button type="submit" class="btn btn-primary w-full">Sign in</button>
            </form>
        </div>
        <a href="{{ route('home') }}" class="self-center text-sm text-muted-on-brand hover:text-white">← Back to website</a>
    </main>
</body>
</html>
