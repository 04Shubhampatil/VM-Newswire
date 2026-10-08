{{-- Sticky save bar at the bottom of long forms. Put buttons in the default slot; optional `note` slot on the left. --}}
<div {{ $attributes->merge(['class' => 'sticky bottom-0 z-20 -mx-4 -mb-4 mt-2 flex flex-col gap-3 border-t border-line bg-white/95 px-4 py-3 backdrop-blur sm:flex-row sm:items-center sm:justify-between md:-mx-8 md:-mb-8 md:px-8']) }}>
    <div class="text-[13px] text-muted">{{ $note ?? '' }}</div>
    <div class="flex flex-wrap items-center gap-2">{{ $slot }}</div>
</div>
