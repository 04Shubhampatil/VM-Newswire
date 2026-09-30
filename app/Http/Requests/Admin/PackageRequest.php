<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PackageRequest extends FormRequest
{
    public const CURRENCIES = ['USD', 'EUR', 'GBP', 'INR', 'AUD', 'CAD', 'AED', 'SGD'];

    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        // Features are edited as one-per-line text.
        $features = collect(preg_split('/\r\n|\r|\n/', (string) $this->input('features_text')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        $this->merge([
            'slug' => filled($this->input('slug')) ? Str::slug((string) $this->input('slug')) : null,
            'features' => $features,
            'price' => filled($this->input('price')) ? str_replace([',', ' '], '', (string) $this->input('price')) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $package = $this->route('package');

        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:160', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('packages', 'slug')->ignore($package?->id)],
            'brand' => ['nullable', 'string', 'max:64'],
            'short_description' => ['required', 'string', 'max:500'],
            'full_content' => ['nullable', 'string', 'max:20000'],
            'distribution_summary' => ['nullable', 'string', 'max:255'],
            'features' => ['array', 'max:10'],
            'features.*' => ['string', 'max:120'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'currency' => ['required', Rule::in(self::CURRENCIES)],
            'is_active' => ['boolean'],
            'is_highlighted' => ['boolean'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'meta_title' => ['nullable', 'string', 'max:120'],
            'meta_description' => ['nullable', 'string', 'max:320'],
        ];
    }

    public function messages(): array
    {
        return ['slug.regex' => 'Use lowercase letters, numbers and hyphens only.'];
    }

    /**
     * @return array<string, mixed>
     */
    public function packageData(): array
    {
        $data = $this->safe()->except([]);
        $data['display_order'] ??= 0;

        return $data;
    }
}
