@props(['packages', 'brands'])
{{--
    Brand filter is client-side (Alpine); rows are server-rendered so the table works without JS.
    "Buy Now" opens the enquiry popover (<x-enquiry-modal>) with the row's package pre-selected;
    without JS it falls back to the contact page with ?package=<slug>.
--}}
<div x-data="{ brand: 'All' }" class="flex flex-col gap-8">
    <div role="group" aria-label="Filter packages by network" class="-mx-5 flex gap-2 overflow-x-auto px-5 md:mx-0 md:flex-wrap md:px-0">
        @foreach (collect(['All'])->merge($brands) as $brand)
            <button type="button" @click="brand = @js($brand)" :aria-pressed="(brand === @js($brand)).toString()"
                    :class="brand === @js($brand) ? 'bg-ink text-white border-ink' : 'bg-white text-ink border-line hover:border-ink'"
                    class="h-11 shrink-0 rounded-[6px] border px-[18px] text-sm font-semibold transition">{{ $brand }}</button>
        @endforeach
    </div>

    {{-- Desktop table --}}
    <div class="hidden rounded-card border border-line bg-white lg:block">
        <table class="w-full border-collapse text-left">
            <caption class="sr-only">Press release distribution packages compared</caption>
            <thead class="sticky top-[76px] z-10">
                <tr class="bg-white text-[11px] font-bold tracking-[0.12em] text-muted uppercase [&>th]:border-b [&>th]:border-ink [&>th]:px-4 [&>th]:py-[18px] [&>th:first-child]:rounded-tl-card [&>th:first-child]:pl-8 [&>th:last-child]:rounded-tr-card [&>th:last-child]:pr-8">
                    <th scope="col" class="w-[32%]">Package</th>
                    <th scope="col">Sample report</th>
                    <th scope="col" class="hidden xl:table-cell">Distribution</th>
                    <th scope="col">Price</th>
                    <th scope="col" class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($packages as $package)
                    <tr x-show="brand === 'All' || brand === @js($package->brand)"
                        class="border-t border-line-soft align-middle transition-colors duration-300 first:border-t-0 hover:bg-[#F8F5FF] {{ $package->is_highlighted ? 'bg-[#F8F5FF]' : '' }} [&>td]:px-4 [&>td]:py-6 [&>td:first-child]:pl-8 [&>td:last-child]:pr-8">
                        <th scope="row" class="px-4 py-6 pl-8 text-left font-normal">
                            <div class="flex flex-col gap-1.5">
                                <span class="flex items-center gap-3">
                                    <span class="font-mono text-xs text-accent-ink">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    @if ($package->is_highlighted)<span class="badge bg-accent-soft text-accent-ink">Most requested</span>@endif
                                </span>
                                <a href="{{ route('packages.show', $package->slug) }}" class="font-display text-[26px] leading-tight font-semibold hover:text-accent-ink">{{ $package->name }}</a>
                                <span class="text-sm leading-normal text-muted">{{ $package->short_description }}</span>
                            </div>
                        </th>
                        <td>
                            @if ($package->currentReport)
                                {{-- Opens the live PDF preview; the href is the no-JS fallback --}}
                                <a href="{{ route('reports.view', $package->slug) }}" target="_blank" rel="noopener" aria-haspopup="dialog"
                                   @click.prevent="$dispatch('open-report', { name: @js($package->name), view: @js(route('reports.view', $package->slug)), download: @js(route('reports.download', $package->slug)) })"
                                   class="link-arrow text-sm" data-track="sample_report_click" data-track-package="{{ $package->name }}">
                                    <x-icon name="file" :size="16" />View report
                                </a>
                                <span class="mt-1 block font-mono text-[11px] text-muted">PDF · {{ $package->currentReport->formatted_size }}</span>
                            @else
                                <span class="text-sm text-muted">Coming soon</span>
                            @endif
                        </td>
                        <td class="hidden text-sm leading-normal xl:table-cell">{{ $package->distribution_summary }}</td>
                        <td><x-price :package="$package" size="sm" :from="false" /></td>
                        <td class="text-right">
                            <x-button :href="route('contact', ['package' => $package->slug])" @click.prevent="$dispatch('open-enquiry', {{ $package->id }})" aria-haspopup="dialog"
                                      size="sm" icon="arrow-right" data-track="cta_click" data-track-label="Packages: Buy Now" data-track-package="{{ $package->name }}">Buy Now</x-button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Mobile / tablet stacked comparison cards --}}
    <div class="grid gap-3.5 md:grid-cols-2 lg:hidden">
        @foreach ($packages as $package)
            <article x-show="brand === 'All' || brand === @js($package->brand)" class="flex flex-col gap-3.5 rounded-card border bg-white p-[22px] {{ $package->is_highlighted ? 'border-accent' : 'border-line' }}">
                <div class="flex h-6 items-center justify-between">
                    <span class="font-mono text-xs text-accent-ink">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    @if ($package->is_highlighted)<span class="badge bg-accent-soft text-accent-ink">Most requested</span>@endif
                </div>
                <a href="{{ route('packages.show', $package->slug) }}" class="font-display text-[26px] leading-tight font-semibold hover:text-accent-ink">{{ $package->name }}</a>
                <dl class="grid grid-cols-[96px_1fr] gap-x-3 gap-y-2.5 text-sm leading-normal">
                    <dt class="text-muted">Distribution</dt>
                    <dd>{{ $package->distribution_summary ?: '—' }}</dd>
                    <dt class="text-muted">Sample report</dt>
                    <dd>
                        @if ($package->currentReport)
                            <a href="{{ route('reports.view', $package->slug) }}" target="_blank" rel="noopener" aria-haspopup="dialog"
                               @click.prevent="$dispatch('open-report', { name: @js($package->name), view: @js(route('reports.view', $package->slug)), download: @js(route('reports.download', $package->slug)) })"
                               class="link-arrow text-sm"><x-icon name="file" :size="15" />View report</a>
                        @else
                            Coming soon
                        @endif
                    </dd>
                </dl>
                <div class="flex items-center justify-between gap-3 border-t border-line-soft pt-3.5">
                    <x-price :package="$package" size="sm" :from="false" />
                    <x-button :href="route('contact', ['package' => $package->slug])" @click.prevent="$dispatch('open-enquiry', {{ $package->id }})" aria-haspopup="dialog" size="sm" icon="arrow-right">Buy Now</x-button>
                </div>
            </article>
        @endforeach
    </div>
</div>
