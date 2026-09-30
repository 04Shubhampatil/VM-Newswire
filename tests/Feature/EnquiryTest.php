<?php

namespace Tests\Feature;

use App\Enums\EmailDeliveryStatus;
use App\Enums\EmailType;
use App\Jobs\SendEnquiryEmails;
use App\Mail\AdminEnquiryMail;
use App\Mail\CustomerAcknowledgementMail;
use App\Models\Enquiry;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

class EnquiryTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+1 555 010 0199',
            'company' => 'Example Co',
            'country' => 'United States',
            'release_count' => '1',
            'message' => 'We are launching a product next month.',
            'context' => 'general',
            'source_page' => 'http://localhost/contact',
        ];
    }

    public function test_required_fields_are_validated(): void
    {
        $this->from('/contact')->post('/enquiries', ['context' => 'general'])
            ->assertRedirect('/contact#enquire')
            ->assertSessionHasErrors(['name', 'email', 'phone', 'message']);

        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_invalid_email_is_rejected(): void
    {
        $this->post('/enquiries', $this->payload(['email' => 'not-an-email']))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_message_length_is_limited(): void
    {
        $this->post('/enquiries', $this->payload(['message' => str_repeat('a', 5001)]))
            ->assertSessionHasErrors('message');
    }

    public function test_valid_general_enquiry_is_stored_and_emails_queued(): void
    {
        Queue::fake();

        $this->post('/enquiries', $this->payload())
            ->assertRedirect(route('enquiries.thanks'));

        $enquiry = Enquiry::sole();
        $this->assertNull($enquiry->package_id);
        $this->assertSame('/contact', $enquiry->source_page);
        $this->assertSame('new', $enquiry->status->value);
        $this->assertCount(2, $enquiry->emailLogs);
        $this->assertTrue($enquiry->emailLogs->every(fn ($log) => $log->delivery_status === EmailDeliveryStatus::Pending));

        Queue::assertPushed(SendEnquiryEmails::class, fn ($job) => $job->enquiryId === $enquiry->id);

        $this->get(route('enquiries.thanks'))->assertOk()->assertSee('Thank you');
    }

    public function test_in_page_submission_returns_json_without_redirect(): void
    {
        Queue::fake();
        $package = Package::factory()->create(['name' => 'MSN']);

        $this->postJson('/enquiries', $this->payload(['package_id' => $package->id, 'context' => 'package']))
            ->assertCreated()
            ->assertJson(['ok' => true, 'message' => 'Thank you. Your enquiry has been received.', 'package' => 'MSN']);

        $this->assertDatabaseCount('enquiries', 1);
        Queue::assertPushed(SendEnquiryEmails::class);
    }

    public function test_forms_record_their_source_page_including_the_home_page(): void
    {
        $this->get('/')->assertSee('name="source_page" value="/"', false);
        $this->get('/contact')->assertSee('name="source_page" value="/contact"', false);
    }

    public function test_home_page_has_the_enquiry_popover_with_its_own_field_ids(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('$dispatch(\'open-enquiry\')', false)
            ->assertSee('id="enquiry-modal-title"', false)
            ->assertSee('id="menq-name"', false)
            ->assertSee('id="enq-name"', false);
    }

    public function test_in_page_submission_returns_field_errors_as_json(): void
    {
        $this->postJson('/enquiries', $this->payload(['email' => 'nope', 'name' => '']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'name']);
    }

    public function test_in_page_honeypot_submission_reports_success_but_stores_nothing(): void
    {
        $this->postJson('/enquiries', $this->payload(['website' => 'spam']))->assertCreated();

        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_package_enquiry_stores_package_and_price_snapshot(): void
    {
        Queue::fake();
        $package = Package::factory()->create(['name' => 'USA Today', 'price' => 499, 'currency' => 'USD']);

        $this->post('/enquiries', $this->payload(['package_id' => $package->id, 'context' => 'package']))->assertRedirect();

        $enquiry = Enquiry::sole();
        $this->assertSame($package->id, $enquiry->package_id);
        $this->assertSame('USA Today', $enquiry->package_name_snapshot);
        $this->assertSame('499.00', $enquiry->package_price_snapshot);

        // A later price change must not rewrite history.
        $package->update(['price' => 650, 'name' => 'USA Today Plus']);
        $enquiry->refresh();
        $this->assertSame('499.00', $enquiry->package_price_snapshot);
        $this->assertSame('USA Today', $enquiry->package_label);
    }

    public function test_package_is_required_for_package_page_enquiries(): void
    {
        $this->post('/enquiries', $this->payload(['context' => 'package']))->assertSessionHasErrors('package_id');
    }

    public function test_inactive_package_cannot_be_enquired(): void
    {
        $package = Package::factory()->inactive()->create();

        $this->post('/enquiries', $this->payload(['package_id' => $package->id]))->assertSessionHasErrors('package_id');
    }

    public function test_honeypot_submissions_are_discarded_silently(): void
    {
        Queue::fake();

        $this->post('/enquiries', $this->payload(['website' => 'http://spam.example']))
            ->assertRedirect(route('enquiries.thanks'));

        $this->assertDatabaseCount('enquiries', 0);
        Queue::assertNothingPushed();
    }

    public function test_enquiries_are_rate_limited(): void
    {
        Queue::fake();
        config(['vmnewswire.enquiries.per_minute' => 2]);

        $this->post('/enquiries', $this->payload())->assertRedirect();
        $this->post('/enquiries', $this->payload())->assertRedirect();
        $this->post('/enquiries', $this->payload())->assertStatus(429);

        $this->assertDatabaseCount('enquiries', 2);
    }

    public function test_emails_are_sent_to_admin_and_customer_and_logged(): void
    {
        Mail::fake();
        $package = Package::factory()->create(['name' => 'MSN']);

        $this->post('/enquiries', $this->payload(['package_id' => $package->id]));

        $enquiry = Enquiry::sole();
        Mail::assertSent(AdminEnquiryMail::class, fn ($mail) => $mail->hasTo('sales@vmnewswire.com')
            && $mail->envelope()->subject === 'New VM Newswire Enquiry — MSN');
        Mail::assertSent(CustomerAcknowledgementMail::class, fn ($mail) => $mail->hasTo('jane@example.com')
            && $mail->envelope()->subject === 'We received your VM Newswire enquiry');

        $this->assertSame(2, $enquiry->emailLogs()->where('delivery_status', EmailDeliveryStatus::Sent)->whereNotNull('sent_at')->count());
    }

    public function test_email_failure_is_logged_and_enquiry_is_kept(): void
    {
        Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP connection refused'));

        $this->post('/enquiries', $this->payload())->assertRedirect(route('enquiries.thanks'));

        $enquiry = Enquiry::sole();
        $logs = $enquiry->emailLogs;
        $this->assertCount(2, $logs);
        $this->assertTrue($logs->every(fn ($log) => $log->delivery_status === EmailDeliveryStatus::Failed));
        $this->assertStringContainsString('SMTP connection refused', $logs->first()->error_message);
    }

    public function test_admin_can_retry_failed_emails(): void
    {
        Mail::fake();
        $enquiry = Enquiry::factory()->create();
        $enquiry->emailLogs()->create(['email_type' => EmailType::CustomerAcknowledgement, 'recipient' => $enquiry->email, 'delivery_status' => EmailDeliveryStatus::Failed, 'error_message' => 'x']);

        $this->actingAs($this->admin())->post(route('admin.enquiries.retry', $enquiry))->assertRedirect();

        $this->assertSame(EmailDeliveryStatus::Sent, $enquiry->emailLogs()->first()->delivery_status);
        Mail::assertSent(CustomerAcknowledgementMail::class);
    }
}
