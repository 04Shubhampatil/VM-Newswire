<x-layouts.admin title="Archived packages" breadcrumb="Admin / Packages / Archived">
    <x-slot:actions>
        <x-button :href="route('admin.packages.index')" variant="secondary" size="sm">Back to packages</x-button>
    </x-slot:actions>

    <p class="text-sm text-muted">Archived packages are hidden from the website. Their enquiries are kept. Restoring brings a package back as inactive.</p>

    <x-admin.data-table caption="Archived packages">
        <x-slot:head><th>Package</th><th>Enquiries</th><th>Archived</th><th class="text-right">Action</th></x-slot:head>
        @forelse ($packages as $package)
            <tr>
                <td class="font-semibold">{{ $package->name }}</td>
                <td>{{ $package->enquiries_count }}</td>
                <td class="text-muted">{{ $package->deleted_at->format('j M Y') }}</td>
                <td class="text-right">
                    <x-admin.confirm-form :action="route('admin.packages.restore', $package->id)" method="PATCH" confirm="Restore this package?">
                        <button type="submit" class="text-sm font-semibold text-accent-ink hover:underline">Restore</button>
                    </x-admin.confirm-form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="py-10 text-center text-muted">No archived packages.</td></tr>
        @endforelse
    </x-admin.data-table>
</x-layouts.admin>
