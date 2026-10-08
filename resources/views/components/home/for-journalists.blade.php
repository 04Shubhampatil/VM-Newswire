{{--
    "Discover Your Next Story" (reference layout): eyebrow, heading, copy and a text link on the left; on the
    right, a product screenshot inside a browser frame over a light teal shape. Copy and the screenshot are edited
    under Website Content → Home page; without an upload the bundled media-network screenshot is used.
--}}
@php
    $company = (string) $site->get('company_name');
    $text = str_replace('{company}', $company, (string) $site->get('journalists_text'));
    $linkLabel = (string) $site->get('journalists_link_label') ?: 'Learn More';
    $image = $site->get('journalists_image')
        ? Storage::disk(config('vmnewswire.posters.disk'))->url($site->get('journalists_image'))
        : asset('images/media-network-screenshot.webp');
@endphp
<section id="journalists" class="overflow-hidden bg-white pb-16 lg:pb-24">
    <div class="container-site">
        <div class="grid items-center gap-12 lg:grid-cols-12 lg:gap-16">
            <div class="lg:col-span-5" data-reveal-group>
                <p class="flex items-center gap-3 text-[12px] font-semibold tracking-[0.14em] text-heading uppercase" data-reveal>
                    <span class="accent-bar w-8"></span>{{ $site->get('journalists_eyebrow') }}
                </p>
                <h2 class="home-h2 mt-5" data-reveal>{{ $site->get('journalists_heading') }}</h2>
                <p class="mt-5 max-w-md text-[16px] leading-[1.7] text-muted" data-reveal>{{ $text }}</p>
                <a href="{{ route('media-network') }}" class="link-arrow mt-7 inline-flex" data-reveal
                   data-track="cta_click" data-track-label="Journalists: {{ $linkLabel }}">
                    {{ $linkLabel }} <x-icon name="arrow-right" :size="16" :stroke="2.2" />
                </a>
            </div>

            <div class="relative lg:col-span-7" data-reveal>
                <span class="absolute top-1/2 left-1/2 h-[115%] w-[110%] -translate-x-1/2 -translate-y-1/2 rounded-[48%] bg-[#e9f7f4]" aria-hidden="true"></span>
                <div class="relative overflow-hidden rounded-xl border border-line bg-white shadow-[var(--shadow-float)]">
                    <div class="flex items-center gap-3 border-b border-line bg-[#f5f9fc] px-4 py-2.5">
                        <span class="flex gap-1.5" aria-hidden="true">
                            <span class="size-2.5 rounded-full bg-[#e5a0a0]"></span>
                            <span class="size-2.5 rounded-full bg-[#f0cf8a]"></span>
                            <span class="size-2.5 rounded-full bg-[#a5d6b7]"></span>
                        </span>
                        <span class="flex min-w-0 grow items-center gap-1.5 rounded-full border border-line bg-white px-3 py-1">
                            <x-icon name="lock" :size="11" :stroke="2" class="shrink-0 text-muted-soft" />
                            <span class="truncate text-[12px] text-muted">{{ parse_url(route('media-network'), PHP_URL_HOST) }}/media-network</span>
                        </span>
                    </div>
                    <a href="{{ route('media-network') }}" class="block" tabindex="-1" aria-hidden="true">
                        <img src="{{ $image }}" alt="" width="1280" height="800" loading="lazy" decoding="async"
                             class="aspect-[16/10] w-full object-cover object-top">
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
