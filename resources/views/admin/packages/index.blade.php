@php
    $active = $packages->where('is_active', true)->count();
    $missingReports = $packages->filter(fn ($p) => ! $p->currentReport)->count();
@endphp
<x-layouts.admin title="Packages" description="Distribution packages sold on the website, in the order they appear." :breadcrumbs="[['Packages', null]]">
    <x-slot:actions>
        @if ($archivedCount)
            <x-button :href="route('admin.packages.archived')" variant="secondary" size="sm">Archived ({{ $archivedCount }})</x-button>
        @endif
        <x-button :href="route('admin.packages.create')" size="sm" icon-left="plus">New package</x-button>
    </x-slot:actions>

    <div class="flex flex-wrap gap-2">
        <span class="admin-chip" aria-current="page">All <span class="admin-chip-count">{{ $packages->count() }}</span></span>
        <span class="admin-chip">Active <span class="admin-chip-count">{{ $active }}</span></span>
        <span class="admin-chip">Inactive <span class="admin-chip-count">{{ $packages->count() - $active }}</span></span>
        @if ($missingReports)<span class="admin-chip">Missing report <span class="admin-chip-count">{{ $missingReports }}</span></span>@endif
    </div>

    <x-admin.data-table caption="Packages" :min-width="960">
        <x-slot:head>
            <th class="w-24">Order</th><th>Package</th><th>Price</th><th>Media</th><th>Sample report</th><th>Enquiries</th><th>Status</th><th class="text-right">Actions</th>
        </x-slot:head>
        @forelse ($packages as $package)
            <tr>
                <td>
                    <div class="flex gap-1">
                        @foreach (['up' => 'arrow-up', 'down' => 'arrow-down'] as $dir => $icon)
                            <form method="POST" action="{{ route('admin.packages.move', [$package, $dir]) }}">
                                @csrf @method('PATCH')
                                <button type="submit" aria-label="Move {{ $package->name }} {{ $dir }}" class="admin-icon-btn" @disabled(($dir === 'up' && $loop->parent->first) || ($dir === 'down' && $loop->parent->last))><x-icon :name="$icon" :size="14" /></button>
                            </form>
                        @endforeach
                    </div>
                </td>
                <td>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.packages.edit', $package) }}" class="font-semibold text-heading hover:text-accent-ink">{{ $package->name }}</a>
                        @if ($package->is_highlighted)<span class="rounded-full bg-cta px-2 py-0.5 text-[10px] font-bold tracking-wide whitespace-nowrap text-cta-ink uppercase">Most popular</span>@endif
                    </div>
                    <div class="font-mono text-[12px] text-muted">/packages/{{ $package->slug }}</div>
                </td>
                <td class="font-semibold whitespace-nowrap text-heading">{{ $package->formatted_price ?? 'On request' }}</td>
                <td class="whitespace-nowrap text-muted">{{ $package->featured_media_count }} featured · {{ $package->network_media_count }} network</td>
                <td><x-admin.status-badge :status="$package->currentReport ? 'uploaded' : 'missing'" /></td>
                <td>{{ $package->enquiries_count }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.packages.toggle', $package) }}">
                        @csrf @method('PATCH')
                        <button type="submit" title="Click to {{ $package->is_active ? 'deactivate' : 'activate' }}"><x-admin.status-badge :status="$package->is_active ? 'active' : 'inactive'" /></button>
                    </form>
                </td>
                <td>
                    <div class="flex justify-end gap-4">
                        @if ($package->is_active)<a href="{{ route('packages.show', $package->slug) }}" target="_blank" class="admin-link text-muted hover:text-heading">View</a>@endif
                        <a href="{{ route('admin.packages.edit', $package) }}" class="admin-link">Edit</a>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="p-0">
                <x-admin.empty-state icon="box" title="No packages yet" text="Create the first distribution package to start taking enquiries.">
                    <x-button :href="route('admin.packages.create')" size="sm" icon-left="plus">Create a package</x-button>
                </x-admin.empty-state>
            </td></tr>
        @endforelse
    </x-admin.data-table>
</x-layouts.admin>
