@props(['packages', 'selected' => null, 'locked' => false, 'heading' => null, 'eyebrow' => 'Enquire now', 'text' => null, 'points' => null])
@php
    $email = $site->get('company_email');
    $phone = $site->get('phone');
    $whatsapp = $site->get('whatsapp');
    $points = $points ?? ['No payment required to enquire', 'Package recommendations from our team', 'Sample report available for every package'];
@endphp
<section id="enquire" class="section-y scroll-mt-20 bg-navy-900 text-canvas">
    <div class="container-site grid gap-10 lg:grid-cols-[440px_minmax(0,1fr)] lg:gap-20">
        <div class="flex flex-col gap-6">
            <p class="eyebrow eyebrow-dark">{{ $eyebrow }}</p>
            <h2 class="display text-[32px] leading-[1.12] text-canvas md:text-[38px] lg:text-[44px]">
                {{ $heading ?? "Let's get your story in front of the right audience." }}
            </h2>
            <p class="text-base leading-relaxed text-muted-on-brand">{{ $text ?? "Tell us about your announcement. You'll get a confirmation email right away, and our team will follow up personally with the right package." }}</p>
            <ul class="flex flex-col gap-3.5 border-t border-white/15 pt-5">
                @foreach ($points as $point)
                    <li class="flex items-center gap-3 text-[15px]"><x-icon name="check" :size="16" :stroke="2.2" class="text-accent-on-brand" />{{ $point }}</li>
                @endforeach
            </ul>
            <address class="flex flex-col gap-2.5 text-[15px] not-italic">
                <a href="mailto:{{ $email }}" class="flex items-center gap-2.5 hover:underline"><x-icon name="mail" :size="16" class="text-muted-on-brand" />{{ $email }}</a>
                @if ($phone)<span class="flex items-center gap-2.5"><x-icon name="phone" :size="16" class="text-muted-on-brand" />{{ $phone }}</span>@endif
                @if ($whatsapp)<span class="flex items-center gap-2.5"><x-icon name="phone" :size="16" class="text-muted-on-brand" />WhatsApp {{ $whatsapp }}</span>@endif
            </address>
        </div>
        <div class="enq-form relative rounded-card bg-white p-5 text-ink shadow-[var(--shadow-elevated)] md:p-8">
            <header class="enq-head">
                <p class="enq-eyebrow">Enquire now</p>
                <h3 class="enq-title">Tell us about your press release.</h3>
                <p class="enq-text">Our team will review your announcement and get back to you with the best distribution options for your needs.</p>
            </header>
            <x-enquiry-form :packages="$packages" :selected="$selected" :locked="$locked" name-label="Full name" />
        </div>
    </div>
</section>
