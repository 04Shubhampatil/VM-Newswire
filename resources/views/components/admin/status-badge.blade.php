@props(['status'])
{{-- Works for EnquiryStatus, EmailDeliveryStatus, or a plain string. Text label is always shown (not colour alone). --}}
@php
    $value = $status instanceof \BackedEnum ? $status->value : (string) $status;
    $styles = [
        'new' => 'bg-accent-soft text-accent-ink',
        'contacted' => 'bg-[#dde6f5] text-[#1e4a8f]',
        'closed' => 'bg-[#e7e5e0] text-[#4a4f59]',
        'sent' => 'bg-success-soft text-success-ink',
        'pending' => 'bg-[#fff6d6] text-[#7a5b00]',
        'failed' => 'bg-danger-soft text-danger',
        'active' => 'bg-success-soft text-success-ink',
        'inactive' => 'bg-[#e7e5e0] text-[#4a4f59]',
    ][$value] ?? 'bg-[#e7e5e0] text-[#4a4f59]';
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {$styles}"]) }}>
    <span class="size-1.5 rounded-full bg-current"></span>{{ ucfirst($value) }}
</span>
