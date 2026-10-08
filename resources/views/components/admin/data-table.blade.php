@props(['caption' => null, 'minWidth' => 720])
{{-- Bordered table card: `head` slot for <th>s, default slot for <tr>s, optional `footer` slot (pagination). --}}
<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-card border border-line bg-white shadow-card']) }}>
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-[14px]" style="min-width: {{ (int) $minWidth }}px">
            @if ($caption)<caption class="sr-only">{{ $caption }}</caption>@endif
            <thead>
                <tr class="border-b border-line bg-[#f8fafc] text-[11px] font-semibold tracking-[0.1em] text-muted uppercase [&>th]:px-5 [&>th]:py-3 [&>th]:font-semibold">
                    {{ $head }}
                </tr>
            </thead>
            <tbody class="[&>tr]:border-t [&>tr]:border-line-soft [&>tr:first-child]:border-t-0 [&>tr>td]:px-5 [&>tr>td]:py-4 [&>tr>td]:align-middle [&>tr]:transition-colors [&>tr:hover]:bg-[#f8fafc]">
                {{ $slot }}
            </tbody>
        </table>
    </div>
    @isset($footer)
        <div class="border-t border-line bg-[#fbfcfd] px-5 py-3 text-[13px]">{{ $footer }}</div>
    @endisset
</div>
