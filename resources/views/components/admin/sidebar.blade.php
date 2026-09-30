@php
    $items = [
        ['Dashboard', 'grid', 'admin.dashboard', 'admin.dashboard'],
        ['Packages', 'box', 'admin.packages.index', 'admin.packages.*'],
        ['Media Network', 'globe', 'admin.media.index', 'admin.media.*'],
        ['Sample Reports', 'file', 'admin.sample-reports.index', 'admin.sample-reports.*'],
        ['Enquiries', 'inbox', 'admin.enquiries.index', 'admin.enquiries.*'],
        ['Website Content', 'edit', 'admin.content.edit', ['admin.content.*', 'admin.faqs.*']],
        ['Settings', 'gear', 'admin.settings.edit', 'admin.settings.*'],
    ];
    // $newEnquiries is provided by App\View\Composers\AdminSidebarComposer.
@endphp
<div x-show="nav" x-cloak @click="nav = false" class="fixed inset-0 z-40 bg-brand/50 lg:hidden"></div>
<aside :class="nav ? 'translate-x-0' : '-translate-x-full'"
       class="fixed inset-y-0 left-0 z-50 flex w-[260px] shrink-0 -translate-x-full flex-col justify-between bg-ink px-4 py-6 text-white transition-transform lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">
    <div class="flex flex-col gap-8">
        <div class="flex items-center justify-between px-2">
            <x-logo dark :size="26" />
            <button type="button" @click="nav = false" aria-label="Close navigation" class="flex size-11 items-center justify-center lg:hidden"><x-icon name="x" /></button>
        </div>
        <nav aria-label="Admin" class="flex flex-col gap-1">
            @foreach ($items as [$label, $icon, $route, $active])
                @php $isActive = request()->routeIs(...(array) $active); @endphp
                <a href="{{ route($route) }}" @if ($isActive) aria-current="page" @endif
                   class="flex h-11 items-center gap-3 rounded-[6px] px-3.5 text-[15px] transition {{ $isActive ? 'bg-white/10 font-semibold text-white' : 'text-muted-on-brand hover:bg-white/10 hover:text-white' }}">
                    <x-icon :name="$icon" />
                    <span class="grow">{{ $label }}</span>
                    @if ($label === 'Enquiries' && $newEnquiries)
                        <span class="rounded-full bg-accent-fill px-2 py-0.5 text-xs text-white" aria-label="{{ $newEnquiries }} new">{{ $newEnquiries }}</span>
                    @endif
                </a>
            @endforeach
        </nav>
    </div>
    <div class="flex flex-col gap-3 border-t border-white/15 px-2 pt-4">
        <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-2 text-sm text-muted-on-brand hover:text-white"><x-icon name="external" :size="16" />View website</a>
        <div class="flex items-center gap-3">
            <span class="flex size-9 items-center justify-center rounded-full bg-accent-fill text-sm font-semibold">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
            <a href="{{ route('admin.account.password') }}" class="flex min-w-0 grow flex-col">
                <span class="truncate text-sm font-medium">{{ auth()->user()->name }}</span>
                <span class="truncate text-xs text-muted-on-brand">{{ auth()->user()->email }}</span>
            </a>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" aria-label="Sign out" class="flex size-11 items-center justify-center text-muted-on-brand hover:text-white"><x-icon name="logout" /></button>
            </form>
        </div>
    </div>
</aside>
