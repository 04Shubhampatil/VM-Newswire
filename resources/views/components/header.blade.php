@php
    $nav = [
        ['label' => 'Home', 'url' => route('home'), 'active' => 'home'],
        ['label' => 'Packages', 'url' => route('packages.index'), 'active' => 'packages.*'],
        ['label' => 'Media Network', 'url' => route('media-network'), 'active' => 'media-network'],
        ['label' => 'How It Works', 'url' => route('home').'#how-it-works', 'active' => null],
        ['label' => 'About', 'url' => route('about'), 'active' => 'about'],
        ['label' => 'Contact', 'url' => route('contact'), 'active' => 'contact'],
    ];
@endphp
<header x-data="{ open: false }" @keydown.escape.window="open = false"
        class="site-header sticky top-0 z-40 border-b border-transparent" :class="open && 'bg-canvas border-line'">
    <div class="container-site flex h-16 items-center justify-between gap-6 lg:h-[76px]">
        <x-logo class="hidden sm:flex" />
        <x-logo class="sm:hidden" :size="26" />

        <nav aria-label="Main" class="hidden h-full items-center gap-7 lg:flex xl:gap-9">
            @foreach ($nav as $item)
                @php $isActive = $item['active'] && request()->routeIs($item['active']); @endphp
                <a href="{{ $item['url'] }}" @if ($isActive) aria-current="page" @endif
                   class="nav-link py-1.5 text-sm whitespace-nowrap transition-colors duration-300 {{ $isActive ? 'font-bold text-ink' : 'font-medium text-muted hover:text-ink' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2.5">
            <x-button :href="route('packages.index')" variant="secondary" size="sm" class="hidden xl:inline-flex" data-track="cta_click" data-track-label="Header: View Packages">View Packages</x-button>
            <x-button :href="route('contact')" size="sm" data-track="cta_click" data-track-label="Header: Enquire Now">
                <span class="sm:hidden">Enquire</span><span class="hidden sm:inline">Enquire Now</span>
            </x-button>
            <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-controls="mobile-nav" aria-label="Menu"
                    class="flex size-11 items-center justify-center rounded-[6px] border border-line bg-white text-ink lg:hidden">
                <x-icon name="menu" :size="20" x-show="!open" />
                <x-icon name="x" :size="20" x-show="open" x-cloak />
            </button>
        </div>
    </div>

    <nav id="mobile-nav" aria-label="Main" x-show="open" x-collapse x-cloak
         class="border-t border-line bg-canvas shadow-[0_24px_32px_-24px_rgba(27,27,47,0.35)] lg:hidden">
        <div class="container-site flex flex-col pb-6">
            @foreach ($nav as $item)
                <a href="{{ $item['url'] }}" @click="open = false"
                   class="group flex h-14 items-center justify-between border-b border-line-soft font-display text-[26px] font-medium {{ $item['active'] && request()->routeIs($item['active']) ? 'text-accent-ink' : 'text-ink' }}">
                    {{ $item['label'] }} <x-icon name="arrow-right" class="arrow-nudge" />
                </a>
            @endforeach
            <x-button :href="route('packages.index')" variant="secondary" class="mt-5 w-full">View Packages</x-button>
        </div>
    </nav>
</header>
