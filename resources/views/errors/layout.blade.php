<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') · {{ config('app.name') }}</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    {{-- Self-contained styles: error pages must render even if assets or the database are unavailable. --}}
    <style>
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh;display:flex;flex-direction:column;background:#FFFFFF;color:#111827;font-family:Inter,'Helvetica Neue',Arial,sans-serif;-webkit-font-smoothing:antialiased}
        header{padding:20px 24px;border-bottom:1px solid #E6E4EF}
        header a{display:inline-flex;align-items:center;gap:10px;color:#111827;text-decoration:none;font:600 22px/1 Inter,'Helvetica Neue',Arial,sans-serif}
        main{flex:1;display:flex;align-items:center;padding:48px 24px}
        .wrap{max-width:720px;margin:0 auto;display:flex;flex-direction:column;gap:20px}
        .code{font:500 13px/1 ui-monospace,Menlo,monospace;letter-spacing:.1em;color:#6D28D9}
        h1{margin:0;font:500 clamp(40px,7vw,72px)/1 Inter,'Helvetica Neue',Arial,sans-serif;letter-spacing:-.02em}
        p{margin:0;font-size:18px;line-height:1.6;color:#5F6673}
        .actions{display:flex;flex-wrap:wrap;gap:12px;padding-top:8px}
        .btn{display:inline-flex;align-items:center;height:52px;padding:0 24px;border-radius:6px;font-weight:600;font-size:15px;text-decoration:none}
        .primary{background:#7C3AED;color:#fff}.primary:hover{background:#6D28D9}
        .secondary{border:1px solid #B9AEE8;color:#111827}.secondary:hover{border-color:#111827;background:#fff}
        a:focus-visible{outline:2px solid #7C3AED;outline-offset:2px}
    </style>
</head>
<body>
    <header>
        <a href="/">
            <svg width="28" height="28" viewBox="0 0 32 32" aria-hidden="true"><rect width="32" height="32" rx="5" fill="#7C3AED"/><path d="M8 11h16M8 16h16" stroke="#FFFFFF" stroke-width="2.2" stroke-linecap="round"/><path d="M8 21h10" stroke="#7C3AED" stroke-width="2.2" stroke-linecap="round"/></svg>
            {{ config('app.name') }}
        </a>
    </header>
    <main>
        <div class="wrap">
            <span class="code">ERROR @yield('code')</span>
            <h1>@yield('heading')</h1>
            <p>@yield('message')</p>
            <div class="actions">
                <a href="/" class="btn primary">Back to home</a>
                <a href="/packages" class="btn secondary">View packages</a>
            </div>
        </div>
    </main>
</body>
</html>
