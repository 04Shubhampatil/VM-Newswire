@props(['type' => 'info', 'title' => null])
{{-- Inline notice. type: info | success | warning | danger. --}}
@php
    [$box, $icon] = [
        'info' => ['border-[#bfdcf0] bg-[#eef6fc] text-[#123b5d]', 'info'],
        'success' => ['border-[#bfe8d6] bg-success-soft text-success-ink', 'check'],
        'warning' => ['border-[#f1dd9c] bg-[#fff8e1] text-[#6b5200]', 'info'],
        'danger' => ['border-danger/30 bg-danger-soft text-danger', 'x'],
    ][$type];
@endphp
<div role="{{ $type === 'danger' ? 'alert' : 'status' }}" {{ $attributes->merge(['class' => "flex gap-3 rounded-[10px] border px-4 py-3.5 text-[14px] {$box}"]) }}>
    <x-icon :name="$icon" :size="18" :stroke="2" class="mt-0.5 shrink-0" />
    <div class="flex min-w-0 grow flex-col gap-1 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
        <p>@if ($title)<strong class="font-semibold">{{ $title }}</strong> @endif{{ $slot }}</p>
        @isset($action)<div class="shrink-0 font-semibold">{{ $action }}</div>@endisset
    </div>
</div>
