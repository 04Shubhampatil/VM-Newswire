<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\SampleReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SampleReportTest extends TestCase
{
    use RefreshDatabase;

    private function pdf(string $name = 'report.pdf'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF");
    }

    public function test_admin_can_upload_a_pdf_with_a_safe_generated_filename(): void
    {
        Storage::fake('local');
        $package = Package::factory()->create();

        $this->actingAs($this->admin())->post(route('admin.sample-reports.store'), [
            'package_id' => $package->id,
            'file' => $this->pdf('../../evil name.pdf'),
        ])->assertRedirect(route('admin.sample-reports.index'));

        $report = SampleReport::sole();
        $this->assertStringStartsWith('sample-reports/', $report->file_path);
        $this->assertStringNotContainsString('evil', $report->file_path);
        $this->assertStringNotContainsString('..', $report->file_name);
        Storage::disk('local')->assertExists($report->file_path);
    }

    public function test_non_pdf_uploads_are_rejected(): void
    {
        Storage::fake('local');
        $package = Package::factory()->create();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.sample-reports.store'), [
            'package_id' => $package->id,
            'file' => UploadedFile::fake()->createWithContent('shell.php', '<?php echo 1;'),
        ])->assertSessionHasErrors('file');

        // Right extension, wrong content.
        $this->actingAs($admin)->post(route('admin.sample-reports.store'), [
            'package_id' => $package->id,
            'file' => UploadedFile::fake()->createWithContent('fake.pdf', 'this is not a pdf'),
        ])->assertSessionHasErrors('file');

        $this->assertDatabaseCount('sample_reports', 0);
    }

    public function test_replacing_a_report_removes_the_old_file(): void
    {
        Storage::fake('local');
        $package = Package::factory()->create();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.sample-reports.store'), ['package_id' => $package->id, 'file' => $this->pdf()]);
        $old = SampleReport::sole();

        $this->travel(1)->minutes();
        $this->actingAs($admin)->post(route('admin.sample-reports.store'), ['package_id' => $package->id, 'file' => $this->pdf('v2.pdf')]);

        $this->assertModelMissing($old);
        Storage::disk('local')->assertMissing($old->file_path);
        $this->assertSame('v2.pdf', $package->currentReport()->first()->file_name);
    }

    public function test_visitors_can_download_the_current_report_of_an_active_package(): void
    {
        Storage::fake('local');
        $package = Package::factory()->create(['name' => 'MSN', 'slug' => 'msn']);
        $this->actingAs($this->admin())->post(route('admin.sample-reports.store'), ['package_id' => $package->id, 'file' => $this->pdf()]);
        auth()->logout();

        $this->get('/reports/msn')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertDownload('vm-newswire-msn-sample-report.pdf');

        $package->update(['is_active' => false]);
        $this->get('/reports/msn')->assertNotFound();
    }

    public function test_report_can_be_viewed_inline_for_the_preview(): void
    {
        Storage::fake('local');
        $package = Package::factory()->create(['name' => 'MSN', 'slug' => 'msn']);
        $this->actingAs($this->admin())->post(route('admin.sample-reports.store'), ['package_id' => $package->id, 'file' => $this->pdf()]);
        auth()->logout();

        $response = $this->get('/reports/msn/view')->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('inline', $response->headers->get('Content-Disposition'));

        $this->get('/packages')->assertSee('open-report', false)->assertSee(route('reports.view', 'msn'), false);
    }

    public function test_missing_report_returns_404(): void
    {
        $package = Package::factory()->create();

        $this->get('/reports/'.$package->slug)->assertNotFound();
    }

    public function test_admin_can_remove_a_report(): void
    {
        Storage::fake('local');
        $package = Package::factory()->create();
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.sample-reports.store'), ['package_id' => $package->id, 'file' => $this->pdf()]);
        $report = SampleReport::sole();

        $this->actingAs($admin)->delete(route('admin.sample-reports.destroy', $report))->assertRedirect();

        $this->assertModelMissing($report);
        Storage::disk('local')->assertMissing($report->file_path);
    }
}
