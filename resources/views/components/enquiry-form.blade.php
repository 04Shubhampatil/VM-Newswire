@props([
    'packages',              // active packages for the dropdown
    'selected' => null,      // Package pre-selected (package page, or ?package= on /contact)
    'locked' => false,       // true on a package page: the package is required
    'source' => null,        // path the enquiry came from
    'prefix' => 'enq',       // id prefix, so two forms on one page don't share ids
])
@php
    $selectedId = old('package_id', $selected?->id);
    $turnstileKey = config('vmnewswire.turnstile.site_key');
    // Border colour follows server-side errors (no-JS path) and in-page errors (JS path).
    $inputAttrs = fn (string $name) => 'class="field-input'.($errors->has($name) ? ' border-danger' : '').'" '
        .":class=\"err('{$name}') && 'border-danger'\" :aria-invalid=\"err('{$name}') ? 'true' : null\""
        .($errors->has($name) ? ' aria-invalid="true"' : '');
@endphp
<div x-data="enquiryForm(@js($selected?->name ?? ''), @js((object) $errors->getMessages()))" {{ $attributes }}>
    <form method="POST" action="{{ route('enquiries.store') }}" novalidate x-show="!sent"
          @focusin.once="start()" @submit="submit($event)"
          class="grid grid-cols-1 gap-5 md:grid-cols-2">
        @csrf
        <input type="hidden" name="context" value="{{ $locked ? 'package' : 'general' }}">
        <input type="hidden" name="source_page" value="{{ $source ?? request()->getPathInfo() }}">

        {{-- Honeypot: hidden from people, tempting for bots. --}}
        <div class="absolute -left-[9999px]" aria-hidden="true">
            <label for="website">Leave this field empty</label>
            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div role="alert" x-show="general" x-text="general" x-cloak
             class="rounded-[6px] border border-danger/30 bg-danger-soft px-4 py-3 text-sm text-danger md:col-span-2"></div>
        @if ($errors->any())
            <div role="alert" x-show="!general" class="rounded-[6px] border border-danger/30 bg-danger-soft px-4 py-3 text-sm text-danger md:col-span-2">
                Please check the highlighted fields and try again.
            </div>
        @endif

        @foreach ([
            ['name', 'Name', 'text', 'name', 'Your full name', 120, true],
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
            <label for="{{ $prefix }}-package" class="field-label">Package</label>
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
            <label for="{{ $prefix }}-message" class="field-label">Message</label>
            <textarea id="{{ $prefix }}-message" name="message" rows="5" required maxlength="{{ config('vmnewswire.enquiries.message_max') }}"
                      placeholder="Tell us about your announcement — topic, timing and target audience."
                      aria-describedby="{{ $prefix }}-message-error" {!! $inputAttrs('message') !!}>{{ old('message') }}</textarea>
            <p id="{{ $prefix }}-message-error" class="field-error" x-show="err('message')" x-text="err('message')" @unless ($errors->has('message')) x-cloak @endunless>{{ $errors->first('message') }}</p>
        </div>

        @if ($turnstileKey)
            <div class="md:col-span-2">
                <div class="cf-turnstile" data-sitekey="{{ $turnstileKey }}"></div>
                <p class="field-error" x-show="err('cf-turnstile-response')" x-text="err('cf-turnstile-response')" @unless ($errors->has('cf-turnstile-response')) x-cloak @endunless>{{ $errors->first('cf-turnstile-response') }}</p>
            </div>
        @endif

        <div class="flex flex-col-reverse gap-4 pt-2 md:col-span-2 md:flex-row md:items-center md:justify-between">
            <p class="flex items-center gap-2.5 text-sm text-muted"><x-icon name="lock" :size="16" class="text-success" />No payment required. Our team will contact you shortly.</p>
            <button type="submit" class="btn btn-primary min-w-[196px]" :disabled="submitting" :aria-busy="submitting.toString()">
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
