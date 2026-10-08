<?php

namespace App\Support;

use App\Models\MediaOutlet;
use App\Models\Package;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Small cached reads used on many pages. Flushed whenever packages or outlets change.
 *
 * Only plain arrays are cached (Laravel 13 refuses to unserialize arbitrary classes),
 * and they are returned as lightweight read-only objects for the views.
 */
class CatalogCache
{
    private const KEYS = ['catalog.footer_packages', 'catalog.media_summary', 'catalog.highlighted_outlets'];

    /**
     * @return Collection<int, object{id: int, name: string, slug: string}>
     */
    public static function footerPackages(): Collection
    {
        try {
            $rows = Cache::remember('catalog.footer_packages', 3600, fn () => Package::active()->ordered()->limit(6)
                ->get(['id', 'name', 'slug'])->map->only(['id', 'name', 'slug'])->all());
        } catch (Throwable) {
            $rows = [];
        }

        return collect($rows)->map(fn (array $row) => (object) $row);
    }

    /**
     * Active outlet counts per category, with up to two example names each.
     *
     * @return Collection<int, array{category: string, count: int, examples: array<int, string>}>
     */
    public static function mediaSummary(): Collection
    {
        return collect(Cache::remember('catalog.media_summary', 3600, function () {
            $counts = MediaOutlet::active()->toBase()
                ->selectRaw('category, count(*) as total')
                ->groupBy('category')
                ->pluck('total', 'category');

            return collect(config('vmnewswire.media_categories'))
                ->filter(fn ($category) => $counts->has($category))
                ->map(function ($category) use ($counts) {
                    $top = MediaOutlet::active()->where('category', $category)
                        ->orderByDesc('is_highlighted')->ordered()->limit(4)->get();

                    return [
                        'category' => $category,
                        'count' => (int) $counts[$category],
                        'examples' => $top->take(2)->pluck('name')->all(),
                        // Photo for the category card: the first outlet in it that has a poster.
                        'poster_src' => $top->first(fn ($o) => $o->poster_path)?->poster_src,
                        'description' => config('vmnewswire.media_category_descriptions.'.$category, ''),
                    ];
                })
                ->values()
                ->all();
        }));
    }

    public const HOMEPAGE_CARD_LIMIT = 8;

    /**
     * Outlets shown as the homepage news cards: the most recently added of those the admin marked
     * "Show on homepage", newest first, capped at HOMEPAGE_CARD_LIMIT.
     *
     * @return Collection<int, object{id: int, name: string, slug: string, category: string, short_description: ?string, description: ?string, poster_src: ?string, poster_width: ?int, poster_height: ?int, website_url: ?string}>
     */
    public static function highlightedOutlets(): Collection
    {
        $rows = Cache::remember('catalog.highlighted_outlets', 3600, fn () => MediaOutlet::active()
            ->where('is_highlighted', true)->orderByDesc('created_at')->orderByDesc('id')->limit(self::HOMEPAGE_CARD_LIMIT)
            ->get()->map->toPosterArray()->all());

        return collect($rows)->map(fn (array $row) => (object) $row);
    }

    public static function flush(): void
    {
        foreach (self::KEYS as $key) {
            Cache::forget($key);
        }
    }
}
