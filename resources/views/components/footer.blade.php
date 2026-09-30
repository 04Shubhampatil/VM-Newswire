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
@endphp
<footer class="bg-ink text-white">
    <div class="container-site flex flex-col gap-14 pt-16 pb-10 lg:pt-20">
        <div data-reveal class="grid gap-12 lg:grid-cols-[1.7fr_1fr_1fr_1fr_1.3fr]">
            <div class="flex max-w-sm flex-col gap-5">
                <x-logo dark />
                <p class="text-[15px] leading-relaxed text-muted-on-brand">{{ $site->get('footer_text') }}</p>
                @if ($socials)
                    <ul class="flex gap-3">
                        @foreach ($socials as $label => $url)
                            <li><a href="{{ $url }}" rel="noopener" target="_blank" class="flex h-11 items-center rounded-[6px] border border-white/15 px-3.5 text-sm hover:border-white">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-10 sm:grid-cols-3 lg:contents">
                <div class="flex flex-col gap-3.5">
                    <h2 class="text-[11px] font-bold tracking-[0.16em] text-muted-on-brand uppercase">Company</h2>
                    <a href="{{ route('about') }}" class="link-underline self-start text-[15px] hover:text-white">About</a>
                    <a href="{{ route('media-network') }}" class="link-underline self-start text-[15px] hover:text-white">Media Network</a>
                    <a href="{{ route('packages.index') }}" class="link-underline self-start text-[15px] hover:text-white">Packages</a>
                    <a href="{{ route('contact') }}" class="link-underline self-start text-[15px] hover:text-white">Contact</a>
                </div>
                <div class="flex flex-col gap-3.5">
                    <h2 class="text-[11px] font-bold tracking-[0.16em] text-muted-on-brand uppercase">Packages</h2>
                    @foreach ($footerPackages as $package)
                        <a href="{{ route('packages.show', $package->slug) }}" class="link-underline self-start text-[15px] hover:text-white">{{ $package->name }}</a>
                    @endforeach
                </div>
                <div class="flex flex-col gap-3.5">
                    <h2 class="text-[11px] font-bold tracking-[0.16em] text-muted-on-brand uppercase">Resources</h2>
                    <a href="{{ route('sample-reports.index') }}" class="link-underline self-start text-[15px] hover:text-white">Sample Reports</a>
                    <a href="{{ route('faq') }}" class="link-underline self-start text-[15px] hover:text-white">FAQ</a>
                    <a href="{{ route('privacy') }}" class="link-underline self-start text-[15px] hover:text-white">Privacy Policy</a>
                    <a href="{{ route('terms') }}" class="link-underline self-start text-[15px] hover:text-white">Terms</a>
                </div>
            </div>

            <address class="flex flex-col gap-3.5 not-italic">
                <h2 class="text-[11px] font-bold tracking-[0.16em] text-muted-on-brand uppercase">Contact</h2>
                <a href="mailto:{{ $email }}" class="flex items-center gap-2.5 text-[15px] hover:underline"><x-icon name="mail" :size="16" class="text-muted-on-brand" />{{ $email }}</a>
                @if ($phone)
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="flex items-center gap-2.5 text-[15px] hover:underline"><x-icon name="phone" :size="16" class="text-muted-on-brand" />{{ $phone }}</a>
                @endif
                @if ($whatsapp)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}" rel="noopener" target="_blank" class="flex items-center gap-2.5 text-[15px] hover:underline"><x-icon name="phone" :size="16" class="text-muted-on-brand" />WhatsApp {{ $whatsapp }}</a>
                @endif
                @if ($site->get('address'))
                    <span class="flex gap-2.5 text-[15px] leading-relaxed"><x-icon name="pin" :size="16" class="mt-1 text-muted-on-brand" />{{ $site->get('address') }}</span>
                @endif
            </address>
        </div>

        <div class="flex flex-col gap-3 border-t border-white/15 pt-6 text-[13px] text-muted-on-brand sm:flex-row sm:items-center sm:justify-between">
            <span>© {{ now()->year }} {{ $site->get('company_name') }}. All rights reserved.</span>
            <span class="flex gap-6">
                <a href="{{ route('privacy') }}" class="hover:text-white hover:underline">Privacy</a>
                <a href="{{ route('terms') }}" class="hover:text-white hover:underline">Terms</a>
            </span>
        </div>
    </div>
</footer>
