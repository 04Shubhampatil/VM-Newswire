<?php

namespace App\Mail;

use App\Models\Enquiry;
use App\Services\SiteSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerAcknowledgementMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Enquiry $enquiry) {}

    public function envelope(): Envelope
    {
        $settings = app(SiteSettings::class);

        return new Envelope(
            subject: 'We received your VM Newswire enquiry',
            replyTo: [new Address((string) $settings->get('company_email'), (string) $settings->get('company_name'))],
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.enquiries.customer', with: [
            'settings' => app(SiteSettings::class),
        ]);
    }
}
