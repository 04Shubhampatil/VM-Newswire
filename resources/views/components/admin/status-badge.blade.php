@props(['status'])
{{-- Works for EnquiryStatus, EmailDeliveryStatus, or a plain string. Text label is always shown (not colour alone). --}}
@php
    $value = $status instanceof \BackedEnum ? $status->value : (string) $status;
    $styles = [
        'new' => 'bg-teal-soft text-accent-ink',
        'contacted' => 'bg-[#e4eefb] text-[#1e4a8f]',
        'closed' => 'bg-canvas-deep text-muted',
        'sent' => 'bg-success-soft text-success-ink',
        'pending' => 'bg-[#fff6d6] text-[#7a5b00]',
        'failed' => 'bg-danger-soft text-danger',
        'active' => 'bg-success-soft text-success-ink',
        'inactive' => 'bg-canvas-deep text-muted',
        'missing' => 'bg-[#fff6d6] text-[#7a5b00]',
        'uploaded' => 'bg-success-soft text-success-ink',
    ][$value] ?? 'bg-canvas-deep text-muted';
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[12px] font-semibold whitespace-nowrap {$styles}"]) }}>
    <span class="size-1.5 rounded-full bg-current"></span>{{ ucfirst($value) }}
</span>
