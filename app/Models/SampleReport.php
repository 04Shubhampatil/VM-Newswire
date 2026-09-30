<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

#[Fillable(['package_id', 'file_name', 'disk', 'file_path', 'file_size', 'uploaded_at'])]
class SampleReport extends Model
{
    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
            'file_size' => 'integer',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    protected function formattedSize(): Attribute
    {
        return Attribute::get(fn () => Number::fileSize($this->file_size, precision: 1));
    }

    /** Filename offered to the visitor on download; never the stored path. */
    protected function downloadName(): Attribute
    {
        return Attribute::get(fn () => 'vm-newswire-'.Str::slug($this->package?->name ?? 'sample').'-sample-report.pdf');
    }
}
