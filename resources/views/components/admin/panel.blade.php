@props(['title' => null, 'description' => null, 'flush' => false])
{{-- White card with an optional header row (title, description, actions slot). `flush` removes body padding. --}}
<section {{ $attributes->merge(['class' => 'rounded-card border border-line bg-white shadow-card']) }}>
    @if ($title || isset($actions))
        <div class="flex flex-col gap-3 border-b border-line-soft px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                @if ($title)<h2 class="admin-panel-title">{{ $title }}</h2>@endif
                @if ($description)<p class="mt-1 text-[13px] text-muted">{{ $description }}</p>@endif
            </div>
            @isset($actions)<div class="flex shrink-0 flex-wrap items-center gap-2">{{ $actions }}</div>@endisset
        </div>
    @endif
    <div class="{{ $flush ? '' : 'flex flex-col gap-5 p-6' }}">
        {{ $slot }}
    </div>
</section>
