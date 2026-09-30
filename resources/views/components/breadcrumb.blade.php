@props(['items' => []])
{{-- $items: [['label' => 'Home', 'url' => route('home')], ['label' => 'Packages']] — last item is the current page. --}}
<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => 'text-[13px] text-muted']) }}>
    <ol class="flex flex-wrap items-center gap-2">
        @foreach ($items as $item)
            <li class="flex items-center gap-2">
                @if (! $loop->first)<span aria-hidden="true">/</span>@endif
                @if (! $loop->last && ! empty($item['url']))
                    <a href="{{ $item['url'] }}" class="hover:text-ink hover:underline">{{ $item['label'] }}</a>
                @else
                    <span class="font-semibold text-ink" aria-current="page">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
@push('schema')
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => collect($items)->values()->map(fn ($item, $i) => array_filter([
            '@type' => 'ListItem',
            'position' => $i + 1,
            'name' => $item['label'],
            'item' => $item['url'] ?? url()->current(),
        ]))->all(),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush
