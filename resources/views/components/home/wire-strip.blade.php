@props(['outlets'])
{{--
    Live wire strip directly under the hero: the CMS dispatch copy (hero_wire_*), the admin-configured wire
    feeds, and buttons that open the outlet detail dialog for every highlighted outlet. Preserves the
    Website Content fields (wire items, dispatch copy) and the outlet poster dialog.
--}}
@php
    $company = (string) $site->get('company_name');
    $wireTitle = str_replace('{company}', $company, (string) $site->get('hero_wire_title'));
    $items = collect($site->heroWireItems())->take(3);
@endphp
<section id="wire" class="border-b border-white/10 bg-navy-900 py-9 lg:py-11">
    <div class="container-site grid gap-8 lg:grid-cols-12 lg:items-center lg:gap-10">
        {{-- Dispatch copy --}}
        <div class="lg:col-span-4" data-reveal-group>
            <p class="eyebrow text-accent-on-brand" data-reveal>{{ $site->get('hero_wire_eyebrow') }}</p>
            <h2 class="mt-3 text-[22px] leading-tight font-semibold text-white" data-reveal>{{ $wireTitle }}</h2>
            <p class="mt-1.5 text-[14px] text-muted-on-brand">{{ $site->get('hero_wire_subtitle') }}</p>
            @if ($site->get('hero_wire_status'))
                <span class="mt-4 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1.5 text-[12px] font-semibold text-white">
                    <span class="relative flex size-2" aria-hidden="true"><span class="absolute inline-flex size-full animate-ping rounded-full bg-teal opacity-70"></span><span class="relative inline-flex size-2 rounded-full bg-teal"></span></span>
                    {{ $site->get('hero_wire_status') }}
                </span>
            @endif
        </div>

        {{-- Admin-configured wire feeds --}}
        @if ($items->isNotEmpty())
            <ul class="grid gap-3 sm:grid-cols-3 lg:col-span-5" data-reveal-group>
                @foreach ($items as $item)
                    <li data-reveal>
                        <a href="{{ $item['link'] ?? '#' }}" target="_blank" rel="noopener"
                           class="flex h-full flex-col gap-1 rounded-xl border border-white/10 bg-white/5 px-4 py-3 transition hover:border-teal/50 hover:bg-white/10">
                            <span class="flex items-center gap-2">
                                @if (! empty($item['logo']))
                                    <img src="{{ App\Services\SiteSettings::logoUrl($item['logo']) }}" alt="{{ $item['name'] ?? '' }}" loading="lazy" class="h-4 w-auto max-w-[70px] object-contain">
                                @endif
                                <span class="truncate text-[14px] font-semibold text-white">{{ $item['name'] ?? '' }}</span>
                            </span>
                            @if (! empty($item['category']))
                                <span class="text-[11px] tracking-wide text-muted-on-brand uppercase">{{ $item['category'] }}</span>
                            @endif
                            @if (! empty($item['link']))
                                <span class="truncate text-[11px] text-accent-on-brand">{{ $item['link'] }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="lg:col-span-5"></div>
        @endif

        {{-- Highlighted outlets: open the detail dialog --}}
        @if ($outlets->isNotEmpty())
            <div class="lg:col-span-3" data-reveal>
                <p class="mb-3 text-[11px] font-semibold tracking-[0.16em] text-muted-on-brand uppercase">In the network</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($outlets as $outlet)
                        <button type="button" @click="$dispatch('open-outlet', @js($outlet->slug))" aria-haspopup="dialog"
                                class="rounded-full border border-white/15 bg-white/5 px-3 py-1.5 text-[13px] font-medium text-white/85 transition hover:border-teal hover:text-white">
                            {{ $outlet->name }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <x-outlet-modal :outlets="$outlets" />
</section>
