<?php

namespace App\Http\Requests;

use App\Rules\Turnstile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnquiryRequest extends FormRequest
{
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
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:190'],
            'phone' => ['required', 'string', 'min:6', 'max:40', 'regex:/^[0-9+().\-\s]+$/'],
            'company' => ['nullable', 'string', 'max:150'],
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

        if (config('vmnewswire.turnstile.secret_key')) {
            $rules['cf-turnstile-response'] = ['required', 'string', new Turnstile];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a phone number using digits, spaces and + ( ) - only.',
            'package_id.required' => 'Please choose a package.',
            'package_id.exists' => 'That package is no longer available. Please choose another.',
            'message.min' => 'Please tell us a little more about your press release.',
            'cf-turnstile-response.required' => 'Please complete the security check.',
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

    public function isSpam(): bool
    {
        return filled($this->input('website'));
    }

    private function sourcePath(): ?string
    {
        $source = (string) $this->input('source_page', '');
        $path = parse_url($source, PHP_URL_PATH);

        return is_string($path) && str_starts_with($path, '/') ? mb_substr($path, 0, 255) : null;
    }
}
