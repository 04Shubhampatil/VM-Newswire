@props(['icon' => 'inbox', 'title', 'text' => null])
<div {{ $attributes->merge(['class' => 'flex flex-col items-center gap-3 px-6 py-14 text-center']) }}>
    <span class="flex size-12 items-center justify-center rounded-full bg-teal-soft text-accent-ink"><x-icon :name="$icon" :size="22" :stroke="1.8" /></span>
    <p class="text-[15px] font-semibold text-heading">{{ $title }}</p>
    @if ($text)<p class="max-w-md text-[13px] text-muted">{{ $text }}</p>@endif
    @if (trim($slot))<div class="mt-2 flex flex-wrap justify-center gap-2">{{ $slot }}</div>@endif
</div>
