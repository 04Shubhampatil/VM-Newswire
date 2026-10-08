<x-layouts.public title="Frequently Asked Questions" description="Answers about VM Newswire press release packages, sample reports and how enquiries work.">
    @push('schema')
        @if ($faqs->isNotEmpty())
            <script type="application/ld+json">{!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $faqs->map(fn ($faq) => [
                    '@type' => 'Question',
                    'name' => $faq->question,
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq->answer],
                ])->values()->all(),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
        @endif
    @endpush

    <section x-data="{ category: 'All' }" class="pt-9 pb-16 md:pt-16 md:pb-24 lg:pt-[72px]">
        <div class="container-site flex flex-col gap-10 md:gap-16">
            <div class="flex max-w-[820px] flex-col gap-6">
                <x-breadcrumb :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'FAQ']]" />
                <p class="eyebrow">Help centre</p>
                <h1 class="display text-[36px] leading-[1.1] tracking-[-0.02em] md:text-[48px] lg:text-[60px]">Frequently asked questions</h1>
                <p class="max-w-[620px] text-base leading-relaxed text-muted md:text-lg">Everything you need to know about our packages, sample reports and how enquiries work.</p>
            </div>

            <div class="flex flex-col gap-8 lg:grid lg:grid-cols-[280px_minmax(0,1fr)] lg:items-start lg:gap-24">
                <aside class="flex flex-col gap-5 lg:sticky lg:top-26">
                    <div role="group" aria-label="FAQ categories" class="-mx-4 flex gap-2 overflow-x-auto px-4 md:mx-0 md:px-0 lg:flex-col lg:gap-1">
                        @foreach (collect(['All'])->merge($categories) as $category)
                            <button type="button" @click="category = @js($category)" :aria-pressed="(category === @js($category)).toString()"
                                    :class="category === @js($category) ? 'bg-navy-900 text-white border-navy-900' : 'bg-white text-ink border-line hover:border-navy-900'"
                                    class="flex h-11 shrink-0 items-center rounded-[6px] border px-4 text-left text-sm font-semibold transition">{{ $category }}</button>
                        @endforeach
                    </div>
                    <div class="hidden flex-col gap-3.5 rounded-card bg-navy-900 p-6 text-canvas lg:flex">
                        <h2 class="display text-[28px] leading-tight text-canvas">Still have questions?</h2>
                        <p class="text-sm leading-relaxed text-muted-on-brand">Our team replies to every enquiry personally.</p>
                        <x-button :href="route('contact')" size="sm" class="h-12! w-full">Enquire Now</x-button>
                    </div>
                </aside>

                <div class="border-b border-line">
                    @forelse ($faqs as $faq)
                        <div x-show="category === 'All' || category === @js($faq->category)">
                            <x-faq-item :question="$faq->question" :open="$loop->first">{{ $faq->answer }}</x-faq-item>
                        </div>
                    @empty
                        <p class="py-8 text-muted">No questions yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <x-cta-band />
</x-layouts.public>
