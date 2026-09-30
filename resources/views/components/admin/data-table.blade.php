@props(['caption' => null])
<div {{ $attributes->merge(['class' => 'overflow-x-auto rounded-[8px] border border-[#E6E4EF] bg-white']) }}>
    <table class="w-full min-w-[720px] border-collapse text-left text-sm">
        @if ($caption)<caption class="sr-only">{{ $caption }}</caption>@endif
        <thead>
            <tr class="border-b border-[#E6E4EF] text-[11px] font-bold tracking-[0.1em] text-muted uppercase [&>th]:px-5 [&>th]:py-3.5">
                {{ $head }}
            </tr>
        </thead>
        <tbody class="[&>tr]:border-t [&>tr]:border-[#EFEDF6] [&>tr:first-child]:border-t-0 [&>tr>td]:px-5 [&>tr>td]:py-3.5 [&>tr]:transition-colors [&>tr:hover]:bg-[#FAF9FD]">
            {{ $slot }}
        </tbody>
    </table>
    @isset($footer)
        <div class="border-t border-[#E6E4EF] px-5 py-3.5">{{ $footer }}</div>
    @endisset
</div>
