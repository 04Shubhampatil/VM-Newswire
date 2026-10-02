<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Services\PosterImage;
use App\Services\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class ContentController extends Controller
{
    public function edit(SiteSettings $settings): View
    {
        return view('admin.content', [
            'settings' => $settings,
            'faqs' => Faq::with('package:id,name')->ordered()->get(),
        ]);
    }

    public function update(Request $request, SiteSettings $settings, PosterImage $images): RedirectResponse
    {
        $posters = config('vmnewswire.posters');

        $data = $request->validate([
            'hero_eyebrow' => ['nullable', 'string', 'max:80'],
            'hero_headline' => ['nullable', 'string', 'max:120'],
            'hero_headline_highlight' => ['nullable', 'string', 'max:120'],
            'hero_text' => ['nullable', 'string', 'max:400'],
            'hero_primary_label' => ['nullable', 'string', 'max:40'],
            'hero_secondary_label' => ['nullable', 'string', 'max:40'],
            'hero_trust_items' => ['nullable', 'string', 'max:300'],
            'trust_strip_heading' => ['nullable', 'string', 'max:120'],
            'packages_eyebrow' => ['nullable', 'string', 'max:80'],
            'packages_heading' => ['nullable', 'string', 'max:160'],
            'packages_text' => ['nullable', 'string', 'max:300'],
            'about_intro' => ['required', 'string', 'max:600'],
            'about_body' => ['nullable', 'string', 'max:10000'],
            'about_image' => [
                'nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp',
                'max:'.$posters['max_kb'],
                "dimensions:min_width={$posters['min_width']},min_height={$posters['min_height']},max_width=8000,max_height=8000",
            ],
            'remove_about_image' => ['boolean'],
            'footer_text' => ['required', 'string', 'max:300'],
            'privacy_content' => ['nullable', 'string', 'max:50000'],
            'privacy_updated_at' => ['nullable', 'date'],
            'terms_content' => ['nullable', 'string', 'max:50000'],
            'terms_updated_at' => ['nullable', 'date'],
        ]);

        unset($data['about_image'], $data['remove_about_image']);
        $old = $settings->get('about_image');

        // New image is stored first; the old file is only deleted after that succeeds.
        try {
            if ($request->hasFile('about_image')) {
                $data['about_image'] = $images->store($request->file('about_image'), 'about')['path'];
                $images->delete($old);
            } elseif ($request->boolean('remove_about_image') && $old) {
                $data['about_image'] = '';
                $images->delete($old);
            }
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['about_image' => $e->getMessage()]);
        }

        $settings->set($data);

        return back()->with('toast', 'Website content saved.');
    }
}
