@php
    $weekDelta = $metrics['previousWeek'] > 0
        ? round(($metrics['thisWeek'] - $metrics['previousWeek']) / $metrics['previousWeek'] * 100)
        : null;
    $weekLine = $weekDelta === null
        ? "{$metrics['thisWeek']} this week"
        : sprintf('%s%d%% vs previous week', $weekDelta >= 0 ? '+' : '', $weekDelta);
    $maxDay = max(1, $series->max('count'));
    $statusTotal = max(1, $byStatus->sum());
    $issues = [];
    if ($attention['failedEmails'] ?? 0) {
        $issues[] = ['danger', 'mail', "{$attention['failedEmails']} enquiry email(s) failed to send", 'Open the enquiry and retry, or check the mail settings.', route('admin.enquiries.index'), 'View enquiries'];
    }
    if (($attention['packagesWithoutReport'] ?? collect())->isNotEmpty()) {
        $issues[] = ['warning', 'file', 'No sample report for '.$attention['packagesWithoutReport']->join(', '), 'Visitors can download a sample report from every package page.', route('admin.sample-reports.create'), 'Upload a report'];
    }
    if ($attention['outletsWithoutPoster'] ?? 0) {
        $issues[] = ['warning', 'globe', "{$attention['outletsWithoutPoster']} active outlet(s) have no poster image", 'Posters appear on the homepage news cards and the outlet dialog.', route('admin.media.index'), 'Open media network'];
    }
    if (($attention['highlightedOutlets'] ?? 0) < 3) {
        $issues[] = ['info', 'star', 'Fewer than 3 outlets are marked “Show in hero network”', 'The homepage news cards show highlighted outlets only.', route('admin.media.index', ['hero' => 1]), 'Choose outlets'];
    }
    if ($attention['inactivePackages'] ?? 0) {
        $issues[] = ['info', 'box', "{$attention['inactivePackages']} package(s) are inactive", 'Inactive packages are hidden from the website.', route('admin.packages.index'), 'Review packages'];
    }
@endphp
<x-layouts.admin title="Dashboard" description="What is happening across enquiries, packages and the media network.">
    <x-slot:actions>
        <x-button :href="route('admin.packages.create')" variant="secondary" size="sm" icon-left="plus">New package</x-button>
        <x-button :href="route('admin.enquiries.export')" variant="dark" size="sm" icon-left="download">Export enquiries</x-button>
    </x-slot:actions>

    <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        <x-admin.metric-card label="New enquiries" :value="number_format($metrics['new'])" :href="route('admin.enquiries.index', ['status' => 'new'])" icon="inbox" accent>
            <x-slot:hint>Waiting for a first reply</x-slot:hint>
        </x-admin.metric-card>
        <x-admin.metric-card label="Enquiries, last 30 days" :value="number_format($metrics['last30'])" :href="route('admin.enquiries.index')" icon="chart" :delta="$weekLine" :delta-up="$weekDelta === null ? null : $weekDelta >= 0">
        </x-admin.metric-card>
        <x-admin.metric-card label="Active packages" :value="$metrics['packages']" :href="route('admin.packages.index')" icon="box">
            <x-slot:hint>Shown on the website</x-slot:hint>
        </x-admin.metric-card>
        <x-admin.metric-card label="Media outlets" :value="number_format($metrics['outlets'])" :href="route('admin.media.index')" icon="globe">
            <x-slot:hint>{{ $attention['highlightedOutlets'] ?? 0 }} highlighted on the homepage</x-slot:hint>
        </x-admin.metric-card>
    </div>

    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
        <x-admin.panel title="Enquiries, last 14 days" :description="number_format($metrics['thisWeek']).' this week · '.number_format($metrics['previousWeek']).' the week before'">
            <div class="flex h-40 items-end gap-1.5 sm:gap-2" role="img" aria-label="Enquiries per day for the last 14 days">
                @foreach ($series as $point)
                    <div class="group relative flex h-full flex-1 flex-col justify-end" title="{{ $point['date']->format('D j M') }}: {{ $point['count'] }}">
                        <div class="w-full rounded-t-[4px] {{ $point['count'] ? 'bg-teal group-hover:bg-accent' : 'bg-canvas-deep' }}" style="height: {{ $point['count'] ? max(8, round($point['count'] / $maxDay * 100)) : 4 }}%"></div>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-between text-[11px] text-muted">
                <span>{{ $series->first()['date']->format('j M') }}</span>
                <span>Today</span>
            </div>
            <div class="grid grid-cols-3 gap-3 border-t border-line-soft pt-4">
                @foreach ($byStatus as $status => $count)
                    <a href="{{ route('admin.enquiries.index', ['status' => $status]) }}" class="flex flex-col gap-1.5 rounded-[10px] border border-line px-4 py-3 transition hover:border-[#b9c7d4]">
                        <x-admin.status-badge :status="$status" class="self-start" />
                        <span class="text-[22px] leading-none font-bold text-heading">{{ $count }}</span>
                        <span class="text-[12px] text-muted">{{ round($count / $statusTotal * 100) }}% of all</span>
                    </a>
                @endforeach
            </div>
        </x-admin.panel>

        <x-admin.panel title="Needs attention" :description="$issues ? count($issues).' item(s) to review' : 'Everything looks good'" flush>
            @if ($issues)
                <ul class="divide-y divide-line-soft">
                    @foreach ($issues as [$type, $icon, $heading, $text, $url, $cta])
                        <li class="flex gap-3 px-6 py-4">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-full {{ ['danger' => 'bg-danger-soft text-danger', 'warning' => 'bg-[#fff6d6] text-[#7a5b00]', 'info' => 'bg-teal-soft text-accent-ink'][$type] }}"><x-icon :name="$icon" :size="16" :stroke="2" /></span>
                            <div class="min-w-0 grow">
                                <p class="text-[14px] font-semibold text-heading">{{ $heading }}</p>
                                <p class="mt-0.5 text-[13px] text-muted">{{ $text }}</p>
                                <a href="{{ $url }}" class="admin-link mt-1.5 inline-flex items-center gap-1">{{ $cta }} <x-icon name="arrow-right" :size="13" :stroke="2.2" /></a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <x-admin.empty-state icon="check" title="Nothing needs your attention" text="Emails are sending, every active package has a sample report, and the media network is complete." />
            @endif
        </x-admin.panel>
    </div>

    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
        <x-admin.panel title="Recent enquiries" flush>
            <x-slot:actions><a href="{{ route('admin.enquiries.index') }}" class="admin-link">View all</a></x-slot:actions>
            @if ($recent->isEmpty())
                <x-admin.empty-state icon="inbox" title="No enquiries yet" text="They will appear here as soon as a visitor submits the enquiry form." />
            @else
                <ul class="divide-y divide-line-soft">
                    @foreach ($recent as $enquiry)
                        <li>
                            <a href="{{ route('admin.enquiries.show', $enquiry) }}" class="flex items-center gap-4 px-6 py-3.5 transition hover:bg-[#f8fafc]">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-canvas-deep text-[13px] font-bold text-heading">{{ strtoupper(mb_substr($enquiry->name, 0, 1)) }}</span>
                                <span class="flex min-w-0 grow flex-col">
                                    <span class="truncate text-[14px] font-semibold text-heading">{{ $enquiry->name }}</span>
                                    <span class="truncate text-[12px] text-muted">{{ $enquiry->package_label }} · {{ $enquiry->email }}</span>
                                </span>
                                <x-admin.status-badge :status="$enquiry->status" class="hidden sm:inline-flex" />
                                <span class="w-20 shrink-0 text-right text-[12px] whitespace-nowrap text-muted">{{ $enquiry->created_at->diffForHumans(null, true, true) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-admin.panel>

        <div class="flex flex-col gap-6">
            <x-admin.panel title="Packages by enquiries" flush>
                <x-slot:actions><a href="{{ route('admin.packages.index') }}" class="admin-link">Manage</a></x-slot:actions>
                @php $maxPackage = max(1, $topPackages->max('enquiries_count')); @endphp
                <ul class="flex flex-col gap-3 px-6 py-5">
                    @forelse ($topPackages as $package)
                        <li class="flex flex-col gap-1.5">
                            <div class="flex items-center justify-between gap-3 text-[13px]">
                                <a href="{{ route('admin.packages.edit', $package) }}" class="truncate font-semibold text-heading hover:text-accent-ink">{{ $package->name }}</a>
                                <span class="shrink-0 text-muted">{{ $package->enquiries_count }} · {{ $package->formatted_price ?? 'On request' }}</span>
                            </div>
                            <div class="h-1.5 w-full rounded-full bg-canvas-deep"><div class="h-full rounded-full bg-navy-900" style="width: {{ round($package->enquiries_count / $maxPackage * 100) }}%"></div></div>
                        </li>
                    @empty
                        <li class="text-[13px] text-muted">No packages yet.</li>
                    @endforelse
                </ul>
            </x-admin.panel>

            <x-admin.panel title="Quick actions">
                <div class="grid grid-cols-2 gap-2">
                    @foreach ([
                        ['Add media outlet', 'globe', route('admin.media.create')],
                        ['Upload sample report', 'upload', route('admin.sample-reports.create')],
                        ['Edit homepage copy', 'edit', route('admin.content.edit')],
                        ['Add an FAQ', 'info', route('admin.faqs.create')],
                    ] as [$label, $icon, $url])
                        <a href="{{ $url }}" class="flex items-center gap-2.5 rounded-[10px] border border-line px-3 py-3 text-[13px] font-semibold text-heading transition hover:border-[#b9c7d4] hover:bg-[#f8fafc]">
                            <x-icon :name="$icon" :size="16" :stroke="1.8" class="shrink-0 text-accent-ink" /> {{ $label }}
                        </a>
                    @endforeach
                </div>
            </x-admin.panel>
        </div>
    </div>
</x-layouts.admin>
