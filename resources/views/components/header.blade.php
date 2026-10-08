@php
    /*
     * Enterprise header (reference layout): monogram logo · centred navigation (Contact Us included) · search.
     * "Services & Solutions" and "Resources" open small dropdowns; every link points at a registered route.
     */
    $nav = [
        ['label' => 'Newsroom', 'url' => route('media-network'), 'active' => 'media-network'],
        ['label' => 'Services & Solutions', 'active' => 'packages.*', 'children' => [
            ['Press Release Distribution', route('packages.index')],
            ['Media Network', route('media-network')],
            ['Reporting & Analytics', route('sample-reports.index')],
        ]],
        ['label' => 'Resources', 'active' => 'sample-reports.*', 'children' => [
            ['Sample Reports', route('sample-reports.index')],
            ['FAQ', route('faq')],
            ['About VM Newswire', route('about')],
        ]],
        ['label' => 'Contact Us', 'url' => route('contact'), 'active' => 'contact'],
    ];
    $email = (string) $site->get('company_email');
    $phone = $site->get('phone') ?: $site->get('whatsapp');
@endphp
<header x-data="{ open: false, search: false }"
        @keydown.escape.window="open = false; search = false"
        class="site-header sticky top-0 z-40 border-b border-line bg-white">
    <div class="container-site flex h-[72px] items-center justify-between gap-6 lg:h-[84px]">
        <x-brand-logo :size="40" class="max-sm:hidden" />
        <x-brand-logo :size="34" class="sm:hidden" />

        {{-- Centred navigation (desktop) --}}
        <nav aria-label="Main" class="hidden h-full flex-1 items-center justify-center gap-2 lg:flex">
            @foreach ($nav as $item)
                @php $isActive = $item['active'] && request()->routeIs($item['active']); @endphp
                @if (isset($item['children']))
                    <div x-data="{ menu: false }" @mouseenter="menu = true" @mouseleave="menu = false" @click.outside="menu = false" class="relative flex h-full items-center">
                        <button type="button" @click="menu = !menu" :aria-expanded="menu.toString()" aria-haspopup="true"
                                class="flex h-full items-center gap-1 px-3 text-[14px] font-medium whitespace-nowrap transition {{ $isActive ? 'text-accent-ink' : 'text-ink hover:text-accent-ink' }}">
                            {{ $item['label'] }}
                            <x-icon name="chevron-down" :size="14" :stroke="2.2" class="transition" x-bind:class="menu && 'rotate-180'" />
                        </button>
                        <div x-show="menu" x-transition.opacity.duration.150ms x-cloak
                             class="absolute top-[calc(100%-12px)] left-1/2 z-50 w-60 -translate-x-1/2 rounded-xl border border-line bg-white p-2 shadow-[var(--shadow-float)]">
                            @foreach ($item['children'] as [$label, $url])
                                <a href="{{ $url }}" class="block rounded-lg px-3 py-2.5 text-[14px] font-medium text-ink transition hover:bg-teal-soft hover:text-accent-ink">{{ $label }}</a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <a href="{{ $item['url'] }}" @if ($isActive) aria-current="page" @endif
                       class="flex h-full items-center px-3 text-[14px] font-medium whitespace-nowrap transition {{ $isActive ? 'text-accent-ink' : 'text-ink hover:text-accent-ink' }}">
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach
        </nav>

        {{-- Search + Enquire Now (desktop). The button opens the enquiry popover on pages that include it and
             falls back to the contact page elsewhere. --}}
        <div class="hidden shrink-0 items-center gap-2 lg:flex">
            <button type="button" @click="search = !search; open = false" :aria-expanded="search.toString()" aria-controls="site-search" aria-label="Search"
                    class="flex size-10 items-center justify-center rounded-full text-ink transition hover:bg-canvas-deep hover:text-accent-ink">
                <x-icon name="search" :size="18" :stroke="2" />
            </button>
            <a href="{{ route('contact') }}#enquire" @click="if (document.getElementById('enquiry-modal-title')) { $event.preventDefault(); $dispatch('open-enquiry'); }"
               class="btn-teal inline-flex h-10 items-center rounded-full px-5 text-[14px] font-semibold transition"
               data-track="cta_click" data-track-label="Header: Enquire Now">Enquire Now</a>
        </div>

        {{-- Mobile: search + menu --}}
        <div class="flex shrink-0 items-center gap-1 lg:hidden">
            <button type="button" @click="search = !search; open = false" :aria-expanded="search.toString()" aria-controls="site-search" aria-label="Search"
                    class="flex size-11 items-center justify-center rounded-full text-ink transition hover:bg-canvas-deep">
                <x-icon name="search" :size="20" />
            </button>
            <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-controls="mobile-nav" aria-label="Menu"
                    class="flex size-11 items-center justify-center rounded-full border border-line text-ink transition hover:bg-canvas-deep">
                <x-icon name="menu" :size="20" x-show="!open" />
                <x-icon name="x" :size="20" x-show="open" x-cloak />
            </button>
        </div>
    </div>

    {{-- Search panel --}}
    <div id="site-search" x-show="search" x-collapse x-cloak class="border-t border-line bg-canvas">
        <div class="container-site py-4">
            <form action="{{ route('media-network') }}" method="get" role="search" class="flex max-w-2xl flex-col gap-2 sm:flex-row">
                <label for="site-search-input" class="sr-only">Search the media network</label>
                <input id="site-search-input" type="search" name="q" maxlength="100" required
                       placeholder="Search media outlets by name…" autocomplete="off"
                       class="field-input h-12 flex-1">
                <button type="submit" class="btn btn-primary h-12 shrink-0 px-6">Search</button>
            </form>
        </div>
    </div>

    {{-- Mobile navigation (slide-down) --}}
    <nav id="mobile-nav" aria-label="Main" x-show="open" x-collapse x-cloak
         class="max-h-[calc(100dvh-72px)] overflow-y-auto border-t border-line bg-white shadow-[0_24px_40px_-28px_rgba(11,22,40,0.45)] lg:hidden">
        <div class="container-site flex flex-col gap-1 py-5">
            @foreach ($nav as $item)
                @if (isset($item['children']))
                    <div x-data="{ sub: false }">
                        <button type="button" @click="sub = !sub" :aria-expanded="sub.toString()"
                                class="flex min-h-12 w-full items-center justify-between gap-4 rounded-xl px-3 text-[17px] font-medium text-ink transition hover:bg-canvas">
                            {{ $item['label'] }}
                            <x-icon name="chevron-down" :size="18" class="text-muted-soft transition" x-bind:class="sub && 'rotate-180'" />
                        </button>
                        <div x-show="sub" x-collapse x-cloak class="mb-1 ml-3 flex flex-col border-l border-line pl-3">
                            @foreach ($item['children'] as [$label, $url])
                                <a href="{{ $url }}" @click="open = false" class="flex min-h-11 items-center text-[15px] text-muted transition hover:text-accent-ink">{{ $label }}</a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <a href="{{ $item['url'] }}" @click="open = false"
                       class="group flex min-h-12 items-center justify-between gap-4 rounded-xl px-3 text-[17px] font-medium text-ink transition hover:bg-canvas">
                        {{ $item['label'] }}
                        <x-icon name="arrow-right" :size="18" class="arrow-nudge text-muted-soft" />
                    </a>
                @endif
            @endforeach

            <a href="{{ route('contact') }}#enquire" @click="open = false; if (document.getElementById('enquiry-modal-title')) { $event.preventDefault(); $dispatch('open-enquiry'); }"
               class="btn-teal mt-3 flex min-h-12 w-full items-center justify-center rounded-full text-[15px] font-semibold"
               data-track="cta_click" data-track-label="Header: Enquire Now (mobile)">Enquire Now</a>

            <address class="mt-3 flex flex-col gap-2 border-t border-line pt-4 text-[14px] text-muted not-italic">
                @if ($phone)
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="flex items-center gap-2"><x-icon name="phone" :size="16" />{{ $phone }}</a>
                @endif
                @if ($email)
                    <a href="mailto:{{ $email }}" class="flex min-w-0 items-center gap-2 truncate"><x-icon name="mail" :size="16" />{{ $email }}</a>
                @endif
            </address>
        </div>
    </nav>
</header>
