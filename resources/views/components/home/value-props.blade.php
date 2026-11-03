{{--
    "Turn Your News into Headlines" (reference layout): a light teal panel with the heading on the left, the
    intro on the right, then a 2×2 grid of white cards, each with a spot illustration, a title, copy and a small
    link. Every claim maps to something the product does and every link to a registered route.
--}}
@php
    $network = (string) $site->get('network_size_label');
    $items = [
        ['art' => 'readers', 'title' => 'Pick a Package, Not a Guess', 'text' => "Every package shows its price, its headline platforms and the full outlet list before you order — no quotes, no hidden add-ons, {$network} outlets to choose from.", 'cta' => 'Compare Packages', 'url' => route('packages.index')],
        ['art' => 'multimedia', 'title' => 'Know Where Your Story Lands', 'text' => 'Browse the complete media network by category and open any outlet to see which package carries it. What you see is what gets published.', 'cta' => 'Browse the Media Network', 'url' => route('media-network')],
        ['art' => 'financial', 'title' => 'Built for Market-Sensitive News', 'text' => 'Earnings, listings, funding rounds and investor updates go to finance and markets publications first, with a real person checking every release before it goes out.', 'cta' => 'See Finance Outlets', 'url' => route('media-network', ['category' => 'Finance'])],
        ['art' => 'analytics', 'title' => 'Proof in Your Inbox', 'text' => 'After distribution you get a placement report with a live link for every outlet — the same report you can download as a sample before you buy.', 'cta' => 'Download a Sample Report', 'url' => route('sample-reports.index')],
    ];
@endphp
<section class="bg-white pb-16 lg:pb-20">
    <div class="container-site">
        <div class="rounded-3xl bg-[#d9f0ea] px-5 py-12 sm:px-10 lg:px-14 lg:py-16">
            <div class="grid gap-6 lg:grid-cols-2 lg:gap-16" data-reveal-group>
                <div data-reveal>
                    <span class="accent-bar"></span>
                    <h2 class="home-h2 mt-5">Distribution You Can <br class="hidden sm:block">See Before You Pay</h2>
                </div>
                <p class="text-[16px] leading-[1.7] text-muted lg:pt-6" data-reveal>
                    Most newswires hide the outlet list until after you order. We publish ours up front — fixed-price packages, the exact platforms
                    each one reaches, and a sample report showing what you will receive once your release is live.
                </p>
            </div>

            <div class="mt-10 grid gap-5 md:grid-cols-2 lg:mt-12 lg:gap-6" data-reveal-group>
                @foreach ($items as $item)
                    <a href="{{ $item['url'] }}" data-reveal
                       class="group flex flex-col gap-5 rounded-2xl bg-white p-6 shadow-[var(--shadow-card)] transition duration-200 hover:-translate-y-0.5 hover:shadow-[var(--shadow-float)] sm:p-8 lg:flex-row lg:items-start">
                        <x-illustrations.spot :name="$item['art']" class="size-20" />
                        <span class="flex flex-col">
                            <span class="text-[18px] leading-snug font-bold text-heading">{{ $item['title'] }}</span>
                            <span class="mt-2.5 text-[15px] leading-relaxed text-muted">{{ $item['text'] }}</span>
                            <span class="mt-5 flex items-center gap-1.5 text-[14px] font-semibold text-accent-ink">
                                {{ $item['cta'] }} <x-icon name="arrow-right" :size="15" :stroke="2.2" class="transition group-hover:translate-x-1" />
                            </span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
