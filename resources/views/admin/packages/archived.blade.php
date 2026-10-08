<x-layouts.admin title="Archived packages" description="Archived packages are hidden from the website. Their enquiries are kept. Restoring brings a package back as inactive." :breadcrumbs="[['Packages', route('admin.packages.index')], ['Archived', null]]">
    <x-slot:actions>
        <x-button :href="route('admin.packages.index')" variant="secondary" size="sm">Back to packages</x-button>
    </x-slot:actions>

    <x-admin.data-table caption="Archived packages" :min-width="640">
        <x-slot:head><th>Package</th><th>Enquiries</th><th>Archived</th><th class="text-right">Action</th></x-slot:head>
        @forelse ($packages as $package)
            <tr>
                <td class="font-semibold text-heading">{{ $package->name }}</td>
                <td>{{ $package->enquiries_count }}</td>
                <td class="text-muted">{{ $package->deleted_at->format('j M Y') }}</td>
                <td class="text-right">
                    <x-admin.confirm-form :action="route('admin.packages.restore', $package->id)" method="PATCH" confirm="Restore this package?">
                        <button type="submit" class="admin-link">Restore</button>
                    </x-admin.confirm-form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="p-0"><x-admin.empty-state icon="box" title="No archived packages" /></td></tr>
        @endforelse
    </x-admin.data-table>
</x-layouts.admin>
