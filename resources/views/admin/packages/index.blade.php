<x-layouts.admin title="Packages" breadcrumb="Admin / Packages">
    <x-slot:actions>
        @if ($archivedCount)
            <x-button :href="route('admin.packages.archived')" variant="secondary" size="sm">Archived ({{ $archivedCount }})</x-button>
        @endif
        <x-button :href="route('admin.packages.create')" size="sm" icon-left="plus">New package</x-button>
    </x-slot:actions>

    <x-admin.data-table caption="Packages">
        <x-slot:head>
            <th class="w-20">Order</th><th>Package</th><th>Price</th><th>Media</th><th>Report</th><th>Enquiries</th><th>Status</th><th class="text-right">Actions</th>
        </x-slot:head>
        @forelse ($packages as $package)
            <tr>
                <td>
                    <div class="flex gap-1">
                        @foreach (['up' => 'arrow-up', 'down' => 'arrow-down'] as $dir => $icon)
                            <form method="POST" action="{{ route('admin.packages.move', [$package, $dir]) }}">
                                @csrf @method('PATCH')
                                <button type="submit" aria-label="Move {{ $package->name }} {{ $dir }}" class="flex size-8 items-center justify-center rounded-[6px] border border-[#E6E4EF] text-muted hover:border-ink hover:text-ink disabled:opacity-30" @disabled(($dir === 'up' && $loop->parent->first) || ($dir === 'down' && $loop->parent->last))><x-icon :name="$icon" :size="14" /></button>
                            </form>
                        @endforeach
                    </div>
                </td>
                <td>
                    <a href="{{ route('admin.packages.edit', $package) }}" class="font-semibold hover:text-accent-ink">{{ $package->name }}</a>
                    @if ($package->is_highlighted)<span class="badge ml-2 bg-accent-soft text-accent-ink">Most requested</span>@endif
                    <div class="font-mono text-xs text-muted">/packages/{{ $package->slug }}</div>
                </td>
                <td class="whitespace-nowrap">{{ $package->formatted_price ?? 'On request' }}</td>
                <td class="whitespace-nowrap text-muted">{{ $package->featured_media_count }} featured · {{ $package->network_media_count }} network</td>
                <td>@if ($package->currentReport)<span class="text-xs font-semibold text-success-ink">PDF uploaded</span>@else<span class="text-xs text-danger">Missing</span>@endif</td>
                <td>{{ $package->enquiries_count }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.packages.toggle', $package) }}">
                        @csrf @method('PATCH')
                        <button type="submit" title="Click to {{ $package->is_active ? 'deactivate' : 'activate' }}"><x-admin.status-badge :status="$package->is_active ? 'active' : 'inactive'" /></button>
                    </form>
                </td>
                <td>
                    <div class="flex justify-end gap-3 text-sm font-semibold">
                        @if ($package->is_active)<a href="{{ route('packages.show', $package->slug) }}" target="_blank" class="text-muted hover:text-ink">View</a>@endif
                        <a href="{{ route('admin.packages.edit', $package) }}" class="text-accent-ink hover:underline">Edit</a>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="py-12 text-center text-muted">No packages yet. <a href="{{ route('admin.packages.create') }}" class="font-semibold text-accent-ink underline">Create the first one</a>.</td></tr>
        @endforelse
    </x-admin.data-table>
</x-layouts.admin>
