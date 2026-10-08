@props(['items'])
{{-- Horizontal page tabs. $items: list of [label, url, active(bool), count|null]. --}}
<nav {{ $attributes->merge(['class' => '-mb-px flex gap-1 overflow-x-auto border-b border-line [scrollbar-width:none]']) }} aria-label="Sections">
    @foreach ($items as $item)
        @php [$label, $url, $active] = $item; $count = $item[3] ?? null; @endphp
        <a href="{{ $url }}" @if ($active) aria-current="page" @endif
           class="flex h-11 shrink-0 items-center gap-2 border-b-2 px-4 text-[14px] font-medium whitespace-nowrap transition {{ $active ? 'border-teal text-heading' : 'border-transparent text-muted hover:text-heading' }}">
            {{ $label }}
            @if ($count !== null)<span class="rounded-full bg-canvas-deep px-2 py-0.5 text-[11px] font-semibold text-muted">{{ $count }}</span>@endif
        </a>
    @endforeach
</nav>
