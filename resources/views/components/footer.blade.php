@php
    // $footerPackages is provided by App\View\Composers\FooterComposer.
    $socials = array_filter([
        'LinkedIn' => $site->get('social_linkedin'),
        'X' => $site->get('social_x'),
        'Facebook' => $site->get('social_facebook'),
    ]);
    $phone = $site->get('phone');
    $whatsapp = $site->get('whatsapp');
    $email = $site->get('company_email');
    $link = 'link-underline self-start text-sm leading-tight text-white/85 hover:text-white';
    $heading = 'mb-1 text-[11px] font-bold tracking-[0.14em] text-muted-on-brand uppercase';
@endphp
<footer class="bg-ink text-white">
    <div class="container-site flex flex-col gap-8 pt-10 pb-6 md:pt-12">
        <div data-reveal class="grid grid-cols-2 gap-8 md:grid-cols-[1.4fr_1fr_1fr] lg:grid-cols-[1.6fr_0.9fr_1.2fr_0.9fr_1.1fr] lg:gap-10">
            <div class="col-span-2 flex max-w-xs flex-col gap-3 md:col-span-3 lg:col-span-1">
                <x-logo dark :size="26" />
                <p class="text-[13.5px] leading-relaxed text-muted-on-brand">{{ $site->get('footer_text') }}</p>
                @if ($socials)
                    <ul class="flex gap-2 pt-1">
                        @foreach ($socials as $label => $url)
                            <li><a href="{{ $url }}" rel="noopener" target="_blank" class="flex h-9 items-center rounded-[6px] border border-white/15 px-3 text-xs hover:border-white">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="flex flex-col gap-2">
                <h2 class="{{ $heading }}">Company</h2>
                <a href="{{ route('about') }}" class="{{ $link }}">About</a>
                <a href="{{ route('media-network') }}" class="{{ $link }}">Media Network</a>
                <a href="{{ route('packages.index') }}" class="{{ $link }}">Packages</a>
                <a href="{{ route('contact') }}" class="{{ $link }}">Contact</a>
            </div>
            <div class="flex flex-col gap-2">
                <h2 class="{{ $heading }}">Packages</h2>
                @foreach ($footerPackages as $package)
                    <a href="{{ route('packages.show', $package->slug) }}" class="{{ $link }}">{{ $package->name }}</a>
                @endforeach
            </div>
            <div class="flex flex-col gap-2">
                <h2 class="{{ $heading }}">Resources</h2>
                <a href="{{ route('sample-reports.index') }}" class="{{ $link }}">Sample Reports</a>
                <a href="{{ route('faq') }}" class="{{ $link }}">FAQ</a>
                <a href="{{ route('privacy') }}" class="{{ $link }}">Privacy Policy</a>
                <a href="{{ route('terms') }}" class="{{ $link }}">Terms</a>
            </div>
            <address class="flex flex-col gap-2 not-italic">
                <h2 class="{{ $heading }}">Contact</h2>
                <a href="mailto:{{ $email }}" class="flex items-center gap-2 text-sm leading-tight text-white/85 hover:text-white hover:underline"><x-icon name="mail" :size="15" class="text-muted-on-brand" />{{ $email }}</a>
                @if ($phone)
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="flex items-center gap-2 text-sm leading-tight text-white/85 hover:text-white hover:underline"><x-icon name="phone" :size="15" class="text-muted-on-brand" />{{ $phone }}</a>
                @endif
                @if ($whatsapp)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}" rel="noopener" target="_blank" class="flex items-center gap-2 text-sm leading-tight text-white/85 hover:text-white hover:underline"><x-icon name="phone" :size="15" class="text-muted-on-brand" />WhatsApp {{ $whatsapp }}</a>
                @endif
                @if ($site->get('address'))
                    <span class="flex gap-2 text-sm leading-snug text-white/85"><x-icon name="pin" :size="15" class="mt-0.5 text-muted-on-brand" />{{ $site->get('address') }}</span>
                @endif
            </address>
        </div>

        <div class="flex flex-col gap-2 border-t border-white/15 pt-4 text-xs text-muted-on-brand sm:flex-row sm:items-center sm:justify-between">
            <span>
                © {{ now()->year }} {{ $site->get('company_name') }}. All rights reserved.
                <span class="mx-1.5 text-white/30" aria-hidden="true">|</span>
                Developed by <a href="https://nivtech.co.in/" target="_blank" rel="noopener" class="font-semibold text-white/85 hover:text-white hover:underline">Nivtech</a>
            </span>
            <span class="flex gap-5">
                <a href="{{ route('privacy') }}" class="hover:text-white hover:underline">Privacy</a>
                <a href="{{ route('terms') }}" class="hover:text-white hover:underline">Terms</a>
            </span>
        </div>
    </div>
</footer>
