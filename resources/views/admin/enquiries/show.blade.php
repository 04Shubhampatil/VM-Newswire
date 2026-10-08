@php
    $failed = $enquiry->emailLogs->where('delivery_status', \App\Enums\EmailDeliveryStatus::Failed)->count();
@endphp
<x-layouts.admin :title="$enquiry->name" :description="'Enquiry #'.$enquiry->id.' · received '.$enquiry->created_at->format('j M Y, H:i')" :breadcrumbs="[['Enquiries', route('admin.enquiries.index')], ['#'.$enquiry->id, null]]">
    <x-slot:actions>
        <x-button :href="route('admin.enquiries.index')" variant="secondary" size="sm" icon-left="arrow-right" class="[&>svg]:rotate-180">All enquiries</x-button>
        <x-button :href="'mailto:'.$enquiry->email.'?subject='.rawurlencode('Re: your VM Newswire enquiry')" size="sm" icon-left="mail">Reply by email</x-button>
    </x-slot:actions>

    @if ($failed)
        <x-admin.alert type="danger" title="{{ $failed }} email(s) failed to send.">Retry them below once the mail settings are fixed.</x-admin.alert>
    @endif

    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="flex flex-col gap-6">
            <x-admin.panel title="Customer">
                <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
                    @foreach ([
                        'Name' => $enquiry->name,
                        'Business email' => $enquiry->email,
                        'Phone / WhatsApp' => $enquiry->phone,
                        'Company' => $enquiry->company ?: '—',
                        'Country' => $enquiry->country ?: '—',
                        'Press releases planned' => $enquiry->release_count ?: '—',
                    ] as $label => $value)
                        <div class="flex flex-col gap-1"><dt class="label-caps">{{ $label }}</dt><dd class="text-[15px] font-medium break-words text-heading">{{ $value }}</dd></div>
                    @endforeach
                </dl>
            </x-admin.panel>

            <x-admin.panel title="Message">
                <p class="text-[15px] leading-relaxed whitespace-pre-line text-ink">{{ $enquiry->message ?: '—' }}</p>
            </x-admin.panel>

            <x-admin.panel title="Email delivery" description="The confirmation sent to the customer and the notification sent to your team." flush>
                @if ($failed)
                    <x-slot:actions>
                        <form method="POST" action="{{ route('admin.enquiries.retry', $enquiry) }}">
                            @csrf
                            <button type="submit" class="btn btn-dark btn-sm"><x-icon name="refresh" :size="15" />Retry failed ({{ $failed }})</button>
                        </form>
                    </x-slot:actions>
                @endif
                <ul class="divide-y divide-line-soft">
                    @forelse ($enquiry->emailLogs as $log)
                        <li class="flex flex-col gap-2 px-6 py-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="flex min-w-0 flex-col gap-0.5">
                                <span class="text-[14px] font-semibold text-heading">{{ $log->email_type->label() }}</span>
                                <span class="text-[13px] text-muted">to {{ $log->recipient ?: '(no address)' }}{{ $log->sent_at ? ' · '.$log->sent_at->format('j M Y, H:i') : '' }}</span>
                                @if ($log->error_message)<span class="text-[12px] text-danger">{{ $log->error_message }}</span>@endif
                            </div>
                            <x-admin.status-badge :status="$log->delivery_status" />
                        </li>
                    @empty
                        <li class="px-6 py-6 text-[13px] text-muted">No emails logged for this enquiry.</li>
                    @endforelse
                </ul>
            </x-admin.panel>
        </div>

        <div class="flex flex-col gap-6 xl:sticky xl:top-24">
            <x-admin.panel title="Status">
                <p class="flex items-center gap-2 text-[13px] text-muted">Currently <x-admin.status-badge :status="$enquiry->status" /></p>
                <form method="POST" action="{{ route('admin.enquiries.status', $enquiry) }}" class="flex gap-2">
                    @csrf @method('PATCH')
                    <label for="status" class="sr-only">Status</label>
                    <select id="status" name="status" class="admin-input">
                        @foreach (\App\Enums\EnquiryStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected($enquiry->status === $status)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-dark btn-sm shrink-0">Update</button>
                </form>
            </x-admin.panel>

            <x-admin.panel title="Package">
                <dl class="flex flex-col gap-3.5">
                    <div><dt class="label-caps">Package at enquiry</dt><dd class="mt-0.5 text-[15px] font-medium text-heading">{{ $enquiry->package_label }}</dd></div>
                    <div><dt class="label-caps">Price at enquiry</dt><dd class="mt-0.5 text-[15px] font-medium text-heading">{{ $enquiry->formatted_price_snapshot ?? '—' }}</dd></div>
                    @if ($enquiry->package)
                        <div><dt class="label-caps">Current price</dt><dd class="mt-0.5 text-[15px] font-medium text-heading">{{ $enquiry->package->formatted_price ?? 'On request' }}{{ $enquiry->package->trashed() ? ' (archived)' : '' }}</dd></div>
                    @endif
                    <div><dt class="label-caps">Source page</dt><dd class="mt-0.5 font-mono text-[13px] text-ink">{{ $enquiry->source_page ?: '—' }}</dd></div>
                </dl>
            </x-admin.panel>
        </div>
    </div>
</x-layouts.admin>
