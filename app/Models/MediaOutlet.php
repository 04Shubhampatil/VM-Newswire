<?php

namespace App\Models;

use Database\Factories\MediaOutletFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'name', 'slug', 'website_url', 'logo_url', 'logo_path', 'poster_path', 'poster_width', 'poster_height',
    'category', 'short_description', 'description', 'is_active', 'is_highlighted', 'display_order',
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
    public function toPosterArray(): array
    {
        return [
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
