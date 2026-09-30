<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

#[Signature('vmn:mail-test {to : Address to send the test message to}')]
#[Description('Send a test email using the MAIL_* settings in .env')]
class MailTest extends Command
{
    public function handle(): int
    {
        $to = (string) $this->argument('to');
        $this->line('Mailer: '.config('mail.default').' · host: '.config('mail.mailers.smtp.host').':'.config('mail.mailers.smtp.port').' · from: '.config('mail.from.address'));

        try {
            Mail::raw(
                "This is a test message from VM Newswire.\n\nIf you can read this, enquiry notifications and customer acknowledgements will be delivered with the current mail settings.",
                fn ($m) => $m->to($to)->subject('VM Newswire mail test')
            );
        } catch (Throwable $e) {
            $this->error('Sending failed: '.$e->getMessage());

            return self::FAILURE;
        }

        if (config('mail.default') === 'log') {
            $this->warn("MAIL_MAILER is 'log': the message was written to storage/logs/laravel.log, not sent. Set MAIL_MAILER=smtp and the MAIL_* values in .env.");
        } else {
            $this->info("Test message sent to {$to}. Check the inbox (and spam folder).");
        }

        return self::SUCCESS;
    }
}
