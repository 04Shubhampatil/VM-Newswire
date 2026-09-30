<x-layouts.public title="Thank you" noindex :event="['name' => 'enquiry_submitted', 'package_name' => $packageName]">
    <section class="py-20 md:py-28 lg:py-32">
        <div class="container-site flex max-w-[760px] flex-col items-start gap-6">
            <span class="flex size-14 items-center justify-center rounded-full bg-success-soft text-success-ink"><x-icon name="check" :size="28" :stroke="2.4" /></span>
            <p class="eyebrow">Enquiry received</p>
            <h1 class="display text-[36px] leading-[1.1] tracking-[-0.02em] md:text-[48px]">Thank you. <em class="text-accent italic">We're on it.</em></h1>
            <p class="text-lg leading-relaxed text-muted">
                We've received your enquiry{{ $packageName ? ' about '.$packageName : '' }} and sent a confirmation to your email.
                Our team will contact you shortly to discuss your distribution. No payment is required at this stage.
            </p>
            <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                <x-button :href="route('packages.index')" icon="arrow-right">Browse Packages</x-button>
                <x-button :href="route('sample-reports.index')" variant="secondary" icon-left="download">Sample Reports</x-button>
            </div>
        </div>
    </section>
</x-layouts.public>
