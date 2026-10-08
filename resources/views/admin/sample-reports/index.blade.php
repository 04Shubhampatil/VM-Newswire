@php $missing = $packages->filter(fn ($p) => ! $p->currentReport)->count(); @endphp
<x-layouts.admin title="Sample Reports" description="One PDF per package. Visitors can preview and download it from the package page." :breadcrumbs="[['Sample Reports', null]]">
    <x-slot:actions>
        <x-button :href="route('admin.sample-reports.create')" size="sm" icon-left="upload">Upload report</x-button>
    </x-slot:actions>

    @if ($missing)
        <x-admin.alert type="warning">{{ $missing }} package(s) have no sample report yet.</x-admin.alert>
    @endif

    <x-admin.data-table caption="Sample reports by package" :min-width="760">
        <x-slot:head><th>Package</th><th>Report</th><th>Size</th><th>Uploaded</th><th class="text-right">Actions</th></x-slot:head>
        @foreach ($packages as $package)
            @php $report = $package->currentReport; @endphp
            <tr>
                <td>
                    <span class="font-semibold text-heading">{{ $package->name }}</span>
                    @unless ($package->is_active)<span class="ml-1 text-[12px] text-muted">(inactive)</span>@endunless
                </td>
                <td>
                    @if ($report)
                        <span class="flex items-center gap-2"><x-icon name="file" :size="16" class="shrink-0 text-accent-ink" /><span class="truncate">{{ $report->file_name }}</span></span>
                    @else
                        <x-admin.status-badge status="missing" />
                    @endif
                </td>
                <td class="text-muted">{{ $report?->formatted_size ?? '—' }}</td>
                <td class="text-muted">{{ $report?->uploaded_at->format('j M Y') ?? '—' }}</td>
                <td>
                    <div class="flex justify-end gap-4">
                        @if ($report)
                            <a href="{{ route('admin.sample-reports.download', $report) }}" class="admin-link text-heading">Preview</a>
                            <a href="{{ route('admin.sample-reports.create', ['package' => $package->id]) }}" class="admin-link">Replace</a>
                            <x-admin.confirm-form :action="route('admin.sample-reports.destroy', $report)" method="DELETE" confirm="Remove this sample report?">
                                <button type="submit" class="admin-link-danger">Remove</button>
                            </x-admin.confirm-form>
                        @else
                            <a href="{{ route('admin.sample-reports.create', ['package' => $package->id]) }}" class="admin-link">Upload</a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-admin.data-table>
</x-layouts.admin>
