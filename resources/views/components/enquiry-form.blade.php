@props([
    'packages',              // active packages for the dropdown
    'selected' => null,      // Package pre-selected (package page, or ?package= on /contact)
    'locked' => false,       // true on a package page: the package is required
    'source' => null,        // path the enquiry came from
    'prefix' => 'enq',       // id prefix, so two forms on one page don't share ids
    'compact' => false,      // Stitch home card: single column, dark 40px fields, no company field
    'nameLabel' => 'Name',   // label of the name field (the modal says "Full name")
])
@php
    $selectedId = old('package_id', $selected?->id);
    $turnstileKey = config('vmnewswire.turnstile.site_key');
    // Border colour follows server-side errors (no-JS path) and in-page errors (JS path).
    $inputAttrs = fn (string $name) => 'class="field-input'.($errors->has($name) ? ' border-danger' : '').'" '
        .":class=\"err('{$name}') && 'border-danger'\" :aria-invalid=\"err('{$name}') ? 'true' : null\""
        .($errors->has($name) ? ' aria-invalid="true"' : '');
@endphp
<div x-data="enquiryForm(@js($selected?->name ?? ''), @js((object) $errors->getMessages()))" {{ $attributes->class(['form-compact' => $compact]) }}>
    <form method="POST" action="{{ route('enquiries.store') }}" novalidate x-show="!sent"
          @focusin.once="start()" @submit="submit($event)"
          class="{{ $compact ? 'space-y-4' : 'grid grid-cols-1 gap-5 md:grid-cols-2' }}">
        @csrf
        <input type="hidden" name="context" value="{{ $locked ? 'package' : 'general' }}">
        <input type="hidden" name="source_page" value="{{ $source ?? request()->getPathInfo() }}">

        {{-- Honeypot: hidden from people, tempting for bots. --}}
        <div class="absolute -left-[9999px]" aria-hidden="true">
            <label for="{{ $prefix }}-website">Leave this field empty</label>
            <input type="text" id="{{ $prefix }}-website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div role="alert" x-show="general" x-text="general" x-cloak
             class="rounded-[6px] border border-danger/30 bg-danger-soft px-4 py-3 text-sm text-danger md:col-span-2"></div>
        @if ($errors->any())
            <div role="alert" x-show="!general" class="rounded-[6px] border border-danger/30 bg-danger-soft px-4 py-3 text-sm text-danger md:col-span-2">
                Please check the highlighted fields and try again.
            </div>
        @endif

        @foreach ($compact ? [
            ['name', 'Full Name', 'text', 'name', 'Jane Doe', 120, true],
            ['email', 'Work Email', 'email', 'email', 'jane@company.com', 190, true],
            ['phone', 'Phone / WhatsApp', 'tel', 'tel', '+1 555 000 0000', 40, true],
        ] : [
            ['name', $nameLabel, 'text', 'name', 'Your full name', 120, true],
            ['email', 'Business Email', 'email', 'email', 'you@company.com', 190, true],
            ['phone', 'Phone / WhatsApp', 'tel', 'tel', '+1 555 000 0000', 40, true],
            ['company', 'Company', 'text', 'organization', 'Company or agency', 150, false],
        ] as [$name, $label, $type, $autocomplete, $placeholder, $max, $required])
            <div>
                <label for="{{ $prefix }}-{{ $name }}" class="field-label">{{ $label }}@unless ($required) <span class="font-normal text-muted">(optional)</span>@endunless</label>
                <input id="{{ $prefix }}-{{ $name }}" name="{{ $name }}" type="{{ $type }}" autocomplete="{{ $autocomplete }}" maxlength="{{ $max }}"
                       value="{{ old($name) }}" placeholder="{{ $placeholder }}" @if ($required) required @endif
                       aria-describedby="{{ $prefix }}-{{ $name }}-error" {!! $inputAttrs($name) !!}>
                <p id="{{ $prefix }}-{{ $name }}-error" class="field-error" x-show="err('{{ $name }}')" x-text="err('{{ $name }}')" @unless ($errors->has($name)) x-cloak @endunless>{{ $errors->first($name) }}</p>
            </div>
        @endforeach

        <div class="md:col-span-2">
            <label for="{{ $prefix }}-package" class="field-label">{{ $compact ? 'Distribution Target / Preferred Package' : 'Package' }}</label>
            <select id="{{ $prefix }}-package" name="package_id" @if ($locked) required @endif aria-describedby="{{ $prefix }}-package_id-error" {!! $inputAttrs('package_id') !!}>
                @unless ($locked)<option value="">Not sure — recommend one</option>@endunless
                @foreach ($packages as $package)
                    <option value="{{ $package->id }}" @selected((string) $selectedId === (string) $package->id)>
                        {{ $package->name }}{{ $package->formatted_price ? ' — from '.$package->formatted_price : '' }}
                    </option>
                @endforeach
            </select>
            <p id="{{ $prefix }}-package_id-error" class="field-error" x-show="err('package_id')" x-text="err('package_id')" @unless ($errors->has('package_id')) x-cloak @endunless>{{ $errors->first('package_id') }}</p>
        </div>

        <div class="md:col-span-2">
            <label for="{{ $prefix }}-message" class="field-label">{{ $compact ? 'Brief Announcement Summary' : 'Message' }}</label>
            <textarea id="{{ $prefix }}-message" name="message" rows="{{ $compact ? 3 : 5 }}" required maxlength="{{ config('vmnewswire.enquiries.message_max') }}"
                      placeholder="{{ $compact ? 'Provide a draft link or an outline of your press release…' : 'Tell us about your announcement — topic, timing and target audience.' }}"
                      aria-describedby="{{ $prefix }}-message-error" {!! $inputAttrs('message') !!}>{{ old('message') }}</textarea>
            <p id="{{ $prefix }}-message-error" class="field-error" x-show="err('message')" x-text="err('message')" @unless ($errors->has('message')) x-cloak @endunless>{{ $errors->first('message') }}</p>
        </div>

        @if (\App\Support\Captcha::driver() === 'image')
            {{-- Picture captcha: type the characters shown. The image is loaded by script with a fresh URL (pages are cached). --}}
            <div class="enq-captcha enq-image-captcha md:col-span-2">
                <input type="hidden" name="captcha_form" value="{{ $prefix }}">
                <label for="{{ $prefix }}-captcha" class="field-label">Security check</label>
                <div class="enq-captcha-row">
                    <img class="enq-captcha-img" data-captcha-src="{{ route('enquiries.captcha', $prefix) }}" width="200" height="64"
                         alt="Security check: type the characters shown in this image" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==">
                    <button type="button" class="enq-captcha-reload" data-captcha-reload aria-label="Show a new image">
                        <x-icon name="refresh" :size="14" :stroke="2.2" /> Reload image
                    </button>
                </div>
                <noscript><img src="{{ route('enquiries.captcha', $prefix) }}" width="200" height="64" alt="Security check image"></noscript>
                <input id="{{ $prefix }}-captcha" name="captcha" type="text" inputmode="text" autocomplete="off" autocapitalize="off" spellcheck="false" maxlength="20"
                       placeholder="Type the characters above" required aria-describedby="{{ $prefix }}-captcha-error" {!! $inputAttrs('captcha') !!}>
                <p id="{{ $prefix }}-captcha-error" class="field-error" x-show="err('captcha')" x-text="err('captcha')" @unless ($errors->has('captcha')) x-cloak @endunless>{{ $errors->first('captcha') }}</p>
            </div>
        @elseif (\App\Support\Captcha::driver() === 'recaptcha')
            {{-- Google reCAPTCHA v2 checkbox, rendered by the script below (one widget per form). --}}
            <div class="enq-captcha enq-recaptcha md:col-span-2">
                <div class="g-recaptcha-slot" data-sitekey="{{ config('vmnewswire.recaptcha.site_key') }}"></div>
                <p class="field-error" x-show="err('g-recaptcha-response')" x-text="err('g-recaptcha-response')" @unless ($errors->has('g-recaptcha-response')) x-cloak @endunless>{{ $errors->first('g-recaptcha-response') }}</p>
            </div>
        @elseif ($turnstileKey)
            <div class="md:col-span-2">
                <div class="cf-turnstile" data-sitekey="{{ $turnstileKey }}"></div>
                <p class="field-error" x-show="err('cf-turnstile-response')" x-text="err('cf-turnstile-response')" @unless ($errors->has('cf-turnstile-response')) x-cloak @endunless>{{ $errors->first('cf-turnstile-response') }}</p>
            </div>
        @elseif (\App\Support\Altcha::enabled())
            {{-- Self-hosted "I'm not a robot" check (App\Support\Altcha). Starts solving when the visitor focuses the form. --}}
            <div class="enq-captcha md:col-span-2">
                <altcha-widget challengeurl="{{ route('enquiries.challenge') }}" name="altcha" auto="onfocus" hidelogo hidefooter
                               strings='{"label":"I’m not a robot","verified":"Verified — you’re human","verifying":"Checking…","waitAlert":"Checking… please wait.","error":"Check failed. Please try again.","expired":"Check expired. Please tick again."}'></altcha-widget>
                <p class="field-error" x-show="err('altcha')" x-text="err('altcha')" @unless ($errors->has('altcha')) x-cloak @endunless>{{ $errors->first('altcha') }}</p>
            </div>
        @endif

        <div class="{{ $compact ? '' : 'flex flex-col-reverse gap-4 pt-2 md:col-span-2 md:flex-row md:items-center md:justify-between' }}">
            @unless ($compact)
                <p class="flex items-center gap-2.5 text-sm text-muted"><x-icon name="lock" :size="16" class="text-success" />No payment required. Our team will contact you shortly.</p>
            @endunless
            <button type="submit" class="{{ $compact ? 'flex w-full items-center justify-center gap-1.5 rounded-md bg-accent-fill px-4 py-3 text-xs font-semibold text-white shadow-lg shadow-blue-700/30 transition hover:bg-blue-600 disabled:opacity-60' : 'btn btn-primary min-w-[196px]' }}" :disabled="submitting" :aria-busy="submitting.toString()">
                <span x-text="submitting ? 'Sending…' : 'Submit Enquiry'">Submit Enquiry</span>
                <svg x-show="submitting" x-cloak class="size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="2.5"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                <x-icon name="arrow-right" :size="17" x-show="!submitting" />
            </button>
        </div>
    </form>

    {{-- In-page success state --}}
    <div x-show="sent" x-cloak x-ref="success" tabindex="-1" role="status" aria-live="polite"
         x-transition:enter="transition duration-500 ease-out" x-transition:enter-start="opacity-0 translate-y-2"
         class="flex flex-col items-start gap-5 py-6 outline-none md:py-10">
        <template x-if="sent">
            <svg class="check-draw size-16" viewBox="0 0 64 64" fill="none" aria-hidden="true">
                <circle cx="32" cy="32" r="29" pathLength="1" stroke="#3F8F63" stroke-width="2.5"/>
                <path d="M20 33.5l8 8 16-17" pathLength="1" stroke="#3F8F63" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </template>
        <h3 class="display text-[30px] leading-[1.12] md:text-[36px]">Thank you. Your enquiry has been received.</h3>
        <p class="max-w-xl text-base leading-relaxed text-muted">
            We've sent a confirmation to your email<span x-show="sentPackage"> about <strong class="font-semibold text-ink" x-text="sentPackage"></strong></span>.
            Our team will contact you shortly — no payment is required at this stage.
        </p>
        <div class="flex flex-col gap-3 sm:flex-row">
            <x-button :href="route('packages.index')" variant="dark" size="sm" icon="arrow-right">Browse packages</x-button>
            <button type="button" @click="again()" class="btn btn-secondary btn-sm">Send another enquiry</button>
        </div>
    </div>
</div>

@once
    {{-- Pages are served from the page cache, so the CSRF token in the HTML belongs to another session.
         Fetch a fresh token (GET /enquiries/token, never cached) when the visitor starts on a form, and hold
         the submit until it has arrived. --}}
    @if (\App\Support\Captcha::driver() === 'recaptcha')
        <script>
            // Render one reCAPTCHA checkbox per enquiry form (the popup and the page form can both be on a page).
            window.vmnRecaptchaReady = function () {
                document.querySelectorAll('.g-recaptcha-slot').forEach(function (slot) {
                    if (slot.dataset.widgetId !== undefined) return;
                    slot.dataset.widgetId = grecaptcha.render(slot, { sitekey: slot.dataset.sitekey });
                });
            };
        </script>
        <script src="https://www.google.com/recaptcha/api.js?onload=vmnRecaptchaReady&render=explicit" async defer></script>
    @elseif (\App\Support\Altcha::enabled())
        <script type="module" src="https://cdn.jsdelivr.net/npm/altcha@1.5.1/dist/altcha.js" async></script>
    @endif
    <script>
        (function () {
            var action = @json(route('enquiries.store'));
            var tokenUrl = @json(route('enquiries.token'));
            var pending = null;
            var ready = false;

            function isEnquiryForm(el) { return el && el.tagName === 'FORM' && el.action === action; }

            function refresh() {
                if (pending) return pending;
                pending = fetch(tokenUrl, { credentials: 'same-origin', headers: { Accept: 'application/json' }, cache: 'no-store' })
                    .then(function (r) { return r.ok ? r.json() : null; })
                    .then(function (data) {
                        if (data && data.token) {
                            document.querySelectorAll('form').forEach(function (f) {
                                if (isEnquiryForm(f)) { var i = f.querySelector('input[name="_token"]'); if (i) i.value = data.token; }
                            });
                            var meta = document.querySelector('meta[name="csrf-token"]');
                            if (meta) meta.setAttribute('content', data.token);
                            ready = true;
                        } else { pending = null; }
                    })
                    .catch(function () { pending = null; });
                return pending;
            }

            ['focusin', 'pointerdown'].forEach(function (type) {
                document.addEventListener(type, function (e) {
                    var form = e.target && e.target.closest ? e.target.closest('form') : null;
                    if (isEnquiryForm(form)) refresh();
                }, true);
            });
            window.addEventListener('open-enquiry', refresh);

            // Human check: a solved puzzle can be used once, so start over after the form is reset
            // ("Send another enquiry") or when the server reports the check as failed or used.
            // Picture captcha: load a fresh image per form (cache-busting URL), and on "Reload image".
            function reloadImage(form, clear) {
                var img = form && form.querySelector('.enq-captcha-img');
                if (! img) return;
                img.src = img.dataset.captchaSrc + '?t=' + Date.now() + Math.random().toString(36).slice(2, 7);
                var input = form.querySelector('input[name="captcha"]');
                if (clear && input) input.value = '';
            }
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('form').forEach(function (f) { if (isEnquiryForm(f)) reloadImage(f, false); });
            });
            document.addEventListener('click', function (e) {
                var btn = e.target.closest ? e.target.closest('[data-captcha-reload]') : null;
                if (! btn) return;
                e.preventDefault();
                var form = btn.closest('form');
                reloadImage(form, true);
                var input = form && form.querySelector('input[name="captcha"]');
                if (input) input.focus();
            });

            function resetCaptcha(form) {
                reloadImage(form, true);
                var widget = form && form.querySelector('altcha-widget');
                if (widget && typeof widget.reset === 'function') widget.reset();
                var slot = form && form.querySelector('.g-recaptcha-slot');
                if (slot && slot.dataset.widgetId !== undefined && window.grecaptcha) window.grecaptcha.reset(Number(slot.dataset.widgetId));
            }
            document.addEventListener('reset', function (e) { if (isEnquiryForm(e.target)) resetCaptcha(e.target); }, true);
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.enq-captcha .field-error').forEach(function (p) {
                    new MutationObserver(function () {
                        if (p.textContent.trim() !== '') resetCaptcha(p.closest('form'));
                    }).observe(p, { childList: true, characterData: true, subtree: true });
                });
            });

            // Runs before the form's own submit handler; waits for the fresh token, then submits again.
            document.addEventListener('submit', function (e) {
                var form = e.target;
                if (! isEnquiryForm(form) || ready || form.dataset.tokenWaited) return;
                e.preventDefault();
                e.stopImmediatePropagation();
                form.dataset.tokenWaited = '1'; // wait at most once, even if the token request fails
                refresh().then(function () { if (form.requestSubmit) form.requestSubmit(); else form.submit(); });
            }, true);
        })();
    </script>
@endonce
