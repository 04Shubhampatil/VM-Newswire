<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['package_id', 'category', 'question', 'answer', 'is_active', 'display_order'])]
class Faq extends Model
{
    public const CATEGORIES = ['General', 'Packages', 'Sample reports', 'Enquiries'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where($query->qualifyColumn('is_active'), true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy($query->qualifyColumn('display_order'))->orderBy($query->qualifyColumn('id'));
    }
}
