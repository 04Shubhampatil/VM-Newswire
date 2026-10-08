<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaOutlet;
use App\Services\PosterImage;
use App\Services\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class ContentController extends Controller
{
    /** Website content is edited one page at a time; each section is its own tab and form. */
    public const SECTIONS = ['home', 'about', 'media-network', 'footer-legal'];

    public function edit(SiteSettings $settings, string $section = 'home'): View
    {
        return view('admin.content.'.$section, [
            'settings' => $settings,
            'section' => $section,
        ]);
    }

    public function update(Request $request, SiteSettings $settings, PosterImage $images): RedirectResponse
    {
        $posters = config('vmnewswire.posters');
        $imageRule = [
            'nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp',
            'max:'.$posters['max_kb'],
            "dimensions:min_width={$posters['min_width']},min_height={$posters['min_height']},max_width=8000,max_height=8000",
        ];
        $logoRule = [
            'nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048',
        ];

        $data = $request->validate([
            'section' => ['nullable', 'string', 'max:30'],
            'hero_eyebrow' => ['nullable', 'string', 'max:80'],
            'hero_headline' => ['nullable', 'string', 'max:120'],
            'hero_headline_highlight' => ['nullable', 'string', 'max:120'],
            'hero_text' => ['nullable', 'string', 'max:400'],
            'hero_primary_label' => ['nullable', 'string', 'max:40'],
            'hero_secondary_label' => ['nullable', 'string', 'max:40'],
            'hero_reach_label' => ['nullable', 'string', 'max:60'],
            'hero_trust_items' => ['nullable', 'string', 'max:300'],
            'hero_wire_eyebrow' => ['nullable', 'string', 'max:80'],
            'hero_wire_title' => ['nullable', 'string', 'max:120'],
            'hero_wire_subtitle' => ['nullable', 'string', 'max:200'],
            'hero_wire_status' => ['nullable', 'string', 'max:50'],
            'hero_wire_items_present' => ['nullable', 'string'],
            'wire_items' => ['nullable', 'array'],
            'wire_items.*.name' => ['nullable', 'string', 'max:120'],
            'wire_items.*.link' => ['nullable', 'string', 'max:500'],
            'wire_items.*.category' => ['nullable', 'string', 'max:80'],
            'wire_items.*.existing_logo' => ['nullable', 'string', 'max:500'],
            'wire_logos.*' => $logoRule,
            'trust_strip_heading' => ['nullable', 'string', 'max:120'],
            'media_strip_logos_present' => ['nullable', 'string'],
            'strip_logos' => ['nullable', 'array'],
            'strip_logos.*.name' => ['nullable', 'string', 'max:120'],
            'strip_logos.*.link' => ['nullable', 'string', 'max:500'],
            'strip_logos.*.existing_logo' => ['nullable', 'string', 'max:500'],
            'strip_logo_files.*' => $logoRule,
            'network_kicker' => ['nullable', 'string', 'max:80'],
            'network_heading' => ['nullable', 'string', 'max:160'],
            'network_text' => ['nullable', 'string', 'max:500'],
            'network_hub_title' => ['nullable', 'string', 'max:120'],
            'network_hub_subtitle' => ['nullable', 'string', 'max:80'],
            'network_orbit_nodes_present' => ['nullable', 'string'],
            'orbit_nodes' => ['nullable', 'array'],
            'orbit_nodes.*.category' => ['nullable', 'string', 'max:80'],
            'orbit_nodes.*.count' => ['nullable', 'string', 'max:50'],
            'homepage_outlets_present' => ['nullable', 'string'],
            'homepage_outlets' => ['nullable', 'array'],
            'homepage_outlets.*' => ['integer', 'exists:media_outlets,id'],
            'news_heading' => ['nullable', 'string', 'max:160'],
            'news_button_label' => ['nullable', 'string', 'max:40'],
            'confidence_heading' => ['nullable', 'string', 'max:160'],
            'confidence_button_label' => ['nullable', 'string', 'max:40'],
            'confidence_cards_present' => ['nullable', 'string'],
            'confidence_cards' => ['nullable', 'array', 'max:6'],
            'confidence_cards.*.quote' => ['nullable', 'string', 'max:400'],
            'confidence_cards.*.name' => ['nullable', 'string', 'max:80'],
            'confidence_cards.*.role' => ['nullable', 'string', 'max:80'],
            'confidence_cards.*.existing_logo' => ['nullable', 'string', 'max:500'],
            'confidence_card_logos.*' => $logoRule,
            'journalists_eyebrow' => ['nullable', 'string', 'max:80'],
            'journalists_heading' => ['nullable', 'string', 'max:120'],
            'journalists_text' => ['nullable', 'string', 'max:500'],
            'journalists_link_label' => ['nullable', 'string', 'max:40'],
            'journalists_image' => $imageRule,
            'remove_journalists_image' => ['boolean'],
            'packages_eyebrow' => ['nullable', 'string', 'max:80'],
            'packages_heading' => ['nullable', 'string', 'max:160'],
            'packages_text' => ['nullable', 'string', 'max:300'],
            'about_intro' => ['sometimes', 'required', 'string', 'max:600'],
            'about_body' => ['nullable', 'string', 'max:10000'],
            'about_image' => $imageRule,
            'remove_about_image' => ['boolean'],
            'media_network_image' => $imageRule,
            'media_network_eyebrow' => ['nullable', 'string', 'max:80'],
            'media_network_heading' => ['nullable', 'string', 'max:160'],
            'media_network_text' => ['nullable', 'string', 'max:500'],
            'directory_eyebrow' => ['nullable', 'string', 'max:80'],
            'directory_heading' => ['nullable', 'string', 'max:120'],
            'directory_text' => ['nullable', 'string', 'max:300'],
            'remove_media_network_image' => ['boolean'],
            'footer_text' => ['sometimes', 'required', 'string', 'max:300'],
            'privacy_content' => ['nullable', 'string', 'max:50000'],
            'privacy_updated_at' => ['nullable', 'date'],
            'terms_content' => ['nullable', 'string', 'max:50000'],
            'terms_updated_at' => ['nullable', 'date'],
        ]);

        $logoDisk = config('vmnewswire.media_logos.disk', 'public');
        $logoDir = config('vmnewswire.media_logos.directory', 'media-logos');

        // Wire items processing
        if ($request->has('wire_items') || $request->has('hero_wire_items_present')) {
            $wireItems = [];
            foreach ($request->input('wire_items', []) as $i => $item) {
                $logo = $item['existing_logo'] ?? null;
                if ($request->hasFile("wire_logos.{$i}")) {
                    $file = $request->file("wire_logos.{$i}");
                    $ext = $file->getClientOriginalExtension() ?: 'png';
                    $logo = $file->storeAs($logoDir, 'wire-'.Str::random(12).'.'.$ext, $logoDisk);
                }
                $name = trim($item['name'] ?? '');
                $link = trim($item['link'] ?? '');
                $category = trim($item['category'] ?? '');

                if ($name !== '' || !empty($logo)) {
                    $wireItems[] = [
                        'name' => $name,
                        'link' => $link,
                        'category' => $category,
                        'logo' => $logo,
                    ];
                }
            }
            $data['hero_wire_items'] = count($wireItems) > 0 ? json_encode($wireItems) : '';
        }
        unset($data['wire_items'], $data['wire_logos'], $data['hero_wire_items_present']);

        // Media strip logos processing
        if ($request->has('strip_logos') || $request->has('media_strip_logos_present')) {
            $stripLogos = [];
            foreach ($request->input('strip_logos', []) as $i => $item) {
                $logo = $item['existing_logo'] ?? null;
                if ($request->hasFile("strip_logo_files.{$i}")) {
                    $file = $request->file("strip_logo_files.{$i}");
                    $ext = $file->getClientOriginalExtension() ?: 'png';
                    $logo = $file->storeAs($logoDir, 'strip-'.Str::random(12).'.'.$ext, $logoDisk);
                }
                $name = trim($item['name'] ?? '');
                $link = trim($item['link'] ?? '');

                if ($name !== '' || !empty($logo)) {
                    $stripLogos[] = [
                        'name' => $name,
                        'link' => $link,
                        'logo' => $logo,
                    ];
                }
            }
            $data['media_strip_logos'] = count($stripLogos) > 0 ? json_encode($stripLogos) : '';
        }
        unset($data['strip_logos'], $data['strip_logo_files'], $data['media_strip_logos_present']);

        // Homepage news cards: the checklist sets "show on homepage" for every active outlet in one go.
        if ($request->has('homepage_outlets_present')) {
            $chosen = array_map('intval', $request->input('homepage_outlets', []));
            foreach (MediaOutlet::active()->get(['id', 'slug', 'is_highlighted']) as $outlet) {
                $show = in_array($outlet->id, $chosen, true);
                if ($outlet->is_highlighted !== $show) {
                    $outlet->update(['is_highlighted' => $show]);
                }
            }
        }
        unset($data['homepage_outlets'], $data['homepage_outlets_present']);

        // Confidence cards: keep every card with a quote; an empty list restores the built-in cards.
        $cardsJson = null;
        if ($request->has('confidence_cards') || $request->has('confidence_cards_present')) {
            $cards = [];
            foreach ($request->input('confidence_cards', []) as $i => $card) {
                $quote = trim($card['quote'] ?? '');
                if ($quote === '') {
                    continue;
                }
                $logo = $card['existing_logo'] ?? null;
                if ($request->hasFile("confidence_card_logos.{$i}")) {
                    $file = $request->file("confidence_card_logos.{$i}");
                    $logo = $file->storeAs($logoDir, 'card-'.Str::random(12).'.'.($file->getClientOriginalExtension() ?: 'png'), $logoDisk);
                }
                $cards[] = ['quote' => $quote, 'name' => trim($card['name'] ?? ''), 'role' => trim($card['role'] ?? ''), 'logo' => $logo ?: null];
            }
            $cardsJson = $cards ? json_encode($cards) : '';
        }
        unset($data['confidence_cards'], $data['confidence_cards_present'], $data['confidence_card_logos']);
        if ($cardsJson !== null) {
            $data['confidence_cards'] = $cardsJson;
        }

        // Network orbit nodes processing
        if ($request->has('orbit_nodes') || $request->has('network_orbit_nodes_present')) {
            $orbitNodes = [];
            foreach ($request->input('orbit_nodes', []) as $node) {
                $cat = trim($node['category'] ?? '');
                $cnt = trim($node['count'] ?? '');
                if ($cat !== '' || $cnt !== '') {
                    $orbitNodes[] = [
                        'category' => $cat,
                        'count' => $cnt,
                    ];
                }
            }
            $data['network_orbit_nodes'] = count($orbitNodes) > 0 ? json_encode($orbitNodes) : '';
        }
        unset($data['orbit_nodes'], $data['network_orbit_nodes_present'], $data['section']);

        // Page photos: setting key => file name prefix. A new image is stored first; the old file is only deleted after that succeeds.
        foreach (['about_image' => 'about', 'media_network_image' => 'media-network-hero', 'journalists_image' => 'journalists'] as $key => $prefix) {
            unset($data[$key], $data['remove_'.$key]);
            $old = $settings->get($key);

            try {
                if ($request->hasFile($key)) {
                    $data[$key] = $images->store($request->file($key), $prefix)['path'];
                    $images->delete($old);
                } elseif ($request->boolean('remove_'.$key) && $old) {
                    $data[$key] = '';
                    $images->delete($old);
                }
            } catch (RuntimeException $e) {
                return back()->withInput()->withErrors([$key => $e->getMessage()]);
            }
        }

        $settings->set($data);

        return back()->with('toast', 'Website content saved.');
    }
}
