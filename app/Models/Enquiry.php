<?php

namespace App\Models;

use App\Enums\EnquiryStatus;
use Database\Factories\EnquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Number;

#[Fillable([
    'package_id', 'package_name_snapshot', 'package_price_snapshot', 'package_currency_snapshot',
    'name', 'email', 'phone', 'company', 'country', 'release_count', 'message', 'source_page', 'status',
])]
class Enquiry extends Model
{
    /** @use HasFactory<EnquiryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => EnquiryStatus::class,
            'package_price_snapshot' => 'decimal:2',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class)->withTrashed();
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class)->latest('id');
    }

    /**
     * @param  array{search?: ?string, status?: ?string, package?: ?string, from?: ?string, to?: ?string}  $filters
     */
    public function scopeFilter(Builder $query, array $filters): void
    {
        $query
            ->when($filters['search'] ?? null, function (Builder $q, string $search) {
                $q->where(function (Builder $q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($filters['package'] ?? null, function (Builder $q, string $package) {
                $package === 'none' ? $q->whereNull('package_id') : $q->where('package_id', $package);
            })
            ->when($filters['from'] ?? null, fn (Builder $q, string $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $q, string $to) => $q->whereDate('created_at', '<=', $to));
    }

    protected function packageLabel(): Attribute
    {
        return Attribute::get(fn () => $this->package_name_snapshot ?? $this->package?->name ?? 'General enquiry');
    }

    protected function formattedPriceSnapshot(): Attribute
    {
        return Attribute::get(fn () => $this->package_price_snapshot === null
            ? null
            : Number::currency((float) $this->package_price_snapshot, in: $this->package_currency_snapshot ?: 'USD'));
    }
}
