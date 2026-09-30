<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Models\MediaOutlet;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPackageTest extends TestCase
{
    use RefreshDatabase;

    private function validData(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'GlobeNewswire Basic',
            'slug' => '',
            'brand' => 'GlobeNewswire',
            'short_description' => 'GlobeNewswire distribution with network pickup.',
            'full_content' => "**Bold** intro.\n\n<script>alert(1)</script>",
            'distribution_summary' => 'GlobeNewswire plus network',
            'features_text' => "Major media distribution\n\nProfessional reporting\n",
            'price' => '299',
            'currency' => 'USD',
            'is_active' => '1',
            'is_highlighted' => '0',
            'display_order' => '3',
        ];
    }

    public function test_admin_can_create_a_package_with_generated_slug(): void
    {
        $this->actingAs($this->admin())->post(route('admin.packages.store'), $this->validData())
            ->assertRedirect(route('admin.packages.edit', 'globenewswire-basic'));

        $package = Package::sole();
        $this->assertSame('globenewswire-basic', $package->slug);
        $this->assertSame(['Major media distribution', 'Professional reporting'], $package->features);
        $this->assertSame('299.00', $package->price);

        // Rich text is rendered safely on the public page.
        $this->get('/packages/globenewswire-basic')
            ->assertOk()
            ->assertSee('<strong>Bold</strong>', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_package_validation(): void
    {
        Package::factory()->create(['slug' => 'taken']);

        $this->actingAs($this->admin())->post(route('admin.packages.store'), $this->validData(['name' => '', 'slug' => 'taken', 'price' => 'abc', 'currency' => 'XXX']))
            ->assertSessionHasErrors(['name', 'slug', 'price', 'currency']);
    }

    public function test_admin_can_update_package_and_price(): void
    {
        $package = Package::factory()->create(['slug' => 'old-slug', 'price' => 100]);

        $this->actingAs($this->admin())->put(route('admin.packages.update', $package), $this->validData(['name' => 'Renamed', 'slug' => 'renamed', 'price' => '450.50']))
            ->assertRedirect();

        $package->refresh();
        $this->assertSame('Renamed', $package->name);
        $this->assertSame('450.50', $package->price);
        $this->get('/packages/old-slug')->assertRedirect('/packages/renamed');
    }

    public function test_only_one_package_can_be_most_requested(): void
    {
        $first = Package::factory()->create(['is_highlighted' => true]);
        $second = Package::factory()->create();

        $this->actingAs($this->admin())->put(route('admin.packages.update', $second), $this->validData(['name' => $second->name, 'is_highlighted' => '1']));

        $this->assertFalse($first->fresh()->is_highlighted);
        $this->assertTrue($second->fresh()->is_highlighted);
    }

    public function test_admin_can_deactivate_and_reactivate_a_package(): void
    {
        $package = Package::factory()->create();
        $admin = $this->admin();

        $this->actingAs($admin)->patch(route('admin.packages.toggle', $package))->assertRedirect();
        $this->assertFalse($package->fresh()->is_active);
        $this->get('/packages/'.$package->slug)->assertNotFound();

        $this->actingAs($admin)->patch(route('admin.packages.toggle', $package));
        $this->assertTrue($package->fresh()->is_active);
    }

    public function test_archiving_keeps_historical_enquiries(): void
    {
        $package = Package::factory()->create(['name' => 'Legacy Package']);
        $enquiry = Enquiry::factory()->create(['package_id' => $package->id, 'package_name_snapshot' => 'Legacy Package']);

        $this->actingAs($this->admin())->delete(route('admin.packages.destroy', $package))->assertRedirect(route('admin.packages.index'));

        $this->assertSoftDeleted($package);
        $this->assertSame($package->id, $enquiry->fresh()->package_id);
        $this->actingAs($this->admin())->get(route('admin.enquiries.show', $enquiry))->assertOk()->assertSee('Legacy Package');

        $this->actingAs($this->admin())->patch(route('admin.packages.restore', $package->id));
        $this->assertNotSoftDeleted($package);
    }

    public function test_admin_can_reorder_packages(): void
    {
        $a = Package::factory()->create(['display_order' => 1]);
        $b = Package::factory()->create(['display_order' => 2]);

        $this->actingAs($this->admin())->patch(route('admin.packages.move', [$b, 'up']));

        $this->assertSame([$b->id, $a->id], Package::ordered()->pluck('id')->all());
    }

    public function test_admin_can_manage_featured_and_network_media(): void
    {
        $package = Package::factory()->create();
        $outlets = MediaOutlet::factory()->count(3)->create();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.packages.media.store', $package), ['media_ids' => $outlets->pluck('id')->all(), 'is_featured' => '1'])
            ->assertSessionHas('toast');
        $this->assertSame(3, $package->featuredMedia()->count());

        $this->actingAs($admin)->patch(route('admin.packages.media.update', [$package, $outlets[0]]), ['action' => 'unfeature']);
        $this->assertSame(2, $package->featuredMedia()->count());
        $this->assertSame(1, $package->networkMedia()->count());

        $this->actingAs($admin)->delete(route('admin.packages.media.destroy', [$package, $outlets[1]]));
        $this->assertSame(2, $package->mediaOutlets()->count());
    }

    public function test_featured_media_is_limited_to_seven(): void
    {
        $package = Package::factory()->create();
        $outlets = MediaOutlet::factory()->count(8)->create();

        $this->actingAs($this->admin())->post(route('admin.packages.media.store', $package), ['media_ids' => $outlets->pluck('id')->all(), 'is_featured' => '1'])
            ->assertSessionHas('toast_error');

        $this->assertSame(0, $package->mediaOutlets()->count());
    }

    public function test_duplicate_package_media_relationships_are_prevented(): void
    {
        $package = Package::factory()->create();
        $outlet = MediaOutlet::factory()->create();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.packages.media.store', $package), ['media_ids' => [$outlet->id]]);
        $this->actingAs($admin)->post(route('admin.packages.media.store', $package), ['media_ids' => [$outlet->id]])->assertSessionHas('toast_error');

        $this->assertSame(1, $package->mediaOutlets()->count());
    }
}
