@props(['name', 'label', 'checked' => false, 'help' => null])
@php $id = 'f-'.$name; $isOn = (bool) old($name, $checked); @endphp
<div {{ $attributes->merge(['class' => 'flex items-center justify-between gap-4']) }}>
    <label for="{{ $id }}" class="flex flex-col gap-0.5">
        <span class="text-[15px] font-medium">{{ $label }}</span>
        @if ($help)<span class="text-[13px] text-muted">{{ $help }}</span>@endif
    </label>
    <input type="hidden" name="{{ $name }}" value="0">
    <input id="{{ $id }}" name="{{ $name }}" type="checkbox" value="1" @checked($isOn) role="switch"
           class="peer relative h-7 w-12 shrink-0 cursor-pointer appearance-none rounded-full bg-[#cfcac0] transition before:absolute before:top-[3px] before:left-[3px] before:size-[22px] before:rounded-full before:bg-white before:transition checked:bg-success checked:before:translate-x-5 focus-visible:outline-2 focus-visible:outline-accent">
</div>
