@props(['packages', 'brands'])
{{--
    Packages comparison (Stitch Packages screen). Brand tabs are client-side (Alpine); rows are server-rendered so the
    table works without JS. "Buy Now" opens the enquiry popover with the package pre-selected (fallback: /contact?package=).
    Desktop: the five-column table. Below lg: stacked cards with the same content.
--}}
@php
    $report = fn ($package) => Js::from(['name' => $package->name, 'view' => route('reports.view', $package->slug), 'download' => route('reports.download', $package->slug)]);
    $badge = 'inline-flex items-center rounded-full bg-[#0e8c7e] px-2.5 py-0.5 text-[10px] font-bold tracking-[0.08em] text-white uppercase';
    $buy = 'inline-flex h-10 items-center justify-center gap-1.5 rounded-full bg-navy-900 px-5 text-[13px] font-semibold whitespace-nowrap text-white transition hover:bg-brand';
@endphp
<div x-data="{ brand: 'All' }">
    <div class="flex flex-wrap items-center gap-2.5 pb-2" role="group" aria-label="Filter packages by network">
        @foreach (collect(['All'])->merge($brands) as $brand)
            {{-- Active look comes only from aria-pressed, so static and Alpine classes can never conflict. --}}
            <button type="button" @click="brand = @js($brand)" :aria-pressed="(brand === @js($brand)).toString()" aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                    class="rounded-full border border-line bg-white px-5 py-2 text-[13px] font-semibold text-heading transition hover:border-heading aria-pressed:border-navy-900 aria-pressed:bg-navy-900 aria-pressed:text-white">{{ $brand }}</button>
        @endforeach
    </div>

    {{-- Desktop table --}}
    <div class="mt-8 hidden overflow-hidden rounded-2xl border border-line bg-white shadow-[var(--shadow-card)] lg:block">
        <table class="w-full border-collapse text-left">
            <caption class="sr-only">Press release distribution packages compared</caption>
            <thead>
                <tr class="border-b border-line bg-[#f5f9fc] text-[11px] font-semibold tracking-[0.12em] text-muted uppercase">
                    <th scope="col" class="w-[30%] px-6 py-4">Package</th>
                    <th scope="col" class="w-[18%] px-6 py-4">Sample report</th>
                    <th scope="col" class="w-[27%] px-6 py-4">Distribution</th>
                    <th scope="col" class="w-[13%] px-6 py-4">Price</th>
                    <th scope="col" class="w-[12%] px-6 py-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line-soft">
                @foreach ($packages as $package)
                    <tr x-show="brand === 'All' || brand === @js($package->brand)"
                        class="transition-colors duration-200 {{ $package->is_highlighted ? 'bg-[#eaf9f6]' : 'hover:bg-[#f9fbfd]' }}">
                        <th scope="row" class="px-6 py-7 align-top font-normal">
                            <div class="flex items-start gap-4">
                                <span class="mt-1 text-xs font-bold tracking-tight text-accent-ink">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <div>
                                    @if ($package->is_highlighted)
                                        <div class="mb-2"><span class="{{ $badge }}">Most requested</span></div>
                                    @endif
                                    <a href="{{ route('packages.show', $package->slug) }}" class="text-[17px] font-bold tracking-tight text-heading hover:text-accent-ink">{{ $package->name }}</a>
                                    <p class="mt-1 text-[13px] leading-snug text-muted">{{ $package->short_description }}</p>
                                </div>
                            </div>
                        </th>
                        <td class="px-6 py-7 align-top">
                            @if ($package->currentReport)
                                <div class="space-y-1">
                                    {{-- Opens the live PDF preview; the href is the no-JS fallback --}}
                                    <a href="{{ route('reports.view', $package->slug) }}" target="_blank" rel="noopener" aria-haspopup="dialog"
                                       @click.prevent="$dispatch('open-report', {{ $report($package) }})"
                                       class="inline-flex items-center gap-1.5 text-sm font-semibold text-accent-ink hover:underline" data-track="sample_report_click" data-track-package="{{ $package->name }}">
                                        <x-icon name="file" :size="16" :stroke="2" />View report
                                    </a>
                                    <p class="text-xs tracking-tight text-muted-soft">PDF &middot; {{ $package->currentReport->formatted_size }}</p>
                                </div>
                            @else
                                <span class="text-sm text-muted-soft">Coming soon</span>
                            @endif
                        </td>
                        <td class="px-6 py-7 align-top">
                            <p class="text-[14px] leading-relaxed text-muted">{{ $package->distribution_summary }}</p>
                        </td>
                        <td class="px-6 py-7 align-top whitespace-nowrap">
                            @if ($package->formatted_price)
                                <div class="flex items-baseline"><span class="text-[26px] font-bold tracking-[-0.02em] text-heading">{{ $package->formatted_price }}</span><span class="ml-1.5 text-xs font-medium text-muted">/ PR</span></div>
                            @else
                                <span class="text-base font-bold text-heading">Price on request</span>
                            @endif
                        </td>
                        <td class="px-6 py-7 text-right align-top">
                            <a href="{{ route('contact', ['package' => $package->slug]) }}" @click.prevent="$dispatch('open-enquiry', {{ $package->id }})" aria-haspopup="dialog"
                               class="{{ $buy }}" data-track="cta_click" data-track-label="Packages: Buy Now" data-track-package="{{ $package->name }}">Buy Now <x-icon name="arrow-right" :size="14" :stroke="2" /></a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Below lg: the same content as stacked cards --}}
    <div class="mt-8 grid gap-4 md:grid-cols-2 lg:hidden">
        @foreach ($packages as $package)
            <article x-show="brand === 'All' || brand === @js($package->brand)"
                     class="flex flex-col gap-4 rounded-2xl border p-5 shadow-[var(--shadow-card)] {{ $package->is_highlighted ? 'border-teal/50 bg-[#eaf9f6]' : 'border-line bg-white' }}">
                <div class="flex items-start gap-3">
                    <span class="mt-1 text-xs font-bold text-accent-ink">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <div class="min-w-0">
                        @if ($package->is_highlighted)<div class="mb-2"><span class="{{ $badge }}">Most requested</span></div>@endif
                        <a href="{{ route('packages.show', $package->slug) }}" class="text-[17px] font-bold tracking-tight text-heading hover:text-accent-ink">{{ $package->name }}</a>
                        <p class="mt-1 text-[13px] leading-snug text-muted">{{ $package->short_description }}</p>
                    </div>
                </div>
                @if ($package->distribution_summary)
                    <p class="border-t border-line-soft pt-3 text-[14px] leading-relaxed text-muted">{{ $package->distribution_summary }}</p>
                @endif
                @if ($package->currentReport)
                    <a href="{{ route('reports.view', $package->slug) }}" target="_blank" rel="noopener" aria-haspopup="dialog" @click.prevent="$dispatch('open-report', {{ $report($package) }})"
                       class="inline-flex items-center gap-1.5 self-start text-sm font-semibold text-accent-ink hover:underline"><x-icon name="file" :size="16" :stroke="2" />View report <span class="text-xs font-normal text-muted-soft">PDF &middot; {{ $package->currentReport->formatted_size }}</span></a>
                @endif
                <div class="mt-auto flex items-center justify-between gap-3 border-t border-line-soft pt-4">
                    @if ($package->formatted_price)
                        <div class="flex items-baseline"><span class="text-2xl font-bold text-heading">{{ $package->formatted_price }}</span><span class="ml-1.5 text-xs font-medium text-muted-soft">/ PR</span></div>
                    @else
                        <span class="text-base font-bold text-ink">Price on request</span>
                    @endif
                    <a href="{{ route('contact', ['package' => $package->slug]) }}" @click.prevent="$dispatch('open-enquiry', {{ $package->id }})" aria-haspopup="dialog" class="{{ $buy }} min-h-11">Buy Now <x-icon name="arrow-right" :size="14" :stroke="2" /></a>
                </div>
            </article>
        @endforeach
    </div>
</div>
