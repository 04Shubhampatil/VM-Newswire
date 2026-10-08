@props(['question', 'open' => false])
<div x-data="{ open: @js($open) }" {{ $attributes->merge(['class' => 'border-t border-line']) }}>
    <h3>
        <button type="button" @click="open = !open" :aria-expanded="open.toString()"
                class="group flex w-full items-center justify-between gap-6 py-6 text-left text-ink md:py-7">
            <span class="font-sans text-[18px] leading-snug font-semibold transition-colors group-hover:text-accent-ink md:text-[21px]">{{ $question }}</span>
            <span class="flex size-8 shrink-0 items-center justify-center rounded-full border border-line text-accent-ink transition-transform duration-300" :class="open && 'rotate-45 border-accent'">
                <x-icon name="plus" :size="16" />
            </span>
        </button>
    </h3>
    <div x-show="open" x-collapse @if (! $open) x-cloak @endif>
        <div class="max-w-[720px] pr-4 pb-7 text-[16px] leading-relaxed text-muted md:pr-[60px]">{{ $slot }}</div>
    </div>
</div>
