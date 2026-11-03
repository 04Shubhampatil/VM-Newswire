<?php

namespace Tests\Feature;

use App\Enums\EnquiryStatus;
use App\Jobs\SendEnquiryEmails;
use App\Models\Enquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EnquirySpamManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['vmnewswire.enquiries.per_minute' => 100, 'vmnewswire.enquiries.per_day' => 100]);
    }

    private function payload(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+1 555 010 0199',
            'message' => 'We are launching a product next month and need US coverage.',
            'context' => 'general',
            'source_page' => '/contact',
        ];
    }

    private const GUEST_POST = "Hi, I'm Christy. We have been in guest posting for a long time and do link building with strong DA, DR sites and dofollow backlinks. Pricing: https://docs.google.com/spreadsheets/d/1Zh/ To unsubscribe reply with \"No\".";

    // ---- Submission ---------------------------------------------------------------------------

    public function test_seo_spam_is_saved_as_spam_without_any_email(): void
    {
        Queue::fake();

        $this->postJson('/enquiries', $this->payload(['name' => 'Christy Mark', 'email' => 'christy@zoomwings.co.uk', 'message' => self::GUEST_POST]))
            ->assertCreated()->assertJson(['ok' => true]); // same response as a real enquiry

        $enquiry = Enquiry::sole();
        $this->assertSame(EnquiryStatus::Spam, $enquiry->status);
        $this->assertSame('auto', $enquiry->spam_marked_by);
        $this->assertNotNull($enquiry->spam_marked_at);
        $this->assertGreaterThanOrEqual(config('spam.spam_threshold'), $enquiry->spam_score);
        $this->assertContains('seo_guest_post', array_column($enquiry->spam_reasons, 'rule'));
        $this->assertSame(0, $enquiry->emailLogs()->count());
        Queue::assertNotPushed(SendEnquiryEmails::class);
    }

    public function test_genuine_enquiry_is_unchanged_new_with_both_emails(): void
    {
        Queue::fake();

        $this->postJson('/enquiries', $this->payload())->assertCreated();

        $enquiry = Enquiry::sole();
        $this->assertSame(EnquiryStatus::New, $enquiry->status);
        $this->assertSame(0, $enquiry->spam_score);
        $this->assertNull($enquiry->spam_reasons);
        $this->assertFalse($enquiry->needsSpamReview());
        $this->assertSame(2, $enquiry->emailLogs()->count());
        $this->assertNotNull($enquiry->ip_address);
        Queue::assertPushed(SendEnquiryEmails::class);
    }

    public function test_medium_risk_enquiry_stays_new_for_review_and_still_gets_emails(): void
    {
        Queue::fake();

        $this->postJson('/enquiries', $this->payload([
            'company' => 'PR House',
            'message' => 'We are a PR agency. Do the placements carry backlinks, and are they dofollow? Pricing for 20 releases please.',
        ]))->assertCreated();

        $enquiry = Enquiry::sole();
        $this->assertSame(EnquiryStatus::New, $enquiry->status);
        $this->assertTrue($enquiry->needsSpamReview());
        $this->assertSame(2, $enquiry->emailLogs()->count());
        Queue::assertPushed(SendEnquiryEmails::class);
    }

    public function test_the_same_message_repeated_by_many_senders_becomes_spam(): void
    {
        Queue::fake();
        $message = 'Quick question about how your publications are selected for each package and timing.';

        foreach (['a@one.com', 'b@two.com', 'c@three.com'] as $email) {
            $this->postJson('/enquiries', $this->payload(['email' => $email, 'message' => $message]))->assertCreated();
        }
        $last = Enquiry::latest('id')->first();
        $this->assertContains('bulk_message', array_column($last->spam_reasons ?? [], 'rule'));

        // Same address sending the same text again within a day is flagged as a duplicate.
        $this->postJson('/enquiries', $this->payload(['email' => 'a@one.com', 'message' => $message]))->assertCreated();
        $this->assertContains('duplicate', array_column(Enquiry::latest('id')->first()->spam_reasons, 'rule'));
    }

    public function test_bait_names_are_still_rejected_by_validation(): void
    {
        $this->postJson('/enquiries', $this->payload(['name' => 'Вам перевод 187721 руб. забрать тут']))
            ->assertStatus(422)->assertJsonValidationErrors('name');
        $this->assertDatabaseCount('enquiries', 0);
    }

    // ---- Admin ----------------------------------------------------------------------------------

    public function test_spam_has_its_own_tab_and_is_left_out_of_all_and_the_dashboard(): void
    {
        $admin = $this->admin();
        Enquiry::factory()->create(['name' => 'Real Customer']);
        Enquiry::factory()->create(['name' => 'Spammy Sender', 'status' => EnquiryStatus::Spam, 'spam_score' => 30]);

        $this->actingAs($admin)->get(route('admin.enquiries.index'))
            ->assertOk()->assertSee('Real Customer')->assertDontSee('Spammy Sender')->assertSee('Spam');

        $this->actingAs($admin)->get(route('admin.enquiries.index', ['status' => 'spam']))
            ->assertOk()->assertSee('Spammy Sender')->assertDontSee('Real Customer')->assertSee('Restore to New');

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertDontSee('Spammy Sender');
    }

    public function test_spam_can_be_searched_and_filtered(): void
    {
        $admin = $this->admin();
        Enquiry::factory()->create(['name' => 'Seo Seller', 'status' => EnquiryStatus::Spam, 'message' => 'cheap guest post offer', 'ip_address' => '203.0.113.9']);
        Enquiry::factory()->create(['name' => 'Bot Two', 'status' => EnquiryStatus::Spam, 'message' => 'something else', 'created_at' => now()->subYear()]);

        $this->actingAs($admin)->get(route('admin.enquiries.index', ['status' => 'spam', 'search' => 'guest post']))
            ->assertSee('Seo Seller')->assertDontSee('Bot Two');
        $this->actingAs($admin)->get(route('admin.enquiries.index', ['status' => 'spam', 'search' => '203.0.113.9']))
            ->assertSee('Seo Seller')->assertDontSee('Bot Two');
        $this->actingAs($admin)->get(route('admin.enquiries.index', ['status' => 'spam', 'from' => now()->subMonth()->toDateString()]))
            ->assertSee('Seo Seller')->assertDontSee('Bot Two');
    }

    public function test_possible_spam_filter_lists_review_band_enquiries(): void
    {
        $admin = $this->admin();
        Enquiry::factory()->create(['name' => 'Borderline Lead', 'spam_score' => 7]);
        Enquiry::factory()->create(['name' => 'Clean Lead', 'spam_score' => 0]);

        $this->actingAs($admin)->get(route('admin.enquiries.index'))->assertSee('Possible spam');
        $this->actingAs($admin)->get(route('admin.enquiries.index', ['flag' => 'review']))
            ->assertSee('Borderline Lead')->assertDontSee('Clean Lead');
    }

    public function test_admin_can_mark_one_enquiry_as_spam_and_restore_it(): void
    {
        $admin = $this->admin();
        $enquiry = Enquiry::factory()->create();

        $this->actingAs($admin)->patch(route('admin.enquiries.status', $enquiry), ['status' => 'spam'])->assertRedirect();
        $enquiry->refresh();
        $this->assertSame(EnquiryStatus::Spam, $enquiry->status);
        $this->assertSame('admin', $enquiry->spam_marked_by);

        $this->actingAs($admin)->get(route('admin.enquiries.show', $enquiry))->assertOk()
            ->assertSee('This enquiry is marked as spam.')->assertSee('Spam check')->assertSee('Restore to New');

        $this->actingAs($admin)->patch(route('admin.enquiries.status', $enquiry), ['status' => 'new'])->assertRedirect();
        $enquiry->refresh();
        $this->assertSame(EnquiryStatus::New, $enquiry->status);
        $this->assertSame(Enquiry::NOT_SPAM, $enquiry->spam_marked_by); // the admin's decision is remembered
    }

    public function test_a_restored_enquiry_is_no_longer_flagged_and_the_cleanup_leaves_it_alone(): void
    {
        $admin = $this->admin();
        $enquiry = Enquiry::factory()->create([
            'name' => 'Christy Mark', 'company' => 'Zoom Wings', 'message' => self::GUEST_POST,
            'status' => EnquiryStatus::Spam, 'spam_score' => 24, 'spam_marked_by' => 'auto',
        ]);

        $this->actingAs($admin)->post(route('admin.enquiries.bulk'), ['bulk_action' => 'restore', 'ids' => [$enquiry->id]]);
        $enquiry->refresh();
        $this->assertFalse($enquiry->needsSpamReview());
        $this->actingAs($admin)->get(route('admin.enquiries.index', ['flag' => 'review']))->assertDontSee('Christy Mark');

        $this->artisan('enquiries:spam-cleanup', ['--apply' => true])->assertSuccessful();
        $this->assertSame(EnquiryStatus::New, $enquiry->fresh()->status);
    }

    public function test_admin_can_bulk_mark_as_spam_and_bulk_restore(): void
    {
        $admin = $this->admin();
        [$a, $b] = Enquiry::factory()->count(2)->create();
        $untouched = Enquiry::factory()->create();

        $this->actingAs($admin)->post(route('admin.enquiries.bulk'), ['bulk_action' => 'spam', 'ids' => [$a->id, $b->id]])
            ->assertRedirect()->assertSessionHas('toast', '2 enquiries marked as spam.');
        $this->assertSame(2, Enquiry::where('status', EnquiryStatus::Spam)->count());
        $this->assertSame(EnquiryStatus::New, $untouched->fresh()->status);

        $this->actingAs($admin)->post(route('admin.enquiries.bulk'), ['bulk_action' => 'restore', 'ids' => [$a->id]])
            ->assertSessionHas('toast', '1 enquiry restored to New.');
        $this->assertSame(EnquiryStatus::New, $a->fresh()->status);
        $this->assertSame(EnquiryStatus::Spam, $b->fresh()->status);

        $this->actingAs($admin)->post(route('admin.enquiries.bulk'), ['bulk_action' => 'spam', 'ids' => []])->assertSessionHasErrors('ids');
        $this->assertDatabaseCount('enquiries', 3); // nothing is ever deleted
    }

    public function test_bulk_actions_require_an_admin(): void
    {
        $enquiry = Enquiry::factory()->create();

        $this->post(route('admin.enquiries.bulk'), ['bulk_action' => 'spam', 'ids' => [$enquiry->id]])->assertRedirect(route('login'));
        $this->assertSame(EnquiryStatus::New, $enquiry->fresh()->status);
    }

    public function test_csv_export_leaves_spam_out_unless_the_spam_tab_is_exported(): void
    {
        $admin = $this->admin();
        Enquiry::factory()->create(['name' => 'Real Customer']);
        Enquiry::factory()->create(['name' => 'Spammy Sender', 'status' => EnquiryStatus::Spam]);

        $all = $this->actingAs($admin)->get(route('admin.enquiries.export'))->streamedContent();
        $this->assertStringContainsString('Real Customer', $all);
        $this->assertStringNotContainsString('Spammy Sender', $all);

        $spam = $this->actingAs($admin)->get(route('admin.enquiries.export', ['status' => 'spam']))->streamedContent();
        $this->assertStringContainsString('Spammy Sender', $spam);
    }

    // ---- Clean-up of existing data --------------------------------------------------------------

    public function test_cleanup_is_a_dry_run_by_default(): void
    {
        Enquiry::factory()->create(['name' => 'Вам перевод 187721 руб. забрать тут https://tinyurl.com/x', 'company' => 'google', 'message' => 'IywOV7f 2KzA TxH0LC4 XdGE6CG PtwM nQFSViY']);

        $this->artisan('enquiries:spam-cleanup')->expectsOutputToContain('DRY RUN')->assertSuccessful();

        $this->assertSame(EnquiryStatus::New, Enquiry::sole()->status);
        $this->assertNull(Enquiry::sole()->spam_score);
    }

    public function test_cleanup_apply_moves_only_new_spam_and_never_deletes(): void
    {
        $bot = Enquiry::factory()->count(3)->sequence(
            ['name' => 'Вам перевод 187721 руб. забрать тут https://tinyurl.com/a', 'message' => 'IywOV7f 2KzA TxH0LC4 XdGE6CG PtwM nQFSViY'],
            ['name' => 'Вам перевод 160926 руб. получить тут https://tinyurl.com/b', 'message' => 'EN6t367 jU7e EVexYtp hbXFNcy leIA Lt4SJHj'],
            ['name' => 'Christy Mark', 'message' => self::GUEST_POST],
        )->create(['company' => 'google']);
        $real = Enquiry::factory()->create(['name' => 'Priya Sharma', 'message' => 'We are launching our robot in March. Which package do you recommend?']);
        // Already handled by the team: left alone even though it looks like spam.
        $contacted = Enquiry::factory()->create(['name' => 'Вам перевод 111 руб. забрать тут', 'status' => EnquiryStatus::Contacted, 'message' => 'IywOV7f 2KzA TxH0LC4 XdGE6CG PtwM nQFSViY']);

        $this->artisan('enquiries:spam-cleanup', ['--apply' => true])->expectsOutputToContain('APPLYING')->assertSuccessful();

        foreach ($bot as $enquiry) {
            $enquiry->refresh();
            $this->assertSame(EnquiryStatus::Spam, $enquiry->status);
            $this->assertSame('cleanup', $enquiry->spam_marked_by);
            $this->assertNotEmpty($enquiry->spam_reasons);
        }
        $this->assertSame(EnquiryStatus::New, $real->fresh()->status);
        $this->assertSame(0, $real->fresh()->spam_score);
        $this->assertSame(EnquiryStatus::Contacted, $contacted->fresh()->status);
        $this->assertDatabaseCount('enquiries', 5);

        // Running it again changes nothing more.
        $this->artisan('enquiries:spam-cleanup', ['--apply' => true])->assertSuccessful();
        $this->assertSame(3, Enquiry::where('status', EnquiryStatus::Spam)->count());
    }
}
