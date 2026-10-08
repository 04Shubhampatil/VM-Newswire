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
                <h1 class="display text-[36px] leading-[1.1] tracking-[-0.02em] md:text-[48px] lg:text-[60px]">Talk to our <em class="text-accent">distribution team.</em></h1>
                <p class="max-w-[620px] text-base leading-relaxed text-muted md:text-lg">Send an enquiry for any package, or ask us to recommend one. No payment is required — our team will contact you shortly.</p>
            </div>
            <dl class="grid gap-6 border-t border-line pt-6 sm:grid-cols-2 lg:grid-cols-3">
                <div class="flex flex-col gap-1.5">
                    <dt class="label-caps">Email</dt>
                    <dd><a href="mailto:{{ $email }}" class="link-underline text-[17px] font-semibold text-ink">{{ $email }}</a></dd>
                </div>
                @if ($phone || $whatsapp)
                    <div class="flex flex-col gap-1.5">
                        <dt class="label-caps">{{ $whatsapp ? 'Phone / WhatsApp' : 'Phone' }}</dt>
                        <dd><a href="{{ $whatsapp ? 'https://wa.me/'.preg_replace('/[^0-9]/', '', $whatsapp) : 'tel:'.preg_replace('/[^0-9+]/', '', $phone) }}" @if ($whatsapp) target="_blank" rel="noopener" @endif class="link-underline text-[17px] font-semibold text-ink">{{ $whatsapp ?: $phone }}</a></dd>
                    </div>
                @endif
                <div class="flex flex-col gap-1.5">
                    <dt class="label-caps">Response</dt>
                    <dd class="text-[17px] font-semibold text-ink">A named contact replies to every enquiry</dd>
                </div>
            </dl>
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
