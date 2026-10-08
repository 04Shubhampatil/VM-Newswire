{{-- Flat-vector support agent with headset, laptop and speech bubble (original artwork, CSS colours). --}}
<svg {{ $attributes->merge(['class' => 'h-auto w-full max-w-[440px]']) }} viewBox="0 0 520 400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <linearGradient id="sup-jacket" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#1b86cf"/><stop offset="1" stop-color="#0f75bc"/></linearGradient>
    </defs>
    {{-- speech bubble --}}
    <g transform="translate(360 30)">
        <rect x="0" y="0" width="130" height="96" rx="14" fill="#fff" stroke="#cfe0f0" stroke-width="3"/>
        <path d="M22 96 l-4 26 l30 -26z" fill="#fff" stroke="#cfe0f0" stroke-width="3" stroke-linejoin="round"/>
        <rect x="22" y="22" width="86" height="7" rx="3.5" fill="#cfe0f0"/>
        <rect x="22" y="40" width="86" height="7" rx="3.5" fill="#cfe0f0"/>
        <rect x="22" y="58" width="60" height="7" rx="3.5" fill="#cfe0f0"/>
    </g>
    {{-- ground shadow --}}
    <ellipse cx="250" cy="372" rx="190" ry="14" fill="#edf2f6"/>
    {{-- body --}}
    <path d="M110 372 C110 290 150 250 250 250 C350 250 390 290 390 372 Z" fill="url(#sup-jacket)"/>
    <path d="M222 252 L250 320 L278 252 Z" fill="#fff"/>
    <path d="M243 252 L257 252 L262 296 L250 326 L238 296 Z" fill="#152944"/>
    <path d="M196 250 C208 262 228 270 250 268 L250 252 Z" fill="#0c5f99"/>
    <path d="M304 250 C292 262 272 270 250 268 L250 252 Z" fill="#0c5f99"/>
    {{-- neck --}}
    <rect x="232" y="214" width="36" height="42" rx="10" fill="#e8b48e"/>
    {{-- head --}}
    <circle cx="250" cy="160" r="62" fill="#f3c6a0"/>
    <path d="M190 150 C190 100 230 86 252 86 C290 86 312 104 312 146 C300 126 282 118 262 118 C240 118 216 124 190 150 Z" fill="#1f2a37"/>
    <path d="M190 150 C184 164 186 186 194 196 C192 176 194 162 198 150 Z" fill="#1f2a37"/>
    {{-- eyes, brows, smile --}}
    <ellipse cx="230" cy="160" rx="5" ry="6" fill="#1f2a37"/>
    <ellipse cx="270" cy="160" rx="5" ry="6" fill="#1f2a37"/>
    <path d="M219 146 q11 -7 22 0" stroke="#1f2a37" stroke-width="3" fill="none" stroke-linecap="round"/>
    <path d="M259 146 q11 -7 22 0" stroke="#1f2a37" stroke-width="3" fill="none" stroke-linecap="round"/>
    <path d="M232 186 q18 16 36 0" stroke="#c0392b" stroke-width="4" fill="none" stroke-linecap="round"/>
    {{-- headset --}}
    <path d="M190 150 C186 110 212 96 250 96 C288 96 314 110 310 150" stroke="#152944" stroke-width="7" fill="none" stroke-linecap="round"/>
    <rect x="180" y="146" width="16" height="30" rx="7" fill="#152944"/>
    <rect x="304" y="146" width="16" height="30" rx="7" fill="#152944"/>
    <path d="M312 176 C320 196 300 206 278 206" stroke="#152944" stroke-width="5" fill="none" stroke-linecap="round"/>
    <circle cx="276" cy="206" r="6" fill="#0f75bc"/>
    {{-- raised hand --}}
    <path d="M318 262 C338 236 352 226 356 206 C358 196 346 192 340 202 C336 214 326 226 314 240 Z" fill="#f3c6a0"/>
    {{-- laptop --}}
    <path d="M130 300 L370 300 L354 372 L146 372 Z" fill="#fff" stroke="#d5dee6" stroke-width="3"/>
    <rect x="118" y="368" width="264" height="12" rx="6" fill="#d5dee6"/>
    <circle cx="250" cy="336" r="16" fill="#e8f2fa" stroke="#9fcbea" stroke-width="3"/>
    <path d="M243 336 l5 5 l10 -11" stroke="#0f75bc" stroke-width="3.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
