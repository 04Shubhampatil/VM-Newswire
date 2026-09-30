<x-layouts.public title="Contact" description="Send VM Newswire an enquiry about press release distribution. No payment required — our team will contact you shortly.">
    @php
        $email = $site->get('company_email');
        $phone = $site->get('phone');
        $whatsapp = $site->get('whatsapp');
    @endphp
    <section class="pt-9 pb-14 md:pt-16 md:pb-20 lg:pt-[72px]">
        <div class="container-site flex flex-col gap-10 md:gap-12">
            <div class="flex max-w-[820px] flex-col gap-6">
                <x-breadcrumb :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'Contact']]" />
                <p class="eyebrow">Contact</p>
                <h1 class="display text-[36px] leading-[1.1] tracking-[-0.02em] md:text-[48px] lg:text-[60px]">Talk to our <em class="text-accent italic">distribution team.</em></h1>
                <p class="max-w-[620px] text-base leading-relaxed text-muted md:text-lg">Send an enquiry for any package, or ask us to recommend one. No payment is required — our team will contact you shortly.</p>
            </div>
            <ul class="grid gap-4 md:grid-cols-3">
                <li>
                    <a href="mailto:{{ $email }}" class="card-lift flex items-center gap-4 rounded-card border border-line bg-white p-6">
                        <span class="flex size-12 shrink-0 items-center justify-center rounded-[6px] bg-accent-soft text-accent-ink"><x-icon name="mail" :size="20" /></span>
                        <span class="flex min-w-0 flex-col gap-1"><span class="label-caps">Email</span><span class="truncate text-base font-semibold">{{ $email }}</span></span>
                    </a>
                </li>
                @if ($phone || $whatsapp)
                    <li>
                        <a href="{{ $whatsapp ? 'https://wa.me/'.preg_replace('/[^0-9]/', '', $whatsapp) : 'tel:'.preg_replace('/[^0-9+]/', '', $phone) }}" @if ($whatsapp) target="_blank" rel="noopener" @endif class="card-lift flex items-center gap-4 rounded-card border border-line bg-white p-6">
                            <span class="flex size-12 shrink-0 items-center justify-center rounded-[6px] bg-accent-soft text-accent-ink"><x-icon name="phone" :size="20" /></span>
                            <span class="flex flex-col gap-1"><span class="label-caps">{{ $whatsapp ? 'Phone / WhatsApp' : 'Phone' }}</span><span class="text-base font-semibold">{{ $whatsapp ?: $phone }}</span></span>
                        </a>
                    </li>
                @endif
                <li>
                    <a href="{{ route('packages.index') }}" class="card-lift flex items-center gap-4 rounded-card border border-line bg-white p-6">
                        <span class="flex size-12 shrink-0 items-center justify-center rounded-[6px] bg-accent-soft text-accent-ink"><x-icon name="globe" :size="20" /></span>
                        <span class="flex flex-col gap-1"><span class="label-caps">Packages</span><span class="text-base font-semibold">Compare all packages</span></span>
                    </a>
                </li>
            </ul>
        </div>
    </section>

    <x-enquiry-section :packages="$packages" :selected="$selected" />

    @if ($faqs->isNotEmpty())
        <section class="section-y">
            <div class="container-site flex flex-col gap-9 lg:grid lg:grid-cols-[400px_minmax(0,1fr)] lg:items-start lg:gap-[120px]">
                <div class="flex flex-col gap-5">
                    <x-section-heading eyebrow="Before you enquire">Common questions</x-section-heading>
                    <a href="{{ route('faq') }}" class="link-arrow">All FAQs <x-icon name="arrow-right" :size="16" /></a>
                </div>
                <div class="border-b border-line">
                    @foreach ($faqs as $faq)
                        <x-faq-item :question="$faq->question" :open="$loop->first">{{ $faq->answer }}</x-faq-item>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.public>
