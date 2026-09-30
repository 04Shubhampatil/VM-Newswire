<?php

namespace App\Models;

use App\Enums\EmailDeliveryStatus;
use App\Enums\EmailType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['enquiry_id', 'email_type', 'recipient', 'delivery_status', 'error_message', 'sent_at'])]
class EmailLog extends Model
{
    protected function casts(): array
    {
        return [
            'email_type' => EmailType::class,
            'delivery_status' => EmailDeliveryStatus::class,
            'sent_at' => 'datetime',
        ];
    }

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }
}
