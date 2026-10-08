@props(['summary'])
{{--
    Network reach section. Kicker, heading and copy come from Website Content, the hub card from the
    {company_name} setting, and the node chips from the admin-managed orbit nodes (falling back to the live
    category counts when none are configured).
--}}
@php
    $hubTitle = str_replace('{company_name}', (string) $site->get('company_name'), (string) $site->get('network_hub_title'));
    $nodes = collect($site->networkOrbitNodes());
    if ($nodes->isEmpty()) {
        $nodes = collect($summary)->map(fn (array $row) => [
            'category' => $row['category'],
            'count' => $row['count'].' outlets',
        ]);
    }
@endphp
<section class="border-b border-line bg-canvas py-16 lg:py-24">
    <div class="container-site grid gap-10 lg:grid-cols-12 lg:gap-14">
        <div class="lg:col-span-5" data-reveal-group>
            <p class="eyebrow" data-reveal>{{ $site->get('network_kicker') }}</p>
            <h2 class="headline mt-4 text-[30px] leading-[1.15] sm:text-[38px]" data-reveal>{!! App\Support\Heading::render((string) $site->get('network_heading')) !!}</h2>
            <p class="mt-5 max-w-md text-[16px] leading-[1.75] text-muted" data-reveal>{{ $site->get('network_text') }}</p>
            <a href="{{ route('media-network') }}" class="link-arrow mt-7 inline-flex" data-reveal>Browse the media network</a>
        </div>

        <div class="lg:col-span-7" data-reveal-group>
            <div class="flex flex-col gap-4 rounded-2xl border border-line bg-white p-6 shadow-card sm:flex-row sm:items-center sm:justify-between" data-reveal>
                <div>
                    <p class="text-[11px] font-semibold tracking-[0.16em] text-muted-soft uppercase">{{ $site->get('network_hub_subtitle') }}</p>
                    <p class="mt-2 text-[24px] leading-tight font-bold text-heading">{{ $hubTitle }}</p>
                </div>
                <span class="inline-flex items-center gap-2 rounded-full bg-teal-soft px-4 py-2 text-[13px] font-semibold text-accent-ink">
                    <span class="size-2 rounded-full bg-teal"></span> Simultaneous distribution
                </span>
            </div>

            @if ($nodes->isNotEmpty())
                <ul class="mt-4 grid gap-4 sm:grid-cols-3" data-reveal-group>
                    @foreach ($nodes as $node)
                        <li class="rounded-2xl border border-line bg-white p-5" data-reveal>
                            <p class="text-[15px] leading-snug font-semibold text-heading">{{ $node['category'] }}</p>
                            <p class="mt-2 text-[22px] leading-none font-bold text-accent-ink">{{ $node['count'] }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</section>
