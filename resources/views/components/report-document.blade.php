@props(['onDark' => true, 'title' => 'Sample distribution report', 'outlets' => []])
{{-- Decorative illustration of a PDF distribution report: floats gently, tilts ≤3° on hover. --}}
<div aria-hidden="true" {{ $attributes->merge(['class' => 'doc-tilt relative mx-auto h-[408px] w-[324px] shrink-0 max-[22.5rem]:[zoom:0.88] md:h-[510px] md:w-[404px] lg:mx-0']) }}>
    <div class="relative h-full w-full">
        <div class="doc-float relative h-full w-full">
            <div class="absolute top-0 left-6 h-[384px] w-[300px] rounded-[4px] md:h-[486px] md:w-[380px] {{ $onDark ? 'bg-brand-soft' : 'bg-line-soft' }}"></div>
            <div class="absolute top-6 left-0 flex h-[384px] w-[300px] flex-col gap-3.5 rounded-[4px] bg-white p-5 text-ink shadow-[0_30px_60px_-40px_rgba(0,0,0,0.5)] md:h-[486px] md:w-[380px] md:p-7 {{ $onDark ? '' : 'border border-line' }}">
                <div class="flex items-center justify-between">
                    <span class="font-sans text-lg font-semibold">{{ $site->get('company_name') }}</span>
                    <span class="inline-flex h-[22px] items-center rounded-[3px] bg-accent-fill px-2 text-[10px] font-bold tracking-[0.1em] text-white">PDF</span>
                </div>
                <div class="flex flex-col gap-1 border-b border-navy-900 pb-3">
                    <span class="text-[9px] font-bold tracking-[0.14em] text-accent-ink uppercase">Distribution report</span>
                    <span class="font-sans text-xl leading-tight font-semibold md:text-2xl">{{ $title }}</span>
                </div>
                @foreach (collect($outlets)->take(5) as $i => $name)
                    <div class="flex items-center justify-between gap-2 border-b border-line-soft pb-2.5 {{ $i === 4 ? 'max-md:hidden' : '' }}">
                        <span class="flex flex-col gap-0.5"><span class="text-xs font-bold">{{ $name }}</span><span class="h-1.5 w-24 rounded-sm bg-line-soft"></span></span>
                        <span class="rounded-[3px] bg-success-soft px-1.5 py-0.5 text-[9px] font-bold tracking-[0.06em] text-success-ink">PUBLISHED</span>
                    </div>
                @endforeach
                <span class="mt-auto text-[10px] text-muted">+ extended network listing</span>
            </div>
        </div>
    </div>
</div>
