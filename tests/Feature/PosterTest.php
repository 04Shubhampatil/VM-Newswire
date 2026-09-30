<?php

namespace Tests\Feature;

use App\Models\MediaOutlet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PosterTest extends TestCase
{
    use RefreshDatabase;

    private function outletData(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'AP News',
            'category' => 'News',
            'short_description' => 'National and international news coverage.',
            'is_active' => '1',
            'is_highlighted' => '1',
            'display_order' => '2',
        ];
    }

    public function test_admin_can_upload_a_poster_which_is_optimised_to_webp(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post(route('admin.media.store'), $this->outletData([
            'poster' => UploadedFile::fake()->image('My Poster (final).PNG', 1600, 1000),
        ]))->assertRedirect(route('admin.media.index'));

        $outlet = MediaOutlet::sole();
        $this->assertSame('ap-news', $outlet->slug);
        $this->assertMatchesRegularExpression('#^media-network/ap-news-[a-z0-9]{10}\.webp$#', $outlet->poster_path);
        $this->assertSame(800, $outlet->poster_width);
        $this->assertSame(500, $outlet->poster_height);
        Storage::disk('public')->assertExists($outlet->poster_path);

        $stored = Storage::disk('public')->get($outlet->poster_path);
        $this->assertSame('RIFF', substr($stored, 0, 4));
        $this->assertSame('WEBP', substr($stored, 8, 4));
    }

    public function test_replacing_a_poster_deletes_the_old_file_after_the_new_one_is_stored(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.media.store'), $this->outletData(['poster' => UploadedFile::fake()->image('a.jpg', 600, 400)]));
        $outlet = MediaOutlet::sole();
        $old = $outlet->poster_path;

        $this->actingAs($admin)->put(route('admin.media.update', $outlet), $this->outletData(['poster' => UploadedFile::fake()->image('b.jpg', 640, 480)]))->assertRedirect();

        $outlet->refresh();
        $this->assertNotSame($old, $outlet->poster_path);
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($outlet->poster_path);
    }

    public function test_poster_can_be_removed(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.media.store'), $this->outletData(['poster' => UploadedFile::fake()->image('a.jpg', 600, 400)]));
        $outlet = MediaOutlet::sole();
        $path = $outlet->poster_path;

        $this->actingAs($admin)->put(route('admin.media.update', $outlet), $this->outletData(['remove_poster' => '1']));

        $this->assertNull($outlet->fresh()->poster_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_unsafe_or_undersized_posters_are_rejected(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        foreach ([
            UploadedFile::fake()->createWithContent('shell.php', '<?php echo 1;'),
            UploadedFile::fake()->createWithContent('poster.jpg', '<?php echo 1;'),
            UploadedFile::fake()->create('logo.svg', 5, 'image/svg+xml'),
            UploadedFile::fake()->image('tiny.png', 120, 80),
        ] as $file) {
            $this->actingAs($admin)->post(route('admin.media.store'), $this->outletData(['poster' => $file]))->assertSessionHasErrors('poster');
        }

        $this->assertDatabaseCount('media_outlets', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_deleting_an_outlet_removes_its_poster_file(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.media.store'), $this->outletData(['poster' => UploadedFile::fake()->image('a.jpg', 600, 400)]));
        $outlet = MediaOutlet::sole();

        $this->actingAs($admin)->delete(route('admin.media.destroy', $outlet))->assertRedirect();

        $this->assertModelMissing($outlet);
        Storage::disk('public')->assertMissing($outlet->poster_path);
    }

    public function test_admin_can_enable_and_disable_an_outlet(): void
    {
        $outlet = MediaOutlet::factory()->create();

        $this->actingAs($this->admin())->patch(route('admin.media.toggle', $outlet))->assertRedirect();
        $this->assertFalse($outlet->fresh()->is_active);
    }

    public function test_hero_renders_database_posters_with_a_fallback_when_missing(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.media.store'), $this->outletData(['poster' => UploadedFile::fake()->image('a.jpg', 600, 400)]));
        MediaOutlet::factory()->create(['name' => 'Fallback Outlet', 'is_highlighted' => true, 'category' => 'Markets']);
        MediaOutlet::factory()->create(['name' => 'Hidden Outlet', 'is_highlighted' => false]);
        auth()->logout();

        $withPoster = MediaOutlet::where('name', 'AP News')->sole();

        $this->get('/')
            ->assertOk()
            ->assertSee($withPoster->poster_src, false)
            ->assertSee('Fallback Outlet')
            ->assertSee('poster-fallback', false)
            ->assertSee("open-outlet', 'ap-news'", false)
            ->assertSee("open-outlet', 'fallback-outlet'", false)
            ->assertDontSee("open-outlet', 'hidden-outlet'", false);
    }

    public function test_old_admin_media_network_url_redirects(): void
    {
        $this->actingAs($this->admin())->get('/admin/media-network')->assertRedirect('/admin/media');
    }
}
