<section class="border-t border-line bg-canvas py-20 md:py-24 lg:py-28">
    <div class="container-site flex flex-col gap-6 md:flex-row md:items-center md:justify-between md:gap-12">
        <div class="flex flex-col gap-4">
            <h2 class="display text-4xl leading-[1.12] md:text-[42px] lg:text-[46px]">Ready to distribute <em class="text-accent italic">your story?</em></h2>
            <p class="text-[17px] text-muted">Compare packages, or tell us about your announcement.</p>
        </div>
        <div class="flex shrink-0 flex-col gap-3 sm:flex-row">
            <x-button :href="route('contact')" icon="arrow-right" data-track="cta_click" data-track-label="CTA band: Enquire Now">Enquire Now</x-button>
            <x-button :href="route('packages.index')" variant="secondary">View Packages</x-button>
        </div>
    </div>
</section>
