<x-layouts.admin title="Media Network" breadcrumb="Admin / Media Network">
    <x-slot:actions>
        <x-button :href="route('admin.media.import')" variant="secondary" size="sm" icon-left="upload">Bulk import CSV</x-button>
        <x-button :href="route('admin.media.create')" size="sm" icon-left="plus">Add Media Outlet</x-button>
    </x-slot:actions>

    <form method="GET" class="flex flex-col gap-3 md:flex-row md:items-end">
        <div class="grow">
            <label for="q" class="admin-label">Search</label>
            <input id="q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Outlet name" class="admin-input">
        </div>
        <x-admin.select name="category" label="Category" placeholder="All categories" :value="$filters['category'] ?? null"
                        :options="array_combine(config('vmnewswire.media_categories'), config('vmnewswire.media_categories'))" class="md:w-52" />
        <x-admin.select name="status" label="Status" placeholder="All" :value="$filters['status'] ?? null" :options="['active' => 'Enabled', 'inactive' => 'Disabled']" class="md:w-40" />
        <label class="flex h-11 items-center gap-2 text-sm"><input type="checkbox" name="hero" value="1" @checked($filters['hero'] ?? false) class="size-4 accent-accent-fill"> Hero only</label>
        <button type="submit" class="btn btn-dark btn-sm">Filter</button>
        @if (array_filter($filters))<a href="{{ route('admin.media.index') }}" class="btn btn-secondary btn-sm">Reset</a>@endif
    </form>

    <x-admin.data-table caption="Media outlets">
        <x-slot:head><th class="w-20">Poster</th><th>Media outlet</th><th>Category</th><th>Hero</th><th>Order</th><th>Status</th><th class="text-right">Actions</th></x-slot:head>
        @forelse ($outlets as $outlet)
            <tr>
                <td>
                    <a href="{{ route('admin.media.edit', $outlet) }}" class="block h-12 w-16 overflow-hidden rounded-[4px] border border-[#E6E4EF] bg-canvas" aria-label="Edit {{ $outlet->name }}">
                        @if ($outlet->poster_src)
                            <img src="{{ $outlet->poster_src }}" alt="" loading="lazy" class="size-full object-cover">
                        @else
                            <span class="flex size-full flex-col justify-between p-1.5"><span class="size-1.5 bg-accent"></span><span class="text-[9px] font-bold tracking-[0.08em] text-muted uppercase">None</span></span>
                        @endif
                    </a>
                </td>
                <td>
                    <a href="{{ route('admin.media.edit', $outlet) }}" class="font-semibold hover:text-accent-ink">{{ $outlet->name }}</a>
                    <div class="max-w-xs truncate text-xs text-muted">{{ $outlet->short_description ?: $outlet->website_url }}</div>
                </td>
                <td>{{ $outlet->category }}</td>
                <td>{{ $outlet->is_highlighted ? 'Yes' : '—' }}</td>
                <td class="font-mono text-xs">{{ $outlet->display_order }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.media.toggle', $outlet) }}">
                        @csrf @method('PATCH')
                        <button type="submit" title="Click to {{ $outlet->is_active ? 'disable' : 'enable' }}"><x-admin.status-badge :status="$outlet->is_active ? 'active' : 'inactive'" /></button>
                    </form>
                </td>
                <td>
                    <div class="flex justify-end gap-4 text-sm font-semibold whitespace-nowrap">
                        <a href="{{ route('admin.media.edit', $outlet) }}#poster" class="text-muted hover:text-ink">Replace image</a>
                        <a href="{{ route('admin.media.edit', $outlet) }}" class="text-accent-ink hover:underline">Edit</a>
                        @if ($outlet->packages_count === 0)
                            <x-admin.confirm-form :action="route('admin.media.destroy', $outlet)" method="DELETE" :confirm="'Delete “'.$outlet->name.'”? This will remove the media outlet and its poster from the media network.'">
                                <button type="submit" class="text-danger hover:underline">Delete</button>
                            </x-admin.confirm-form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="py-12 text-center text-muted">No outlets found.</td></tr>
        @endforelse
        <x-slot:footer><div class="text-sm">{{ $outlets->links() }}</div></x-slot:footer>
    </x-admin.data-table>
</x-layouts.admin>
