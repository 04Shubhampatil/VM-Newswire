<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Owner-editable site settings (company details, footer, about, legal pages).
 * Values are cached; defaults apply until the owner saves a value.
 */
class SiteSettings
{
    private const CACHE_KEY = 'site_settings.all';

    /** @var array<string, string|null>|null */
    private ?array $loaded = null;

    /**
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        return [
            'company_name' => 'VM Newswire',
            'company_email' => 'sales@vmnewswire.com',
            'phone' => '',
            'whatsapp' => '',
            'address' => '',
            'admin_notification_email' => '',
            'network_size_label' => '200+',
            'hero_eyebrow' => 'Global press release distribution',
            'hero_headline' => 'The News Starts',
            'hero_headline_highlight' => 'Here',
            'hero_text' => "Tell your story, get noticed, and deliver results with VM Newswire's press release distribution services and professional reporting.",
            'hero_primary_label' => 'Send a Press Release',
            'hero_secondary_label' => 'Learn More',
            'hero_reach_label' => 'Multi-region distribution',
            'hero_trust_items' => "Global Reach ({network} Outlets)\nVerified Media Network\nTransparent Proof of Delivery",
            'hero_wire_eyebrow' => 'Central wire release',
            'hero_wire_title' => '{company} Dispatch',
            'hero_wire_subtitle' => 'Your story reaches global media simultaneously',
            'hero_wire_status' => 'Distributing',
            'hero_wire_items' => '',
            'trust_strip_heading' => 'Your press release can appear across {network} leading media outlets.',
            'media_strip_logos' => '',
            'network_kicker' => 'Media network',
            'network_heading' => 'Where your press release *can appear.*',
            'network_text' => 'Our media network connects your announcement with leading news platforms, business publications and digital media outlets.',
            'network_hub_title' => '{company_name}',
            'network_hub_subtitle' => 'Core hub',
            'network_orbit_nodes' => '',
            'news_heading' => '',
            'news_button_label' => 'See All News',
            'confidence_heading' => "Deliver Your News with\nConfidence",
            'confidence_button_label' => 'See Sample Reports',
            'confidence_cards' => '',
            'journalists_eyebrow' => 'Journalists & Media Tools',
            'journalists_heading' => 'Discover Your Next Story',
            'journalists_text' => 'Browse the {company} media network by category and outlet, see which publications carry each package, and find the announcements that matter to your readers.',
            'journalists_link_label' => 'Learn More',
            'journalists_image' => '',
            'packages_eyebrow' => 'Our packages',
            'packages_heading' => 'Compare packages *at a glance.*',
            'packages_text' => 'Find the distribution package that matches your reach, media and reporting requirements.',
            'footer_text' => 'Global press release distribution across leading news platforms, business publications and digital media networks.',
            'social_linkedin' => '',
            'social_x' => '',
            'social_facebook' => '',
            'social_instagram' => '',
            'social_youtube' => '',
            'about_intro' => 'VM Newswire helps companies, agencies and founders distribute press releases across leading news platforms, business publications and digital media networks.',
            'about_body' => 'We package distribution into clear, fixed-price options — so you know which platforms your announcement reaches, what it costs, and what the report will show before you commit.',
            'about_image' => '',
            'media_network_image' => '',
            'media_network_eyebrow' => 'Media network',
            'media_network_heading' => 'Where your press release *can appear.*',
            'media_network_text' => '{company} packages combine recognised newswire and headline publications with an extended network of {network} digital outlets across business, finance, news, technology, markets and digital media.',
            'directory_eyebrow' => 'Outlet directory',
            'directory_heading' => 'Featured publications',
            'directory_text' => 'Filter by category to see which outlets appear in which packages.',
            'privacy_updated_at' => '',
            'privacy_content' => '',
            'terms_updated_at' => '',
            'terms_content' => '',
        ];
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $value = $this->all()[$key] ?? null;

        return filled($value) ? $value : ($default ?? (self::defaults()[$key] ?? null));
    }

    /**
     * @return array<string, string|null>
     */
    public function all(): array
    {
        if ($this->loaded !== null) {
            return $this->loaded;
        }

        try {
            $stored = Cache::rememberForever(self::CACHE_KEY, fn () => Schema::hasTable('site_settings')
                ? SiteSetting::query()->pluck('setting_value', 'setting_key')->all()
                : []);
        } catch (Throwable) {
            // The site must still render (e.g. error pages) if the database is unavailable.
            $stored = [];
        }

        return $this->loaded = array_merge(self::defaults(), array_filter($stored, fn ($v) => $v !== null && $v !== ''));
    }

    /**
     * @param  array<string, string|null>  $values
     */
    public function set(array $values): void
    {
        foreach ($values as $key => $value) {
            SiteSetting::updateOrCreate(['setting_key' => $key], ['setting_value' => $value]);
        }

        $this->flush();
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->loaded = null;
    }

    public function adminNotificationEmail(): ?string
    {
        return $this->get('admin_notification_email')
            ?: config('vmnewswire.admin_notification_email')
            ?: $this->get('company_email');
    }

    public static function logoUrl(?string $pathOrUrl): ?string
    {
        if (blank($pathOrUrl)) {
            return null;
        }

        if (str_starts_with($pathOrUrl, 'http://') || str_starts_with($pathOrUrl, 'https://') || str_starts_with($pathOrUrl, '//') || str_starts_with($pathOrUrl, 'data:')) {
            return $pathOrUrl;
        }

        return Storage::disk(config('vmnewswire.media_logos.disk'))->url($pathOrUrl);
    }

    /**
     * @return array<int, array{name: string, logo: ?string, link: ?string, category: ?string}>
     */
    public function heroWireItems(): array
    {
        $raw = $this->get('hero_wire_items');
        if (blank($raw)) {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return array<int, array{name: string, logo: ?string, link: ?string}>
     */
    public function mediaStripLogos(): array
    {
        $raw = $this->get('media_strip_logos');
        if (blank($raw)) {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    public const DEFAULT_CONFIDENCE_CARDS = [
        ['quote' => 'Every enquiry is answered by a named contact on our team. We confirm the package, timing and details with you personally before anything is distributed.', 'name' => 'The VM Newswire team', 'role' => 'Enquiries & onboarding'],
        ['quote' => 'One fixed price per press release, published up front. You can see the headline platforms and the extended network for every package before you commit.', 'name' => 'The VM Newswire team', 'role' => 'Distribution packages'],
        ['quote' => 'After distribution you receive a report with every placement and live link, ready to forward. Download a sample report before you choose.', 'name' => 'The VM Newswire team', 'role' => 'Reporting'],
    ];

    /**
     * Homepage "Deliver Your News with Confidence" cards; the built-in three until the admin saves their own.
     *
     * @return array<int, array{quote: string, name: string, role: string}>
     */
    public function confidenceCards(): array
    {
        $decoded = json_decode((string) $this->get('confidence_cards'), true);

        return is_array($decoded) && $decoded !== [] ? $decoded : self::DEFAULT_CONFIDENCE_CARDS;
    }

    /**
     * @return array<int, array{category: string, count: string}>
     */
    public function networkOrbitNodes(): array
    {
        $raw = $this->get('network_orbit_nodes');
        if (blank($raw)) {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }
}
