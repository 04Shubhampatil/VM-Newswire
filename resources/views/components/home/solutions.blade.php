{{--
    "Get Results with the Solutions You Need" (reference layout): heading left, intro right, then five audience
    categories, each a spot illustration with a label. Each links to the page that best serves that audience.
--}}
@php
    $solutions = [
        ['art' => 'pr', 'label' => "PR & Corporate\nCommunications", 'url' => route('packages.index')],
        ['art' => 'ir', 'label' => 'IR Professionals', 'url' => route('media-network', ['category' => 'Finance'])],
        ['art' => 'agencies', 'label' => 'Agencies', 'url' => route('contact')],
        ['art' => 'companies', 'label' => 'Public Companies', 'url' => route('media-network', ['category' => 'Markets'])],
        ['art' => 'industry', 'label' => "Industry-Specific\nSolutions", 'url' => route('media-network')],
    ];
@endphp
<section id="solutions" class="border-t border-line bg-white py-16 lg:py-24">
    <div class="container-site">
        <div class="grid gap-6 lg:grid-cols-2 lg:gap-16" data-reveal-group>
            <div data-reveal>
                <span class="accent-bar"></span>
                <h2 class="home-h2 mt-5">Get Results with the <br class="hidden sm:block">Solutions You Need</h2>
            </div>
            <p class="text-[16px] leading-[1.7] text-muted lg:pt-6" data-reveal>
                {{ $site->get('company_name') }} helps PR teams, agencies, investor relations professionals and businesses of all sizes
                reach the right audiences and give their announcements the visibility they deserve.
            </p>
        </div>

        <ul class="mt-12 grid grid-cols-2 gap-x-4 gap-y-10 sm:grid-cols-3 lg:mt-14 lg:grid-cols-5" data-reveal-group>
            @foreach ($solutions as $solution)
                <li data-reveal>
                    <a href="{{ $solution['url'] }}" class="group flex flex-col items-center gap-4 text-center">
                        <x-illustrations.spot :name="$solution['art']" class="size-20 transition duration-200 group-hover:-translate-y-1" />
                        <span class="text-[15px] leading-snug font-semibold whitespace-pre-line text-heading transition group-hover:text-accent-ink">{{ $solution['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</section>
