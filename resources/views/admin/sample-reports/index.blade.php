<x-layouts.admin title="Sample Reports" breadcrumb="Admin / Sample reports">
    <x-slot:actions>
        <x-button :href="route('admin.sample-reports.create')" size="sm" icon-left="upload">Upload report</x-button>
    </x-slot:actions>

    <x-admin.data-table caption="Sample reports by package">
        <x-slot:head><th>Package</th><th>Current report</th><th>Size</th><th>Uploaded</th><th class="text-right">Actions</th></x-slot:head>
        @foreach ($packages as $package)
            @php $report = $package->currentReport; @endphp
            <tr>
                <td class="font-semibold">{{ $package->name }} @unless ($package->is_active)<span class="ml-1 text-xs font-normal text-muted">(inactive)</span>@endunless</td>
                <td>{{ $report?->file_name ?? '—' }}</td>
                <td class="text-muted">{{ $report?->formatted_size ?? '—' }}</td>
                <td class="text-muted">{{ $report?->uploaded_at->format('j M Y') ?? '—' }}</td>
                <td>
                    <div class="flex justify-end gap-4 text-sm font-semibold">
                        @if ($report)
                            <a href="{{ route('admin.sample-reports.download', $report) }}" class="hover:underline">Preview</a>
                            <a href="{{ route('admin.sample-reports.create', ['package' => $package->id]) }}" class="text-accent-ink hover:underline">Replace</a>
                            <x-admin.confirm-form :action="route('admin.sample-reports.destroy', $report)" method="DELETE" confirm="Remove this sample report?">
                                <button type="submit" class="text-danger hover:underline">Remove</button>
                            </x-admin.confirm-form>
                        @else
                            <a href="{{ route('admin.sample-reports.create', ['package' => $package->id]) }}" class="text-accent-ink hover:underline">Upload</a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-admin.data-table>
</x-layouts.admin>
