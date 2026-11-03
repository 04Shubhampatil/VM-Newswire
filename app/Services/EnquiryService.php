<?php

namespace App\Services;

use App\Enums\EmailDeliveryStatus;
use App\Enums\EmailType;
use App\Enums\EnquiryStatus;
use App\Jobs\SendEnquiryEmails;
use App\Models\Enquiry;
use App\Models\Package;
use App\Support\SpamAssessment;
use Illuminate\Support\Facades\DB;

class EnquiryService
{
    public function __construct(private SiteSettings $settings) {}

    /**
     * Stores the enquiry (with a snapshot of the package name and price) and queues
     * both emails. Emails are only dispatched after the transaction commits.
     *
     * @param  array<string, mixed>  $data  validated form data
     */
    public function submit(array $data, ?SpamAssessment $assessment = null, ?string $ip = null): Enquiry
    {
        $isSpam = $assessment?->isSpam() ?? false;
        $package = filled($data['package_id'] ?? null) ? Package::find($data['package_id']) : null;

        $enquiry = DB::transaction(function () use ($data, $package, $assessment, $isSpam, $ip) {
            $enquiry = Enquiry::create([
                'package_id' => $package?->id,
                'package_name_snapshot' => $package?->name,
                'package_price_snapshot' => $package?->price,
                'package_currency_snapshot' => $package ? $package->currency : null,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'company' => $data['company'] ?? null,
                'country' => $data['country'] ?? null,
                'release_count' => $data['release_count'] ?? null,
                'message' => $data['message'],
                'source_page' => $data['source_page'] ?? null,
                'ip_address' => $ip,
                'status' => $isSpam ? EnquiryStatus::Spam : EnquiryStatus::New,
                'spam_score' => $assessment?->score,
                'spam_reasons' => $assessment && $assessment->reasons ? $assessment->reasons : null,
                'spam_marked_by' => $isSpam ? 'auto' : null,
                'spam_marked_at' => $isSpam ? now() : null,
            ]);

            // Spam: kept for the record, but no confirmation to the (often forged) address and no team alert.
            if ($isSpam) {
                return $enquiry;
            }

            $logs = [[
                'email_type' => EmailType::AdminNotification,
                'recipient' => (string) $this->settings->adminNotificationEmail(),
                'delivery_status' => EmailDeliveryStatus::Pending,
            ]];

            // At most one confirmation per address per day, so the form cannot be used to flood
            // someone else's inbox from our mail account.
            $recentlyAcknowledged = \App\Models\EmailLog::query()
                ->where('email_type', EmailType::CustomerAcknowledgement)
                ->where('recipient', $enquiry->email)
                ->where('created_at', '>=', now()->subDay())
                ->exists();

            if (! $recentlyAcknowledged) {
                $logs[] = [
                    'email_type' => EmailType::CustomerAcknowledgement,
                    'recipient' => $enquiry->email,
                    'delivery_status' => EmailDeliveryStatus::Pending,
                ];
            }

            $enquiry->emailLogs()->createMany($logs);

            return $enquiry;
        });

        if (! $isSpam) {
            SendEnquiryEmails::dispatch($enquiry->id);
        }

        return $enquiry;
    }

    /**
     * Re-queues failed emails for an enquiry (admin "Retry" action).
     */
    public function retryFailedEmails(Enquiry $enquiry): int
    {
        $count = $enquiry->emailLogs()
            ->where('delivery_status', EmailDeliveryStatus::Failed)
            ->update(['delivery_status' => EmailDeliveryStatus::Pending, 'error_message' => null]);

        if ($count > 0) {
            SendEnquiryEmails::dispatch($enquiry->id);
        }

        return $count;
    }
}
