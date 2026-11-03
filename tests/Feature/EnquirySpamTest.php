<?php

namespace Tests\Feature;

use App\Enums\EmailType;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Requests\StoreEnquiryRequest;
use App\Models\EmailLog;
use App\Models\Enquiry;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Protection against the automated "transfer of N rub." campaign that abused the enquiry form to send
 * our confirmation email to third-party addresses.
 */
class EnquirySpamTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+1 555 010 0199',
            'message' => 'We are launching a product next month.',
            'context' => 'general',
            'source_page' => '/contact',
        ];
    }

    public function test_the_enquiry_endpoint_is_csrf_protected(): void
    {
        // Tests normally skip CSRF checks; switch the real check back on for this request.
        $this->app->instance('env', 'production');
        $this->app->bind(ValidateCsrfToken::class, fn ($app) => new class($app, $app['encrypter']) extends ValidateCsrfToken
        {
            protected function runningUnitTests()
            {
                return false;
            }
        });

        $this->postJson('/enquiries', $this->payload())->assertStatus(419);
        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_token_endpoint_returns_a_fresh_uncached_token_and_starts_the_timer(): void
    {
        $response = $this->getJson('/enquiries/token')->assertOk()->assertJsonStructure(['token']);

        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        $this->assertIsInt(session(StoreEnquiryRequest::STARTED_AT));
    }

    public function test_names_carrying_bait_text_are_rejected(): void
    {
        Queue::fake();
        config(['vmnewswire.enquiries.per_minute' => 50, 'vmnewswire.enquiries.per_day' => 50]);

        foreach (['Вам перевод 187721 руб. забрать', 'Win now https://spam.example', 'Visit www.spam.ru', 'Agent 007'] as $name) {
            $this->postJson('/enquiries', $this->payload(['name' => $name]))->assertStatus(422)->assertJsonValidationErrors('name');
        }

        // Real names in any script still pass.
        foreach (['Jane O\'Neil-Smith', 'Анна Петрова', 'José Álvarez', 'Dr. R. Sharma'] as $name) {
            $this->postJson('/enquiries', $this->payload(['name' => $name, 'email' => uniqid().'@example.com']))->assertCreated();
        }

        $this->assertDatabaseCount('enquiries', 4);
    }

    public function test_random_string_messages_are_kept_as_spam_without_emails(): void
    {
        Queue::fake();

        $this->postJson('/enquiries', $this->payload(['message' => 'IywOV7f 2KzA TxH0LC4 XdGE6CG PtwM nQFSViY']))->assertCreated();

        $this->assertSame(\App\Enums\EnquiryStatus::Spam, \App\Models\Enquiry::sole()->status);
        Queue::assertNothingPushed();
        $this->assertFalse(StoreEnquiryRequest::looksRandom('We need distribution on Yahoo Finance and MSN for our Q3 results.'));
        $this->assertFalse(StoreEnquiryRequest::looksRandom('Launching iPhone and LinkedIn campaigns next week for PRWire.'));
    }

    public function test_submissions_faster_than_a_person_can_type_are_flagged_for_review(): void
    {
        Queue::fake();

        // Too fast on its own (e.g. browser autofill) is not enough for Spam: kept, flagged, emails sent.
        $this->getJson('/enquiries/token');
        $this->postJson('/enquiries', $this->payload())->assertCreated();
        $fast = \App\Models\Enquiry::sole();
        $this->assertSame(\App\Enums\EnquiryStatus::New, $fast->status);
        $this->assertTrue($fast->needsSpamReview());
        $this->assertContains('too_fast', array_column($fast->spam_reasons, 'rule'));

        $this->getJson('/enquiries/token');
        $this->travel(StoreEnquiryRequest::MIN_SECONDS + 1)->seconds();
        $this->postJson('/enquiries', $this->payload(['email' => 'second@example.com']))->assertCreated();
        $this->assertFalse(\App\Models\Enquiry::where('email', 'second@example.com')->sole()->needsSpamReview());
    }

    public function test_only_one_confirmation_email_per_address_per_day(): void
    {
        Queue::fake();

        $this->postJson('/enquiries', $this->payload())->assertCreated();
        $this->postJson('/enquiries', $this->payload(['message' => 'Following up on my earlier enquiry.']))->assertCreated();

        $this->assertDatabaseCount('enquiries', 2);
        $this->assertSame(1, EmailLog::where('email_type', EmailType::CustomerAcknowledgement)->count());
        $this->assertSame(2, EmailLog::where('email_type', EmailType::AdminNotification)->count());

        $this->travel(25)->hours();
        $this->postJson('/enquiries', $this->payload())->assertCreated();
        $this->assertSame(2, EmailLog::where('email_type', EmailType::CustomerAcknowledgement)->count());
    }

    public function test_a_spoofed_forwarded_for_header_does_not_bypass_the_rate_limit(): void
    {
        Queue::fake();
        config(['vmnewswire.enquiries.per_minute' => 2]);

        foreach ([1, 2] as $i) {
            $this->withHeader('X-Forwarded-For', "203.0.113.{$i}")
                ->postJson('/enquiries', $this->payload(['email' => "a{$i}@example.com"]))->assertCreated();
        }

        $this->withHeader('X-Forwarded-For', '203.0.113.99')
            ->postJson('/enquiries', $this->payload(['email' => 'a3@example.com']))->assertStatus(429);
    }
}
