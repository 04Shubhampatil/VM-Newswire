<?php

namespace Tests\Feature;

use App\Models\MediaOutlet;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_media_outlet_with_logo(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post(route('admin.media.store'), [
            'name' => 'Example Finance Daily',
            'website_url' => 'https://example.com',
            'category' => 'Finance',
            'is_active' => '1',
            'is_highlighted' => '0',
            'logo' => UploadedFile::fake()->image('logo.png', 200, 60),
        ])->assertRedirect(route('admin.media.index'));

        $outlet = MediaOutlet::sole();
        $this->assertSame('Finance', $outlet->category);
        $this->assertNotNull($outlet->logo_path);
        $this->assertStringNotContainsString('logo.png', $outlet->logo_path);
        Storage::disk('public')->assertExists($outlet->logo_path);
    }

    public function test_media_validation_rejects_bad_category_and_svg_logo(): void
    {
        $this->actingAs($this->admin())->post(route('admin.media.store'), [
            'name' => 'Bad',
            'category' => 'Sports',
            'logo' => UploadedFile::fake()->create('logo.svg', 5, 'image/svg+xml'),
        ])->assertSessionHasErrors(['category', 'logo']);
    }

    public function test_outlet_used_by_a_package_cannot_be_deleted(): void
    {
        $package = $this->packageWithMedia(featured: 1, network: 0);
        $outlet = $package->mediaOutlets()->first();

        $this->actingAs($this->admin())->delete(route('admin.media.destroy', $outlet))->assertSessionHas('toast_error');
        $this->assertModelExists($outlet);

        $unused = MediaOutlet::factory()->create();
        $this->actingAs($this->admin())->delete(route('admin.media.destroy', $unused))->assertRedirect();
        $this->assertModelMissing($unused);
    }

    public function test_csv_import_validates_every_row_and_reports_results(): void
    {
        MediaOutlet::factory()->create(['name' => 'Existing Outlet']);
        $package = Package::factory()->create();

        $csv = implode("\n", [
            "\xEF\xBB\xBFname,website_url,logo_url,category,is_active",
            'Good One,https://good.example,,Business,1',
            'Good Two,,,finance,yes',
            ',https://noname.example,,News,1',
            'Bad Category,,,Sports,1',
            'Bad Url,not-a-url,,News,1',
            'Existing Outlet,,,News,1',
            'Good One,,,Business,1',
        ]);

        $this->actingAs($this->admin())->post(route('admin.media.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('outlets.csv', $csv),
            'package_id' => $package->id,
        ])->assertRedirect(route('admin.media.import'));

        $result = session('import_result');
        $this->assertSame(2, $result['imported']);
        $this->assertSame(0, $result['updated']);
        $this->assertSame(2, $result['skipped']);
        $this->assertSame(3, $result['failed']);
        $this->assertSame([4, 5, 6, 7, 8], array_column($result['errors'], 'row'));

        $this->assertSame('Finance', MediaOutlet::where('name', 'Good Two')->value('category'));
        // Imported (and existing) outlets are attached to the package network.
        $this->assertSame(3, $package->networkMedia()->count());
    }

    public function test_csv_import_can_update_existing_outlets(): void
    {
        MediaOutlet::factory()->create(['name' => 'Existing Outlet', 'category' => 'News']);

        $this->actingAs($this->admin())->post(route('admin.media.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('outlets.csv', "name,category,is_active\nExisting Outlet,Markets,0"),
            'update_existing' => '1',
        ]);

        $outlet = MediaOutlet::where('name', 'Existing Outlet')->sole();
        $this->assertSame('Markets', $outlet->category);
        $this->assertFalse($outlet->is_active);
    }

    public function test_csv_import_requires_name_and_category_headers(): void
    {
        $this->actingAs($this->admin())->post(route('admin.media.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('outlets.csv', "title,url\nA,https://a.example"),
        ])->assertSessionHasErrors('file');

        $this->assertDatabaseCount('media_outlets', 0);
    }
}
