@props(['variant' => 'serif'])
{{--
    Closing call to action (24-7 "Ready to Grow Your Brand?"): a rounded blue box inside the container with a light
    heading, one line of copy, a white button and a decorative rising-bars graphic on the right. `variant` is kept
    for backwards compatibility; both variants render the same.
--}}
<section class="bg-white py-16 lg:py-[75px]">
    <div class="container-site">
        <div class="band-blue relative overflow-hidden rounded-[6px] px-8 py-12 text-white sm:px-16 sm:py-14">
            <div class="relative z-10 flex max-w-[640px] flex-col items-start gap-3">
                <h2 class="text-[30px] leading-tight font-semibold text-white sm:text-[36px] lg:text-[42px]">Ready to distribute your story?</h2>
                <p class="text-[18px] leading-relaxed text-white sm:text-[20px]">Send an enquiry today. No payment is required and our team replies personally.</p>
                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('contact') }}" class="btn btn-on-brand" data-track="cta_click" data-track-label="CTA band: Get Started">Get Started</a>
                    <a href="{{ route('packages.index') }}" class="btn border border-white/60 text-white hover:bg-white/10">View Packages</a>
                </div>
            </div>
            {{-- rising bars --}}
            <div class="pointer-events-none absolute right-10 bottom-0 hidden items-end gap-3 lg:flex" aria-hidden="true">
                @foreach ([38, 64, 96, 128, 168, 200] as $h)
                    <span class="w-10 rounded-t-[3px] bg-gradient-to-t from-white/10 to-white/50" style="height: {{ $h }}px"></span>
                @endforeach
            </div>
        </div>
    </div>
</section>
