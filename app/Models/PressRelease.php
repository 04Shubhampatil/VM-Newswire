<?php

namespace App\Models;

use Database\Factories\PressReleaseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * A press release shown in the public Newsroom (/newsroom).
 */
#[Fillable(['title', 'slug', 'category', 'author', 'excerpt', 'body', 'image_path', 'is_published', 'published_at'])]
class PressRelease extends Model
{
    /** @use HasFactory<PressReleaseFactory> */
    use HasFactory;

    public const CATEGORIES = [
        'Business', 'Technology', 'Finance', 'Blockchain', 'Health', 'Lifestyle', 'Real Estate',
        'Entertainment', 'Education', 'Sports', 'Automotive', 'Apps & Software', 'Arts & Design', 'Economics', 'Fashion',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (PressRelease $release) {
            if (blank($release->slug)) {
                $release->slug = static::uniqueSlug($release->title, $release->id);
            }
            if ($release->is_published && $release->published_at === null) {
                $release->published_at = now();
            }
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::limit(Str::slug($title), 90, '') ?: 'press-release';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Visible on the public site: published flag set and the publish date not in the future. */
    public function scopePublished(Builder $query): void
    {
        $query->where($query->qualifyColumn('is_published'), true)
            ->whereNotNull($query->qualifyColumn('published_at'))
            ->where($query->qualifyColumn('published_at'), '<=', now());
    }

    public function scopeLatestFirst(Builder $query): void
    {
        $query->orderByDesc($query->qualifyColumn('published_at'))->orderByDesc($query->qualifyColumn('id'));
    }

    /** Full URL for the poster: bundled asset ("images/…") or an upload on the public disk. */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(function () {
            if (blank($this->image_path)) {
                return null;
            }

            return str_starts_with($this->image_path, 'images/')
                ? asset($this->image_path)
                : Storage::disk(config('vmnewswire.posters.disk'))->url($this->image_path);
        });
    }

    /** Short teaser for cards: the excerpt, or the first sentences of the body. */
    protected function teaser(): Attribute
    {
        return Attribute::get(fn () => Str::limit(trim($this->excerpt ?: preg_replace('/[#*_>`\[\]]+/', '', (string) $this->body)), 150));
    }

    protected function authorName(): Attribute
    {
        return Attribute::get(fn () => $this->author ?: 'VM Newswire');
    }
}
