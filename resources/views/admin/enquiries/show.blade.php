@php
    $failed = $enquiry->emailLogs->where('delivery_status', \App\Enums\EmailDeliveryStatus::Failed)->count();
@endphp
<x-layouts.admin :title="$enquiry->name" breadcrumb="Admin / Enquiries / #{{ $enquiry->id }}">
    <x-slot:actions>
        <x-button :href="'mailto:'.$enquiry->email.'?subject='.rawurlencode('Re: your VM Newswire enquiry')" size="sm" icon-left="mail">Reply by email</x-button>
    </x-slot:actions>

    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="flex flex-col gap-6">
            <section class="admin-panel flex flex-col gap-4">
                <h2 class="text-lg font-semibold">Customer</h2>
                <dl class="grid gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
                    @foreach ([
                        'Name' => $enquiry->name,
                        'Business email' => $enquiry->email,
                        'Phone / WhatsApp' => $enquiry->phone,
                        'Company' => $enquiry->company ?: '—',
                        'Country' => $enquiry->country ?: '—',
                        'Press releases' => $enquiry->release_count ?: '—',
                    ] as $label => $value)
                        <div class="flex flex-col gap-1"><dt class="label-caps">{{ $label }}</dt><dd class="text-[15px] font-medium break-words">{{ $value }}</dd></div>
                    @endforeach
                </dl>
            </section>
            <section class="admin-panel flex flex-col gap-3">
                <h2 class="text-lg font-semibold">PR goals / notes</h2>
                <p class="text-[15px] leading-relaxed whitespace-pre-line">{{ $enquiry->message }}</p>
            </section>
            <section class="admin-panel flex flex-col gap-4">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-lg font-semibold">Email delivery</h2>
                    @if ($failed)
                        <form method="POST" action="{{ route('admin.enquiries.retry', $enquiry) }}">
                            @csrf
                            <button type="submit" class="btn btn-dark btn-sm"><x-icon name="refresh" :size="16" />Retry failed ({{ $failed }})</button>
                        </form>
                    @endif
                </div>
                <ul class="flex flex-col divide-y divide-[#EFEDF6]">
                    @forelse ($enquiry->emailLogs as $log)
                        <li class="flex flex-col gap-1 py-3 text-sm sm:flex-row sm:items-start sm:justify-between">
                            <span class="flex flex-col gap-0.5">
                                <span class="font-medium">{{ $log->email_type->label() }}</span>
                                <span class="text-muted">to {{ $log->recipient ?: '(no address)' }}{{ $log->sent_at ? ' · '.$log->sent_at->format('j M Y, H:i') : '' }}</span>
                                @if ($log->error_message)<span class="text-xs text-danger">{{ $log->error_message }}</span>@endif
                            </span>
                            <x-admin.status-badge :status="$log->delivery_status" />
                        </li>
                    @empty
                        <li class="py-3 text-sm text-muted">No emails logged.</li>
                    @endforelse
                </ul>
            </section>
        </div>

        <div class="flex flex-col gap-6">
            <section class="admin-panel flex flex-col gap-4">
                <h2 class="text-lg font-semibold">Status</h2>
                <form method="POST" action="{{ route('admin.enquiries.status', $enquiry) }}" class="flex gap-2">
                    @csrf @method('PATCH')
                    <label for="status" class="sr-only">Status</label>
                    <select id="status" name="status" class="admin-input">
                        @foreach (\App\Enums\EnquiryStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected($enquiry->status === $status)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-dark btn-sm">Update</button>
                </form>
                <p class="text-sm">Currently <x-admin.status-badge :status="$enquiry->status" /></p>
            </section>
            <section class="admin-panel flex flex-col gap-4 text-sm">
                <h2 class="text-lg font-semibold">Package</h2>
                <dl class="flex flex-col gap-3">
                    <div><dt class="label-caps">Package at enquiry</dt><dd class="text-[15px] font-medium">{{ $enquiry->package_label }}</dd></div>
                    <div><dt class="label-caps">Price at enquiry</dt><dd class="text-[15px] font-medium">{{ $enquiry->formatted_price_snapshot ?? '—' }}</dd></div>
                    @if ($enquiry->package)
                        <div><dt class="label-caps">Current price</dt><dd class="text-[15px] font-medium">{{ $enquiry->package->formatted_price ?? 'On request' }}{{ $enquiry->package->trashed() ? ' (archived)' : '' }}</dd></div>
                    @endif
                    <div><dt class="label-caps">Source page</dt><dd class="font-mono text-[13px]">{{ $enquiry->source_page ?: '—' }}</dd></div>
                    <div><dt class="label-caps">Submitted</dt><dd class="text-[15px] font-medium">{{ $enquiry->created_at->format('j M Y, H:i') }}</dd></div>
                </dl>
            </section>
            <a href="{{ route('admin.enquiries.index') }}" class="text-sm font-semibold text-accent-ink hover:underline">← All enquiries</a>
        </div>
    </div>
</x-layouts.admin>
