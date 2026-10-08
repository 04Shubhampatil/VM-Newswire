@php $activeFilters = array_filter($filters); @endphp
<x-layouts.admin title="Media Network" description="The publications and newswires that carry releases. Highlighted outlets appear on the homepage." :breadcrumbs="[['Media Network', null]]">
    <x-slot:actions>
        <x-button :href="route('admin.media.import')" variant="secondary" size="sm" icon-left="upload">Import CSV</x-button>
        <x-button :href="route('admin.media.create')" size="sm" icon-left="plus">Add outlet</x-button>
    </x-slot:actions>

    <x-admin.panel flush>
        <form method="GET" class="grid gap-3 p-4 md:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_200px_160px_auto_auto] xl:items-end">
            <div>
                <label for="q" class="admin-label">Search</label>
                <div class="relative">
                    <x-icon name="search" :size="16" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-muted-soft" />
                    <input id="q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Outlet name" class="admin-input pl-9">
                </div>
            </div>
            <x-admin.select name="category" label="Category" placeholder="All categories" :value="$filters['category'] ?? null"
                            :options="array_combine(config('vmnewswire.media_categories'), config('vmnewswire.media_categories'))" />
            <x-admin.select name="status" label="Status" placeholder="All" :value="$filters['status'] ?? null" :options="['active' => 'Enabled', 'inactive' => 'Disabled']" />
            <label class="flex h-10 items-center gap-2 text-[13px] font-medium"><input type="checkbox" name="hero" value="1" @checked($filters['hero'] ?? false) class="size-4 accent-navy-900"> Highlighted only</label>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-dark btn-sm">Apply</button>
                @if ($activeFilters)<a href="{{ route('admin.media.index') }}" class="btn btn-secondary btn-sm">Reset</a>@endif
            </div>
        </form>
    </x-admin.panel>

    <x-admin.data-table caption="Media outlets" :min-width="900">
        <x-slot:head><th class="w-24">Poster</th><th>Outlet</th><th>Category</th><th>Homepage</th><th>Order</th><th>Status</th><th class="text-right">Actions</th></x-slot:head>
        @forelse ($outlets as $outlet)
            <tr>
                <td>
                    <a href="{{ route('admin.media.edit', $outlet) }}#poster" class="block h-12 w-16 overflow-hidden rounded-[6px] border border-line bg-canvas-deep" aria-label="Edit {{ $outlet->name }}">
                        @if ($outlet->poster_src)
                            <img src="{{ $outlet->poster_src }}" alt="" loading="lazy" class="size-full object-cover">
                        @else
                            <span class="flex size-full items-center justify-center text-[9px] font-bold tracking-[0.08em] text-muted-soft uppercase">None</span>
                        @endif
                    </a>
                </td>
                <td>
                    <a href="{{ route('admin.media.edit', $outlet) }}" class="font-semibold text-heading hover:text-accent-ink">{{ $outlet->name }}</a>
                    <div class="max-w-xs truncate text-[12px] text-muted">{{ $outlet->short_description ?: $outlet->website_url }}</div>
                </td>
                <td><span class="rounded-full bg-canvas-deep px-2.5 py-1 text-[12px] font-medium text-ink">{{ $outlet->category }}</span></td>
                <td>@if ($outlet->is_highlighted)<span class="inline-flex items-center gap-1 text-[12px] font-semibold text-accent-ink"><x-icon name="star" :size="14" :stroke="2" />Highlighted</span>@else<span class="text-muted">—</span>@endif</td>
                <td class="font-mono text-[12px] text-muted">{{ $outlet->display_order }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.media.toggle', $outlet) }}">
                        @csrf @method('PATCH')
                        <button type="submit" title="Click to {{ $outlet->is_active ? 'disable' : 'enable' }}"><x-admin.status-badge :status="$outlet->is_active ? 'active' : 'inactive'" /></button>
                    </form>
                </td>
                <td>
                    <div class="flex justify-end gap-4 whitespace-nowrap">
                        <a href="{{ route('admin.media.edit', $outlet) }}" class="admin-link">Edit</a>
                        @if ($outlet->packages_count === 0)
                            <x-admin.confirm-form :action="route('admin.media.destroy', $outlet)" method="DELETE" :confirm="'Delete “'.$outlet->name.'”? This will remove the media outlet and its poster from the media network.'">
                                <button type="submit" class="admin-link-danger">Delete</button>
                            </x-admin.confirm-form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="p-0">
                <x-admin.empty-state icon="globe" :title="$activeFilters ? 'No outlets match these filters' : 'No media outlets yet'" :text="$activeFilters ? 'Try another search or clear the filters.' : 'Add outlets one by one or import a CSV.'">
                    @if ($activeFilters)<a href="{{ route('admin.media.index') }}" class="btn btn-secondary btn-sm">Clear filters</a>@else<x-button :href="route('admin.media.create')" size="sm" icon-left="plus">Add outlet</x-button>@endif
                </x-admin.empty-state>
            </td></tr>
        @endforelse
        <x-slot:footer>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <span class="text-muted">Showing {{ $outlets->firstItem() ?? 0 }}–{{ $outlets->lastItem() ?? 0 }} of {{ $outlets->total() }}</span>
                {{ $outlets->links() }}
            </div>
        </x-slot:footer>
    </x-admin.data-table>
</x-layouts.admin>
