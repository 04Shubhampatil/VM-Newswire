@php
    $sortLink = function (string $column, string $label) use ($sort, $direction) {
        $next = $sort === $column && $direction === 'desc' ? 'asc' : 'desc';
        $arrow = $sort === $column ? ($direction === 'desc' ? ' ↓' : ' ↑') : '';
        $url = request()->fullUrlWithQuery(['sort' => $column, 'direction' => $next, 'page' => null]);

        return '<a href="'.e($url).'" class="hover:text-heading">'.e($label).$arrow.'</a>';
    };
    $statusTabs = collect([null => 'All'])->merge(collect(\App\Enums\EnquiryStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]));
    $activeFilters = array_filter($filters);
    $onSpamTab = ($filters['status'] ?? null) === \App\Enums\EnquiryStatus::Spam->value;
@endphp
<x-layouts.admin title="Enquiries" description="Every enquiry submitted through the website. Update the status as you work through them." :breadcrumbs="[['Enquiries', null]]">
    <x-slot:actions>
        <x-button :href="route('admin.enquiries.export', request()->only(['search', 'status', 'flag', 'package', 'from', 'to']))" variant="dark" size="sm" icon-left="download">Export CSV</x-button>
    </x-slot:actions>

    <div class="flex flex-wrap gap-2">
        @foreach ($statusTabs as $value => $label)
            @php $count = $value === '' || $value === null ? $statusCounts->except(\App\Enums\EnquiryStatus::Spam->value)->sum() : ($statusCounts[$value] ?? 0); @endphp
            <a href="{{ request()->fullUrlWithQuery(['status' => $value ?: null, 'flag' => null, 'page' => null]) }}" class="admin-chip" @if (($filters['status'] ?? null) === ($value ?: null) && ! $filters['flag']) aria-current="page" @endif>
                {{ $label }} <span class="admin-chip-count">{{ $count }}</span>
            </a>
        @endforeach
        @if ($reviewCount)
            <a href="{{ request()->fullUrlWithQuery(['status' => null, 'flag' => 'review', 'page' => null]) }}" class="admin-chip" @if ($filters['flag'] === 'review') aria-current="page" @endif title="Kept as normal enquiries, but some spam signals matched. Check them and mark as spam if needed.">
                Possible spam <span class="admin-chip-count">{{ $reviewCount }}</span>
            </a>
        @endif
    </div>

    <x-admin.panel flush>
        <form method="GET" class="grid gap-3 p-4 md:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_220px_160px_160px_auto] xl:items-end">
            <input type="hidden" name="status" value="{{ $filters['status'] }}">
            <input type="hidden" name="flag" value="{{ $filters['flag'] }}">
            <div>
                <label for="search" class="admin-label">Search</label>
                <div class="relative">
                    <x-icon name="search" :size="16" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-muted-soft" />
                    <input id="search" type="search" name="search" value="{{ $filters['search'] }}" placeholder="Name, email, company or message" class="admin-input pl-9">
                </div>
            </div>
            <x-admin.select name="package" label="Package" placeholder="All packages" :value="$filters['package']"
                            :options="['none' => 'General (no package)'] + $packages->pluck('name', 'id')->all()" />
            <x-admin.input name="from" label="From" type="date" :value="$filters['from']" />
            <x-admin.input name="to" label="To" type="date" :value="$filters['to']" />
            <div class="flex gap-2">
                <button type="submit" class="btn btn-dark btn-sm">Apply</button>
                @if ($activeFilters)<a href="{{ route('admin.enquiries.index') }}" class="btn btn-secondary btn-sm">Reset</a>@endif
            </div>
        </form>
    </x-admin.panel>

    {{-- Bulk actions. Row checkboxes belong to this form via form="bulk-form" (rows also hold their own status forms). --}}
    <form id="bulk-form" method="POST" action="{{ route('admin.enquiries.bulk') }}" x-data="{ count: 0 }" @bulk-change.window="count = $event.detail"
          class="flex flex-wrap items-center gap-3" x-show="count > 0" x-cloak>
        @csrf
        <span class="text-[13px] font-semibold text-heading" x-text="count + ' selected'"></span>
        @if ($onSpamTab)
            <button type="submit" name="bulk_action" value="restore" class="btn btn-secondary btn-sm">Restore to New</button>
        @else
            <button type="submit" name="bulk_action" value="spam" class="btn btn-secondary btn-sm" onclick="return confirm('Mark the selected enquiries as spam? They stay in the Spam tab and can be restored.')">Mark as spam</button>
        @endif
    </form>
    @error('ids')<x-admin.alert type="danger" :title="$message" />@enderror

    <x-admin.data-table caption="Enquiries" :min-width="900">
        <x-slot:head>
            <th class="w-10">
                <label class="sr-only" for="bulk-all">Select all on this page</label>
                <input id="bulk-all" type="checkbox" class="size-4 accent-navy-900"
                       onchange="document.querySelectorAll('.bulk-row').forEach(c => c.checked = this.checked); window.dispatchEvent(new CustomEvent('bulk-change', { detail: document.querySelectorAll('.bulk-row:checked').length }))">
            </th>
            <th>{!! $sortLink('name', 'Customer') !!}</th><th>Company</th><th>Package</th>
            <th>{!! $sortLink('status', 'Status') !!}</th><th>{!! $sortLink('created_at', 'Received') !!}</th><th class="w-16"><span class="sr-only">Open</span></th>
        </x-slot:head>
        @forelse ($enquiries as $enquiry)
            <tr>
                <td>
                    <label class="sr-only" for="bulk-{{ $enquiry->id }}">Select {{ $enquiry->name }}</label>
                    <input id="bulk-{{ $enquiry->id }}" type="checkbox" name="ids[]" value="{{ $enquiry->id }}" form="bulk-form" class="bulk-row size-4 accent-navy-900"
                           onchange="window.dispatchEvent(new CustomEvent('bulk-change', { detail: document.querySelectorAll('.bulk-row:checked').length }))">
                </td>
                <td>
                    <div class="flex items-center gap-3">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-canvas-deep text-[13px] font-bold text-heading">{{ strtoupper(mb_substr($enquiry->name, 0, 1)) }}</span>
                        <div class="min-w-0">
                            <a href="{{ route('admin.enquiries.show', $enquiry) }}" class="block truncate font-semibold text-heading hover:text-accent-ink">{{ $enquiry->name }}</a>
                            <p class="truncate text-[12px] text-muted">{{ $enquiry->email }}@if ($enquiry->phone) · {{ $enquiry->phone }}@endif</p>
                            @if ($enquiry->needsSpamReview())
                                <span class="mt-1 inline-flex items-center rounded-full bg-[#fff6d6] px-2 py-0.5 text-[11px] font-semibold text-[#7a5b00]" title="Spam score {{ $enquiry->spam_score }}">Possible spam</span>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="text-muted">{{ $enquiry->company ?: '—' }}</td>
                <td>{{ $enquiry->package_label }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.enquiries.status', $enquiry) }}" x-data>
                        @csrf @method('PATCH')
                        <label class="sr-only" for="status-{{ $enquiry->id }}">Status for {{ $enquiry->name }}</label>
                        <select id="status-{{ $enquiry->id }}" name="status" @change="$el.form.submit()" class="admin-input h-9 w-36 text-[13px]">
                            @foreach (\App\Enums\EnquiryStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected($enquiry->status === $status)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        <noscript><button type="submit" class="admin-link">Save</button></noscript>
                    </form>
                </td>
                <td class="whitespace-nowrap text-muted">
                    <span class="block text-heading">{{ $enquiry->created_at->format('j M Y') }}</span>
                    <span class="text-[12px]">{{ $enquiry->created_at->format('H:i') }}</span>
                </td>
                <td class="text-right"><a href="{{ route('admin.enquiries.show', $enquiry) }}" class="admin-icon-btn ml-auto" aria-label="Open {{ $enquiry->name }}"><x-icon name="arrow-right" :size="15" :stroke="2" /></a></td>
            </tr>
        @empty
            <tr><td colspan="7" class="p-0">
                <x-admin.empty-state icon="inbox" :title="$activeFilters ? 'No enquiries match these filters' : 'No enquiries yet'" :text="$activeFilters ? 'Try a wider date range or clear the filters.' : 'They will appear here as soon as a visitor submits the enquiry form.'">
                    @if ($activeFilters)<a href="{{ route('admin.enquiries.index') }}" class="btn btn-secondary btn-sm">Clear filters</a>@endif
                </x-admin.empty-state>
            </td></tr>
        @endforelse
        <x-slot:footer>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <span class="text-muted">Showing {{ $enquiries->firstItem() ?? 0 }}–{{ $enquiries->lastItem() ?? 0 }} of {{ $enquiries->total() }}</span>
                {{ $enquiries->links() }}
            </div>
        </x-slot:footer>
    </x-admin.data-table>
</x-layouts.admin>
