@props(['question', 'open' => false])
<div x-data="{ open: @js($open) }" {{ $attributes->merge(['class' => 'border-t border-line']) }}>
    <h3>
        <button type="button" @click="open = !open" :aria-expanded="open.toString()"
                class="flex w-full items-center justify-between gap-6 py-[26px] text-left text-ink">
            <span class="font-display text-xl leading-snug font-semibold md:text-2xl">{{ $question }}</span>
            <span class="flex size-9 shrink-0 items-center justify-center rounded-full border border-line text-accent-ink transition-transform duration-300" :class="open && 'rotate-45'">
                <x-icon name="plus" :size="16" />
            </span>
        </button>
    </h3>
    <div x-show="open" x-collapse @if (! $open) x-cloak @endif>
        <div class="pr-4 pb-[26px] text-base leading-relaxed text-muted md:pr-[60px]">{{ $slot }}</div>
    </div>
</div>
