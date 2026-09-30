<?php

namespace App\Jobs;

use App\Enums\EmailDeliveryStatus;
use App\Enums\EmailType;
use App\Mail\AdminEnquiryMail;
use App\Mail\CustomerAcknowledgementMail;
use App\Models\EmailLog;
use App\Models\Enquiry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Sends every pending email for an enquiry and records the outcome in email_logs.
 * A failed send never deletes or rolls back the enquiry; the admin can retry it.
 */
class SendEnquiryEmails implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public int $enquiryId)
    {
        $this->afterCommit();
    }

    public function handle(): void
    {
        $enquiry = Enquiry::with('package')->find($this->enquiryId);

        if (! $enquiry) {
            return;
        }

        $pending = $enquiry->emailLogs()->where('delivery_status', EmailDeliveryStatus::Pending)->get();

        foreach ($pending as $log) {
            $this->send($enquiry, $log);
        }
    }

    private function send(Enquiry $enquiry, EmailLog $log): void
    {
        if (blank($log->recipient)) {
            $log->update([
                'delivery_status' => EmailDeliveryStatus::Failed,
                'error_message' => 'No recipient address. Set the notification email in Admin → Settings.',
            ]);

            return;
        }

        $mailable = match ($log->email_type) {
            EmailType::AdminNotification => new AdminEnquiryMail($enquiry),
            EmailType::CustomerAcknowledgement => new CustomerAcknowledgementMail($enquiry),
        };

        try {
            Mail::to($log->recipient)->send($mailable);

            $log->update(['delivery_status' => EmailDeliveryStatus::Sent, 'sent_at' => now(), 'error_message' => null]);
        } catch (Throwable $e) {
            Log::error('Enquiry email failed', ['enquiry_id' => $enquiry->id, 'type' => $log->email_type->value, 'error' => $e->getMessage()]);

            $log->update([
                'delivery_status' => EmailDeliveryStatus::Failed,
                'error_message' => Str::limit($e->getMessage(), 1000),
            ]);
        }
    }
}
