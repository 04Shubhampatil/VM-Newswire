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
    'spam_score', 'spam_reasons', 'spam_marked_by', 'spam_marked_at', 'ip_address',
])]
class Enquiry extends Model
{
    /** @use HasFactory<EnquiryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => EnquiryStatus::class,
            'spam_reasons' => 'array',
            'spam_marked_at' => 'datetime',
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
     * Without a status filter, Spam is left out (it has its own tab). "flag=review" lists the
     * non-spam enquiries whose spam score is in the review band.
     *
     * @param  array{search?: ?string, status?: ?string, flag?: ?string, package?: ?string, from?: ?string, to?: ?string}  $filters
     */
    public function scopeFilter(Builder $query, array $filters): void
    {
        $query
            ->when(empty($filters['status']), fn (Builder $q) => $q->notSpam())
            ->when(($filters['flag'] ?? null) === 'review', fn (Builder $q) => $q->needsSpamReview())
            ->when($filters['search'] ?? null, function (Builder $q, string $search) {
                $q->where(function (Builder $q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%")
                        ->orWhere('ip_address', $search);
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($filters['package'] ?? null, function (Builder $q, string $package) {
                $package === 'none' ? $q->whereNull('package_id') : $q->where('package_id', $package);
            })
            ->when($filters['from'] ?? null, fn (Builder $q, string $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $q, string $to) => $q->whereDate('created_at', '<=', $to));
    }

    /** Everything except Spam: what the team works through, and what the dashboard counts. */
    public function scopeNotSpam(Builder $query): void
    {
        $query->where($query->qualifyColumn('status'), '!=', EnquiryStatus::Spam->value);
    }

    /** An admin restored it from Spam: their decision wins over the score. */
    public const NOT_SPAM = 'not_spam';

    /** Kept in New but scored as possibly spam: shown with a "Possible spam" badge. */
    public function needsSpamReview(): bool
    {
        return $this->status !== EnquiryStatus::Spam
            && $this->spam_marked_by !== self::NOT_SPAM
            && $this->spam_score !== null
            && $this->spam_score >= (int) config('spam.review_threshold');
    }

    /** Non-spam enquiries in the review band that no admin has cleared yet. */
    public function scopeNeedsSpamReview(Builder $query): void
    {
        $query->notSpam()
            ->where('spam_score', '>=', (int) config('spam.review_threshold'))
            ->where(fn (Builder $q) => $q->whereNull('spam_marked_by')->orWhere('spam_marked_by', '!=', self::NOT_SPAM));
    }

    public function markAsSpam(string $by): void
    {
        $this->forceFill(['status' => EnquiryStatus::Spam, 'spam_marked_by' => $by, 'spam_marked_at' => now()])->save();
    }

    /** Back to New, remembering that an admin judged it genuine (clears the badge; the clean-up skips it). */
    public function restoreFromSpam(): void
    {
        $this->forceFill(['status' => EnquiryStatus::New, 'spam_marked_by' => self::NOT_SPAM, 'spam_marked_at' => now()])->save();
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
