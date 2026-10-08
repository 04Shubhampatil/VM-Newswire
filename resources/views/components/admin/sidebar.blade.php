@php
    $groups = [
        'Overview' => [
            ['Dashboard', 'grid', 'admin.dashboard', 'admin.dashboard'],
        ],
        'Catalogue' => [
            ['Packages', 'box', 'admin.packages.index', 'admin.packages.*'],
            ['Media Network', 'globe', 'admin.media.index', 'admin.media.*'],
            ['Sample Reports', 'file', 'admin.sample-reports.index', 'admin.sample-reports.*'],
        ],
        'Customers' => [
            ['Enquiries', 'inbox', 'admin.enquiries.index', 'admin.enquiries.*'],
        ],
        'Website' => [
            ['Website Content', 'edit', 'admin.content.edit', 'admin.content.*'],
            ['FAQs', 'info', 'admin.faqs.index', 'admin.faqs.*'],
        ],
        'System' => [
            ['Settings', 'gear', 'admin.settings.edit', 'admin.settings.*'],
            ['My Account', 'user', 'admin.account.password', 'admin.account.*'],
        ],
    ];
    // $newEnquiries is provided by App\View\Composers\AdminSidebarComposer.
@endphp
<div x-show="nav" x-cloak @click="nav = false" class="fixed inset-0 z-40 bg-navy-900/60 lg:hidden"></div>
<aside :class="nav ? 'translate-x-0' : '-translate-x-full'"
       class="fixed inset-y-0 left-0 z-50 flex w-[264px] shrink-0 -translate-x-full flex-col bg-navy-900 text-white transition-transform lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">
    <div class="flex h-16 items-center justify-between border-b border-white/10 px-5">
        <x-brand-logo dark :size="32" :href="route('admin.dashboard')" />
        <button type="button" @click="nav = false" aria-label="Close navigation" class="flex size-10 items-center justify-center rounded-[8px] text-white/70 hover:bg-white/10 hover:text-white lg:hidden"><x-icon name="x" /></button>
    </div>

    <nav aria-label="Admin" class="flex grow flex-col overflow-y-auto px-3 pb-4">
        @foreach ($groups as $group => $items)
            <p class="admin-nav-group">{{ $group }}</p>
            @foreach ($items as [$label, $icon, $route, $active])
                @php $isActive = request()->routeIs(...(array) $active); @endphp
                <a href="{{ route($route) }}" @if ($isActive) aria-current="page" @endif class="admin-nav-link">
                    <x-icon :name="$icon" :size="18" :stroke="1.8" />
                    <span class="grow">{{ $label }}</span>
                    @if ($label === 'Enquiries' && $newEnquiries)
                        <span class="rounded-full bg-teal px-2 py-0.5 text-[11px] font-bold text-navy-900" aria-label="{{ $newEnquiries }} new">{{ $newEnquiries }}</span>
                    @endif
                </a>
            @endforeach
        @endforeach
    </nav>

    <div class="flex flex-col gap-3 border-t border-white/10 p-4">
        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="flex items-center gap-2 text-[13px] text-white/60 transition hover:text-white"><x-icon name="external" :size="15" />View website</a>
        <div class="flex items-center gap-3 rounded-[10px] bg-white/5 px-3 py-2.5">
            <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-teal text-[13px] font-bold text-navy-900">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
            <a href="{{ route('admin.account.password') }}" class="flex min-w-0 grow flex-col">
                <span class="truncate text-[13px] font-semibold text-white">{{ auth()->user()->name }}</span>
                <span class="truncate text-[12px] text-white/50">{{ auth()->user()->email }}</span>
            </a>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" aria-label="Sign out" title="Sign out" class="flex size-9 items-center justify-center rounded-[8px] text-white/60 transition hover:bg-white/10 hover:text-white"><x-icon name="logout" :size="17" /></button>
            </form>
        </div>
    </div>
</aside>
