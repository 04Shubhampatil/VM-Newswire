{{--
    Pre-compiled site assets (no Node/Vite at runtime): the Inter font preloads, the self-hosted fonts stylesheet,
    the compiled Tailwind stylesheet and the Alpine bundle. Query strings carry the file's modification time so
    browsers pick up replacements. Sources live in resources/css and resources/js.
--}}
@php
    $versioned = fn (string $path) => asset($path).'?v='.(@filemtime(public_path($path)) ?: 1);
@endphp
@foreach (['inter-400-normal-C38fXH4l', 'inter-500-normal-Cerq10X2', 'inter-600-normal-LgqL8muc', 'inter-700-normal-Yt3aPRUw'] as $font)
    <link rel="preload" as="font" type="font/woff2" crossorigin href="{{ asset('fonts/'.$font.'.woff2') }}">
@endforeach
<link rel="stylesheet" href="{{ $versioned('css/fonts.css') }}">
<link rel="stylesheet" href="{{ $versioned('css/app.css') }}">
<script src="{{ $versioned('js/app.js') }}" defer></script>
