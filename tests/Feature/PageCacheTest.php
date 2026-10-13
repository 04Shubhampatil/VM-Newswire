<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\PressRelease;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_are_served_from_the_cache_and_refreshed_when_content_changes(): void
    {
        Package::factory()->create(['name' => 'Cached Package']);

        $this->get('/packages')->assertOk()->assertHeader('X-Page-Cache', 'miss')->assertSee('Cached Package');
        $this->get('/packages')->assertOk()->assertHeader('X-Page-Cache', 'hit')->assertSee('Cached Package');

        // Saving a package bumps the cache version, so the next request is rendered fresh.
        Package::factory()->create(['name' => 'Brand New Package']);
        $this->get('/packages')->assertOk()->assertHeader('X-Page-Cache', 'miss')->assertSee('Brand New Package');

        $post = PressRelease::factory()->create(['title' => 'Cached post']);
        $this->get('/blogs')->assertHeader('X-Page-Cache', 'miss');
        $this->get('/blogs')->assertHeader('X-Page-Cache', 'hit');
        $post->update(['title' => 'Renamed post']);
        $this->get('/blogs')->assertHeader('X-Page-Cache', 'miss')->assertSee('Renamed post');
    }

    public function test_query_strings_get_their_own_cache_entry(): void
    {
        // The sidebar lists recent posts for every filter, so the main grid is checked through its bylines.
        PressRelease::factory()->create(['title' => 'Finance piece', 'category' => 'PR Strategy', 'author' => 'Fiona Finance']);
        PressRelease::factory()->create(['title' => 'Media piece', 'category' => 'Media Relations', 'author' => 'Mia Media']);

        $this->get('/blogs?category=PR+Strategy')->assertHeader('X-Page-Cache', 'miss')->assertSee('By Fiona Finance')->assertDontSee('By Mia Media');
        $this->get('/blogs?category=Media+Relations')->assertHeader('X-Page-Cache', 'miss')->assertSee('By Mia Media')->assertDontSee('By Fiona Finance');
        $this->get('/blogs?category=PR+Strategy')->assertHeader('X-Page-Cache', 'hit')->assertSee('By Fiona Finance');
    }

    public function test_signed_in_admins_and_non_get_requests_bypass_the_cache(): void
    {
        $this->get('/')->assertHeader('X-Page-Cache', 'miss');
        $this->get('/login')->assertOk()->assertHeaderMissing('X-Page-Cache');
        $this->actingAs($this->admin())->get('/')->assertOk()->assertHeaderMissing('X-Page-Cache');
    }

    public function test_enquiries_can_be_submitted_from_a_cached_page_without_a_fresh_csrf_token(): void
    {
        $package = Package::factory()->create();

        $this->post('/enquiries', [
            'name' => 'Cache Tester',
            'email' => 'cache@example.com',
            'phone' => '+1 555 000 0000',
            'package_id' => $package->id,
            'message' => 'Submitted from a cached page.',
            'context' => 'package',
        ], ['Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'])->assertCreated();

        $this->assertDatabaseHas('enquiries', ['email' => 'cache@example.com']);
    }
}
