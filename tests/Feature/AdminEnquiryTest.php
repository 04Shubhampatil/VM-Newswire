<?php

namespace Tests\Feature;

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEnquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_enquiries_can_be_filtered_and_searched(): void
    {
        $package = Package::factory()->create();
        Enquiry::factory()->create(['name' => 'Alice Package', 'package_id' => $package->id, 'package_name_snapshot' => $package->name]);
        Enquiry::factory()->create(['name' => 'Bob General', 'company' => 'Findable Corp', 'status' => EnquiryStatus::Closed]);
        Enquiry::factory()->create(['name' => 'Carol Old', 'created_at' => now()->subYear()]);
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.enquiries.index', ['package' => $package->id]))
            ->assertOk()->assertSee('Alice Package')->assertDontSee('Bob General');

        $this->actingAs($admin)->get(route('admin.enquiries.index', ['status' => 'closed']))
            ->assertSee('Bob General')->assertDontSee('Alice Package');

        $this->actingAs($admin)->get(route('admin.enquiries.index', ['search' => 'Findable']))
            ->assertSee('Bob General')->assertDontSee('Carol Old');

        $this->actingAs($admin)->get(route('admin.enquiries.index', ['from' => now()->subMonth()->toDateString()]))
            ->assertSee('Alice Package')->assertDontSee('Carol Old');

        $this->actingAs($admin)->get(route('admin.enquiries.index', ['package' => 'none']))
            ->assertSee('Bob General')->assertDontSee('Alice Package');
    }

    public function test_admin_can_update_enquiry_status(): void
    {
        $enquiry = Enquiry::factory()->create();

        $this->actingAs($this->admin())->patch(route('admin.enquiries.status', $enquiry), ['status' => 'contacted'])
            ->assertSessionHas('toast', 'Enquiry status updated to Contacted.');

        $this->assertSame(EnquiryStatus::Contacted, $enquiry->fresh()->status);
    }

    public function test_invalid_status_is_rejected(): void
    {
        $enquiry = Enquiry::factory()->create();

        $this->actingAs($this->admin())->patch(route('admin.enquiries.status', $enquiry), ['status' => 'converted'])
            ->assertSessionHasErrors('status');
    }

    public function test_status_cannot_be_changed_with_get(): void
    {
        $enquiry = Enquiry::factory()->create();

        $this->actingAs($this->admin())->get('/admin/enquiries/'.$enquiry->id.'/status')->assertStatus(405);
    }

    public function test_enquiry_detail_shows_price_snapshot_and_email_status(): void
    {
        $enquiry = Enquiry::factory()->create(['package_name_snapshot' => 'MSN', 'package_price_snapshot' => 249, 'package_currency_snapshot' => 'USD']);
        $enquiry->emailLogs()->create(['email_type' => 'admin_notification', 'recipient' => 'sales@vmnewswire.com', 'delivery_status' => 'failed', 'error_message' => 'Mailbox unavailable']);

        $this->actingAs($this->admin())->get(route('admin.enquiries.show', $enquiry))
            ->assertOk()
            ->assertSee('$249.00')
            ->assertSee('Mailbox unavailable')
            ->assertSee('Retry failed');
    }

    public function test_csv_export_streams_filtered_enquiries_and_neutralises_formulas(): void
    {
        Enquiry::factory()->create(['name' => '=HYPERLINK("http://evil")', 'email' => 'a@example.com', 'status' => EnquiryStatus::New]);
        Enquiry::factory()->create(['name' => 'Closed Person', 'status' => EnquiryStatus::Closed]);

        $response = $this->actingAs($this->admin())->get(route('admin.enquiries.export', ['status' => 'new']));

        $response->assertOk()->assertDownload();
        $csv = $response->streamedContent();

        $this->assertStringContainsString('ID,Name,Email,Phone,Company,Country,Package', $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringContainsString('a@example.com', $csv);
        $this->assertStringNotContainsString('Closed Person', $csv);
    }
}
