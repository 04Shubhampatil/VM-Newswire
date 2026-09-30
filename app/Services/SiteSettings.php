<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
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
            'footer_text' => 'Global press release distribution across leading news platforms, business publications and digital media networks.',
            'social_linkedin' => '',
            'social_x' => '',
            'social_facebook' => '',
            'about_intro' => 'VM Newswire helps companies, agencies and founders distribute press releases across leading news platforms, business publications and digital media networks.',
            'about_body' => 'We package distribution into clear, fixed-price options — so you know which platforms your announcement reaches, what it costs, and what the report will show before you commit.',
            'about_image' => '',
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
}
