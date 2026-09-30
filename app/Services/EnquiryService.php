<?php

namespace App\Services;

use App\Enums\EmailDeliveryStatus;
use App\Enums\EmailType;
use App\Enums\EnquiryStatus;
use App\Jobs\SendEnquiryEmails;
use App\Models\Enquiry;
use App\Models\Package;
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
    public function submit(array $data): Enquiry
    {
        $package = filled($data['package_id'] ?? null) ? Package::find($data['package_id']) : null;

        $enquiry = DB::transaction(function () use ($data, $package) {
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
                'status' => EnquiryStatus::New,
            ]);

            $enquiry->emailLogs()->createMany([
                [
                    'email_type' => EmailType::AdminNotification,
                    'recipient' => (string) $this->settings->adminNotificationEmail(),
                    'delivery_status' => EmailDeliveryStatus::Pending,
                ],
                [
                    'email_type' => EmailType::CustomerAcknowledgement,
                    'recipient' => $enquiry->email,
                    'delivery_status' => EmailDeliveryStatus::Pending,
                ],
            ]);

            return $enquiry;
        });

        SendEnquiryEmails::dispatch($enquiry->id);

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
