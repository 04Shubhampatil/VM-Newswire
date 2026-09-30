<x-layouts.admin title="Dashboard" breadcrumb="Admin / Dashboard">
    <x-slot:actions>
        <x-button :href="route('admin.enquiries.export')" variant="dark" size="sm" icon-left="download">Export CSV</x-button>
    </x-slot:actions>

    @if ($failedEmails)
        <div role="alert" class="flex flex-col gap-2 rounded-[8px] border border-danger/30 bg-danger-soft px-5 py-4 text-sm text-danger sm:flex-row sm:items-center sm:justify-between">
            <span><strong>{{ $failedEmails }} enquiry email(s) failed to send.</strong> Check your mail settings, then retry from the enquiry page.</span>
            <a href="{{ route('admin.enquiries.index') }}" class="font-semibold underline">View enquiries</a>
        </div>
    @endif
    @if ($packagesWithoutReport->isNotEmpty())
        <div class="rounded-[8px] border border-[#e8d9a8] bg-[#fff8e1] px-5 py-4 text-sm text-[#6b5200]">
            No sample report yet for: {{ $packagesWithoutReport->join(', ') }}.
            <a href="{{ route('admin.sample-reports.create') }}" class="font-semibold underline">Upload one</a>
        </div>
    @endif

    <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        <x-admin.metric-card label="Total Enquiries" :value="number_format($metrics['total'])" :href="route('admin.enquiries.index')">
            <x-slot:hint>{{ $metrics['last30'] }} in the last 30 days</x-slot:hint>
        </x-admin.metric-card>
        <x-admin.metric-card label="New Enquiries" :value="number_format($metrics['new'])" :href="route('admin.enquiries.index', ['status' => 'new'])" accent />
        <x-admin.metric-card label="Active Packages" :value="$metrics['packages']" :href="route('admin.packages.index')" />
        <x-admin.metric-card label="Media Outlets" :value="number_format($metrics['outlets'])" :href="route('admin.media.index')" />
    </div>

    <section class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold">Recent enquiries</h2>
            <a href="{{ route('admin.enquiries.index') }}" class="text-sm font-semibold text-accent-ink hover:underline">View all</a>
        </div>
        <x-admin.data-table caption="Recent enquiries">
            <x-slot:head>
                <th>Name</th><th>Package</th><th>Email</th><th>Status</th><th>Date</th><th><span class="sr-only">Action</span></th>
            </x-slot:head>
            @forelse ($recent as $enquiry)
                <tr>
                    <td class="font-medium">{{ $enquiry->name }}</td>
                    <td>{{ $enquiry->package_label }}</td>
                    <td class="text-muted">{{ $enquiry->email }}</td>
                    <td><x-admin.status-badge :status="$enquiry->status" /></td>
                    <td class="whitespace-nowrap text-muted">{{ $enquiry->created_at->format('j M Y') }}</td>
                    <td class="text-right"><a href="{{ route('admin.enquiries.show', $enquiry) }}" class="font-semibold text-accent-ink hover:underline">Open</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-10 text-center text-muted">No enquiries yet. They'll appear here as soon as visitors submit the form.</td></tr>
            @endforelse
        </x-admin.data-table>
    </section>
</x-layouts.admin>
