@props(['small' => false])
<div class="flex flex-col rounded-[4px] border border-ink bg-white shadow-[8px_8px_0_#F1EAFE] {{ $small ? 'h-[176px] w-[160px] gap-2 p-4' : 'h-[248px] w-[200px] gap-[11px] p-[22px]' }}">
    <span class="flex items-center justify-between font-mono {{ $small ? 'text-[8px]' : 'text-[10px]' }} tracking-[0.08em] text-accent-ink">PRESS RELEASE<span class="text-muted">FOR IMMEDIATE RELEASE</span></span>
    <span class="font-display {{ $small ? 'text-[17px]' : 'text-[22px]' }} leading-[1.1] font-semibold">Your announcement, headline here</span>
    @foreach ([100, 92, 96] as $w)<span class="block h-1.5 rounded-sm bg-line-soft" style="width: {{ $w }}%"></span>@endforeach
    @unless ($small)<span class="block h-1.5 w-[70%] rounded-sm bg-line-soft"></span>@endunless
    <span class="h-px bg-line"></span>
    @foreach ($small ? [88, 94] : [88, 94, 60] as $w)<span class="block h-1.5 rounded-sm bg-line-soft" style="width: {{ $w }}%"></span>@endforeach
    <span class="mt-auto inline-flex h-[22px] items-center self-start rounded-[4px] bg-accent-fill px-2 text-[10px] font-bold tracking-[0.1em] text-white">DISTRIBUTING</span>
</div>
