<?php

namespace App\Http\Requests;

use App\Rules\Turnstile;
use App\Support\Altcha;
use App\Support\Captcha;
use App\Support\ImageCaptcha;
use App\Services\SpamDetector;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnquiryRequest extends FormRequest
{
    /** Session key holding when the visitor started the form (set by GET /enquiries/token). */
    public const STARTED_AT = 'enquiry_form_started_at';

    /** A person needs at least this long to fill in the form. */
    public const MIN_SECONDS = 4;

    /** Letters (any script), spaces and . ' - only: no digits, URLs or symbols. */
    private const NAME_PATTERN = "/^[\p{L}\p{M}][\p{L}\p{M} .'\-]*$/u";

    /** Text that looks like a link or a contact handle. */
    private const LINK_PATTERN = '~(https?://|www\.|\.(com|ru|net|org|xyz|top|info|io)\b|t\.me/|@\w{3,})~i';

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'phone' => trim((string) $this->input('phone')),
            'company' => filled($this->input('company')) ? trim((string) $this->input('company')) : null,
            // Keep only the path of the page the form was submitted from.
            'source_page' => $this->sourcePath(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'min:2', 'max:80', 'regex:'.self::NAME_PATTERN, 'not_regex:'.self::LINK_PATTERN],
            'email' => ['required', 'string', 'email:rfc', 'max:190'],
            'phone' => ['required', 'string', 'min:6', 'max:40', 'regex:/^[0-9+().\-\s]+$/'],
            'company' => ['nullable', 'string', 'max:150', 'not_regex:'.self::LINK_PATTERN],
            'country' => ['nullable', 'string', Rule::in(config('vmnewswire.countries'))],
            'release_count' => ['nullable', 'string', Rule::in(config('vmnewswire.enquiries.release_counts'))],
            'package_id' => [
                Rule::requiredIf($this->input('context') === 'package'),
                'nullable',
                'integer',
                Rule::exists('packages', 'id')->where('is_active', true)->whereNull('deleted_at'),
            ],
            'message' => ['required', 'string', 'min:10', 'max:'.config('vmnewswire.enquiries.message_max')],
            'source_page' => ['nullable', 'string', 'max:255'],
        ];

        $captcha = Captcha::driver();

        if ($captcha === 'image') {
            // Compared in passedValidation(), once every other field is valid (each picture can be checked once).
            $rules[ImageCaptcha::FIELD] = ['required', 'string', 'max:20'];
        } elseif ($captcha === 'recaptcha') {
            // Checked with Google in passedValidation(), once every other field is valid: a token can be
            // checked only once, so a visitor fixing a typo does not have to tick the box again.
            $rules[Captcha::RECAPTCHA_FIELD] = ['required', 'string', 'max:4000'];
        } elseif ($captcha === 'turnstile') {
            $rules['cf-turnstile-response'] = ['required', 'string', new Turnstile];
        } elseif ($captcha === 'altcha') {
            $rules[Altcha::FIELD] = ['required', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                if (Altcha::verify($value) === null) {
                    $fail('The human check failed or expired. Please tick the box again.');
                }
            }];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a phone number using digits, spaces and + ( ) - only.',
            'name.regex' => 'Please enter your name using letters only.',
            'name.not_regex' => 'Please enter your name using letters only.',
            'company.not_regex' => 'Please enter the company name without links.',
            'package_id.required' => 'Please choose a package.',
            'package_id.exists' => 'That package is no longer available. Please choose another.',
            'message.min' => 'Please tell us a little more about your press release.',
            'cf-turnstile-response.required' => 'Please complete the security check.',
            'captcha.required' => 'Please type the characters shown in the image.',
            'g-recaptcha-response.required' => 'Please tick “I’m not a robot” before sending.',
            'altcha.required' => 'Please tick “I’m not a robot” before sending.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'full name',
            'email' => 'business email',
            'phone' => 'phone / WhatsApp number',
            'package_id' => 'package',
            'message' => 'PR goals / notes',
        ];
    }

    /**
     * On validation errors, return the visitor to the form rather than the top of the page.
     */
    protected function getRedirectUrl(): string
    {
        return strtok(url()->previous(), '#').'#enquire';
    }

    /**
     * A solved captcha may be used once. Checked only after every other rule passed, so a visitor who
     * fixes a typo can resubmit without solving it again.
     */
    protected function passedValidation(): void
    {
        if (Captcha::driver() === 'image') {
            if (! app(ImageCaptcha::class)->check($this->input(ImageCaptcha::FORM_FIELD), $this->input(ImageCaptcha::FIELD))) {
                throw ValidationException::withMessages([ImageCaptcha::FIELD => 'The characters did not match. Please type the characters in the new image.'])
                    ->redirectTo($this->getRedirectUrl());
            }

            return;
        }

        if (Captcha::driver() === 'recaptcha') {
            if (! Captcha::verifyRecaptcha($this->input(Captcha::RECAPTCHA_FIELD), $this->ip())) {
                throw ValidationException::withMessages([Captcha::RECAPTCHA_FIELD => 'The “I’m not a robot” check failed or expired. Please tick the box again.'])
                    ->redirectTo($this->getRedirectUrl());
            }

            return;
        }

        if (Altcha::enabled()) {
            $data = Altcha::verify($this->input(Altcha::FIELD));
            if ($data === null || ! Altcha::consume((string) $data['challenge'])) {
                throw ValidationException::withMessages([Altcha::FIELD => 'The human check has already been used. Please tick the box again.'])
                    ->redirectTo($this->getRedirectUrl());
            }
        }
    }

    /**
     * Behaviour signals for the spam score (App\Services\SpamDetector). These no longer discard the
     * enquiry: it is saved with status Spam so nothing is lost silently.
     *
     * @return array{honeypot: bool, too_fast: bool, ip: ?string}
     */
    public function spamContext(): array
    {
        // Only judged when the form was started via the token endpoint (the normal JS path).
        $startedAt = $this->hasSession() ? $this->session()->get(self::STARTED_AT) : null;

        return [
            'honeypot' => filled($this->input('website')),
            'too_fast' => is_int($startedAt) && now()->timestamp - $startedAt < self::MIN_SECONDS,
            'ip' => $this->ip(),
        ];
    }

    /** @deprecated use SpamDetector::looksRandom() */
    public static function looksRandom(string $message): bool
    {
        return SpamDetector::looksRandom($message);
    }

    private function sourcePath(): ?string
    {
        $source = (string) $this->input('source_page', '');
        $path = parse_url($source, PHP_URL_PATH);

        return is_string($path) && str_starts_with($path, '/') ? mb_substr($path, 0, 255) : null;
    }
}
