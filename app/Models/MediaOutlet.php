<?php

namespace App\Models;

use Database\Factories\MediaOutletFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'name', 'slug', 'website_url', 'logo_url', 'logo_path', 'poster_path', 'poster_width', 'poster_height',
    'category', 'short_description', 'description', 'is_active', 'is_highlighted', 'homepage_order', 'display_order',
])]
class MediaOutlet extends Model
{
    /** @use HasFactory<MediaOutletFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_highlighted' => 'boolean',
            'display_order' => 'integer',
            'poster_width' => 'integer',
            'poster_height' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (MediaOutlet $outlet) {
            if (blank($outlet->slug)) {
                $outlet->slug = static::uniqueSlug($outlet->name, $outlet->id);
            }
        });
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'outlet';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function scopeActive(Builder $query): void
    {
        $query->where($query->qualifyColumn('is_active'), true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy($query->qualifyColumn('display_order'))->orderBy($query->qualifyColumn('name'));
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_media')
            ->withPivot(['is_featured', 'display_order'])
            ->withTimestamps();
    }

    /** Uploaded logo wins over an external logo URL. */
    protected function logoSrc(): Attribute
    {
        return Attribute::get(function () {
            if ($this->logo_path) {
                return Storage::disk(config('vmnewswire.media_logos.disk'))->url($this->logo_path);
            }

            return $this->logo_url;
        });
    }

    /** Public URL of the poster, or null when none is uploaded (views render a designed fallback). */
    protected function posterSrc(): Attribute
    {
        return Attribute::get(fn () => $this->poster_path
            ? Storage::disk(config('vmnewswire.posters.disk'))->url($this->poster_path)
            : null);
    }

    /**
     * Plain array for views and the hero modal (no model instances in the cache).
     *
     * @return array{id: int, name: string, slug: string, category: string, short_description: ?string, description: ?string, poster_src: ?string, poster_width: ?int, poster_height: ?int, website_url: ?string}
     */
    /**
     * The package an outlet card links to, chosen from the outlet's active packages (given in display order):
     * a package whose brand or name is the outlet itself (e.g. "Benzinga" → "Benzinga Markets Reach") wins,
     * then the "Most popular" package, then the first in display order.
     *
     * @param  \Illuminate\Support\Collection<int, Package>  $packages
     */
    public function primaryPackage(Collection $packages): ?Package
    {
        $outlet = mb_strtolower(trim($this->name));
        $compact = str_replace(' ', '', $outlet);

        return $packages->sortBy(function (Package $package, int $index) use ($outlet, $compact) {
            $brand = mb_strtolower((string) $package->brand);
            $name = mb_strtolower($package->name);
            $rank = match (true) {
                $brand !== '' && ($brand === $outlet || str_replace(' ', '', $brand) === $compact) => 0,
                str_contains($name, $outlet) || str_contains(str_replace(' ', '', $name), $compact) => 1,
                (bool) $package->is_highlighted => 2,
                default => 3,
            };

            return sprintf('%d-%05d', $rank, $index);
        })->first();
    }

    public function toPosterArray(): array
    {
        $package = $this->primaryPackage($this->packages()->active()->ordered()->get(['packages.id', 'packages.name', 'packages.slug', 'packages.brand', 'packages.is_highlighted']));

        return [
            'package_name' => $package?->name,
            'package_url' => $package ? route('packages.show', $package->slug) : null,
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'category' => $this->category,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'poster_src' => $this->poster_src,
            'poster_width' => $this->poster_width,
            'poster_height' => $this->poster_height,
            'logo_src' => $this->logo_src,
            'website_url' => $this->website_url,
        ];
    }
}
