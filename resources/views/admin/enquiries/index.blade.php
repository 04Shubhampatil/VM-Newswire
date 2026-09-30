@php
    $sortLink = function (string $column, string $label) use ($sort, $direction) {
        $next = $sort === $column && $direction === 'desc' ? 'asc' : 'desc';
        $arrow = $sort === $column ? ($direction === 'desc' ? ' ↓' : ' ↑') : '';
        $url = request()->fullUrlWithQuery(['sort' => $column, 'direction' => $next, 'page' => null]);

        return '<a href="'.e($url).'" class="hover:text-ink">'.e($label).$arrow.'</a>';
    };
@endphp
<x-layouts.admin title="Enquiries" breadcrumb="Admin / Enquiries">
    <x-slot:actions>
        <x-button :href="route('admin.enquiries.export', request()->only(['search', 'status', 'package', 'from', 'to']))" variant="dark" size="sm" icon-left="download">Export CSV</x-button>
    </x-slot:actions>

    <form method="GET" class="grid gap-3 md:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_220px_160px_150px_150px_auto] xl:items-end">
        <div>
            <label for="search" class="admin-label">Search</label>
            <input id="search" type="search" name="search" value="{{ $filters['search'] }}" placeholder="Name, email or company" class="admin-input">
        </div>
        <x-admin.select name="package" label="Package" placeholder="All packages" :value="$filters['package']"
                        :options="['none' => 'General (no package)'] + $packages->pluck('name', 'id')->all()" />
        <x-admin.select name="status" label="Status" placeholder="All statuses" :value="$filters['status']"
                        :options="collect(\App\Enums\EnquiryStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all()" />
        <x-admin.input name="from" label="From" type="date" :value="$filters['from']" />
        <x-admin.input name="to" label="To" type="date" :value="$filters['to']" />
        <div class="flex gap-2">
            <button type="submit" class="btn btn-dark btn-sm">Filter</button>
            @if (array_filter($filters))<a href="{{ route('admin.enquiries.index') }}" class="btn btn-secondary btn-sm">Reset</a>@endif
        </div>
    </form>

    <x-admin.data-table caption="Enquiries">
        <x-slot:head>
            <th>{!! $sortLink('name', 'Name') !!}</th><th>Email</th><th>Phone</th><th>Company</th><th>Package</th>
            <th>{!! $sortLink('status', 'Status') !!}</th><th>{!! $sortLink('created_at', 'Created') !!}</th><th class="text-right">Actions</th>
        </x-slot:head>
        @forelse ($enquiries as $enquiry)
            <tr class="{{ $enquiry->status === \App\Enums\EnquiryStatus::New ? 'font-medium' : '' }}">
                <td>{{ $enquiry->name }}</td>
                <td class="text-muted">{{ $enquiry->email }}</td>
                <td class="whitespace-nowrap text-muted">{{ $enquiry->phone }}</td>
                <td class="text-muted">{{ $enquiry->company ?: '—' }}</td>
                <td>{{ $enquiry->package_label }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.enquiries.status', $enquiry) }}" x-data>
                        @csrf @method('PATCH')
                        <label class="sr-only" for="status-{{ $enquiry->id }}">Status for {{ $enquiry->name }}</label>
                        <select id="status-{{ $enquiry->id }}" name="status" @change="$el.form.submit()" class="h-9 rounded-[6px] border border-[#E6E4EF] bg-white px-2 text-[13px]">
                            @foreach (\App\Enums\EnquiryStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected($enquiry->status === $status)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        <noscript><button type="submit" class="text-xs underline">Save</button></noscript>
                    </form>
                </td>
                <td class="whitespace-nowrap text-muted">{{ $enquiry->created_at->format('j M Y, H:i') }}</td>
                <td class="text-right"><a href="{{ route('admin.enquiries.show', $enquiry) }}" class="font-semibold text-accent-ink hover:underline">Open</a></td>
            </tr>
        @empty
            <tr><td colspan="8" class="py-12 text-center text-muted">{{ array_filter($filters) ? 'No enquiries match these filters.' : 'No enquiries yet.' }}</td></tr>
        @endforelse
        <x-slot:footer><div class="text-sm">{{ $enquiries->links() }}</div></x-slot:footer>
    </x-admin.data-table>
</x-layouts.admin>
