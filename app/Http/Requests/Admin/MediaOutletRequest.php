<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MediaOutletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $media = $this->route('media');
        $posters = config('vmnewswire.posters');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('media_outlets', 'name')->ignore($media?->id)],
            'website_url' => ['nullable', 'url:http,https', 'max:255'],
            'logo_url' => ['nullable', 'url:https', 'max:255'],
            // SVG is excluded on purpose: it can carry scripts.
            'logo' => ['nullable', 'file', 'image', 'mimes:png,jpg,jpeg,webp', 'max:'.config('vmnewswire.media_logos.max_kb')],
            'remove_logo' => ['boolean'],
            'poster' => [
                'nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp',
                'max:'.$posters['max_kb'],
                "dimensions:min_width={$posters['min_width']},min_height={$posters['min_height']},max_width=8000,max_height=8000",
            ],
            'remove_poster' => ['boolean'],
            'category' => ['required', Rule::in(config('vmnewswire.media_categories'))],
            'short_description' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['boolean'],
            'is_highlighted' => ['boolean'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ];
    }

    public function messages(): array
    {
        $p = config('vmnewswire.posters');

        return [
            'poster.dimensions' => "The poster must be at least {$p['min_width']}×{$p['min_height']} pixels.",
            'poster.max' => 'The poster may not be larger than '.round($p['max_kb'] / 1024).' MB.',
            'poster.mimes' => 'The poster must be a JPG, PNG or WebP image.',
            'poster.mimetypes' => 'The poster must be a JPG, PNG or WebP image.',
        ];
    }

    public function attributes(): array
    {
        return ['poster' => 'poster image', 'short_description' => 'short description'];
    }
}
