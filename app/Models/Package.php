<?php

namespace App\Models;

use Database\Factories\PackageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

#[Fillable([
    'name', 'slug', 'brand', 'short_description', 'full_content', 'distribution_summary', 'features',
    'price', 'currency', 'is_active', 'is_highlighted', 'display_order', 'meta_title', 'meta_description',
])]
class Package extends Model
{
    /** @use HasFactory<PackageFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_highlighted' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Package $package) {
            if (blank($package->slug)) {
                $package->slug = static::uniqueSlug($package->name, $package->id);
            }
        });

        // Remember the previous slug so old URLs can redirect.
        static::updating(function (Package $package) {
            if ($package->isDirty('slug') && filled($package->getOriginal('slug'))) {
                PackageSlugRedirect::updateOrCreate(
                    ['old_slug' => $package->getOriginal('slug')],
                    ['package_id' => $package->id],
                );
                PackageSlugRedirect::where('old_slug', $package->slug)->delete();
            }
        });
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'package';
        $slug = $base;
        $i = 2;

        while (static::withTrashed()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive(Builder $query): void
    {
        $query->where($query->qualifyColumn('is_active'), true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy($query->qualifyColumn('display_order'))->orderBy($query->qualifyColumn('name'));
    }

    public function mediaOutlets(): BelongsToMany
    {
        return $this->belongsToMany(MediaOutlet::class, 'package_media')
            ->withPivot(['is_featured', 'display_order'])
            ->withTimestamps()
            ->orderByPivot('display_order');
    }

    public function featuredMedia(): BelongsToMany
    {
        return $this->mediaOutlets()->wherePivot('is_featured', true)->where('media_outlets.is_active', true);
    }

    public function networkMedia(): BelongsToMany
    {
        return $this->mediaOutlets()->wherePivot('is_featured', false)->where('media_outlets.is_active', true);
    }

    public function enquiries(): HasMany
    {
        return $this->hasMany(Enquiry::class);
    }

    public function sampleReports(): HasMany
    {
        return $this->hasMany(SampleReport::class);
    }

    public function currentReport(): HasOne
    {
        return $this->hasOne(SampleReport::class)->latestOfMany('uploaded_at');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class)->where('is_active', true)->orderBy('display_order');
    }

    public function slugRedirects(): HasMany
    {
        return $this->hasMany(PackageSlugRedirect::class);
    }

    protected function formattedPrice(): Attribute
    {
        return Attribute::get(fn () => $this->price === null
            ? null
            : Number::currency((float) $this->price, in: $this->currency ?: 'USD', precision: fmod((float) $this->price, 1) ? 2 : 0));
    }

    protected function seoTitle(): Attribute
    {
        return Attribute::get(fn () => $this->meta_title ?: "{$this->name} Press Release Distribution | VM Newswire");
    }

    protected function seoDescription(): Attribute
    {
        return Attribute::get(fn () => $this->meta_description ?: Str::limit($this->short_description, 155));
    }
}
