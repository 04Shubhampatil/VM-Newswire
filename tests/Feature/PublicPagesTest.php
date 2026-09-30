<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public static function staticPages(): array
    {
        return [
            'home' => ['/'],
            'packages' => ['/packages'],
            'media network' => ['/media-network'],
            'about' => ['/about'],
            'contact' => ['/contact'],
            'faq' => ['/faq'],
            'privacy' => ['/privacy'],
            'terms' => ['/terms'],
            'sample reports' => ['/sample-reports'],
        ];
    }

    #[DataProvider('staticPages')]
    public function test_public_page_loads(string $url): void
    {
        $this->packageWithMedia(['name' => 'AccessWire Test']);

        $this->get($url)->assertOk()->assertSee('<link rel="canonical"', false);
    }

    public function test_pages_render_with_an_empty_catalogue(): void
    {
        $this->get('/')->assertOk()->assertSee('Packages are being updated');
        $this->get('/packages')->assertOk();
    }

    public function test_home_page_lists_active_packages_from_the_database(): void
    {
        $this->packageWithMedia(['name' => 'Visible Package']);
        Package::factory()->inactive()->create(['name' => 'Hidden Package']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Visible Package')
            ->assertDontSee('Hidden Package');
    }

    public function test_packages_page_shows_comparison_with_buy_now_popover(): void
    {
        $package = $this->packageWithMedia(['name' => 'GlobeNewswire Basic', 'brand' => 'GlobeNewswire', 'price' => 299]);

        $this->get('/packages')
            ->assertOk()
            ->assertSee('Compare packages')
            ->assertSee('GlobeNewswire Basic')
            ->assertSee('$299')
            ->assertSee('Buy Now')
            ->assertSee("\$dispatch('open-enquiry', {$package->id})", false)
            ->assertSee(route('packages.show', $package->slug), false)
            ->assertSee('Coming soon')
            ->assertSee('id="enquiry-modal-title"', false)
            ->assertDontSee('View Details')
            ->assertDontSee('>Report<', false);
    }

    public function test_package_detail_page_loads_by_slug_with_featured_media_and_seo(): void
    {
        $package = $this->packageWithMedia(['name' => 'AccessWire + BI + AP News', 'slug' => 'accesswire-bi-ap-news']);
        $featured = $package->featuredMedia()->first();

        $this->get('/packages/accesswire-bi-ap-news')
            ->assertOk()
            ->assertSee('AccessWire + BI + AP News')
            ->assertSee($featured->name)
            ->assertSee('<title>AccessWire + BI + AP News Press Release Distribution | VM Newswire</title>', false)
            ->assertSee('"@type":"Service"', false)
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSee('View full distribution network');
    }

    public function test_inactive_package_is_hidden(): void
    {
        $package = Package::factory()->inactive()->create();

        $this->get('/packages/'.$package->slug)->assertNotFound();
        $this->get('/packages')->assertDontSee($package->name);
        $this->getJson('/packages/'.$package->slug.'/network')->assertNotFound();
    }

    public function test_archived_package_is_hidden(): void
    {
        $package = Package::factory()->create();
        $package->delete();

        $this->get('/packages/'.$package->slug)->assertNotFound();
    }

    public function test_unknown_package_returns_friendly_404(): void
    {
        $this->get('/packages/does-not-exist')->assertNotFound()->assertSee("couldn't find that page");
    }

    public function test_old_slug_redirects_permanently_after_rename(): void
    {
        $package = Package::factory()->create(['slug' => 'old-name']);
        $package->update(['slug' => 'new-name']);

        $this->get('/packages/old-name')->assertStatus(301)->assertRedirect('/packages/new-name');
    }

    public function test_network_endpoint_returns_paginated_non_featured_outlets_with_search(): void
    {
        $package = $this->packageWithMedia(featured: 2, network: 3);
        $network = $package->networkMedia()->first();

        $this->getJson("/packages/{$package->slug}/network")
            ->assertOk()
            ->assertJsonPath('total', 3)
            ->assertJsonStructure(['data' => [['name', 'category']], 'current_page', 'last_page']);

        $this->getJson("/packages/{$package->slug}/network?search=".urlencode($network->name))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.name', $network->name);
    }

    public function test_package_faqs_are_shown_on_package_page(): void
    {
        $package = Package::factory()->create();
        Faq::create(['package_id' => $package->id, 'category' => 'Packages', 'question' => 'Package specific question?', 'answer' => 'Yes.']);

        $this->get('/packages/'.$package->slug)->assertSee('Package specific question?');
    }

    public function test_contact_page_preselects_package_from_query_string(): void
    {
        $package = Package::factory()->create(['name' => 'MSN', 'slug' => 'msn']);

        $this->get('/contact?package=msn')
            ->assertOk()
            ->assertSee('<option value="'.$package->id.'" selected', false);
    }

    public function test_sitemap_lists_active_packages_only(): void
    {
        $active = Package::factory()->create();
        $inactive = Package::factory()->inactive()->create();

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('packages.show', $active->slug), false)
            ->assertDontSee(route('packages.show', $inactive->slug), false);
    }

    public function test_robots_txt_is_served(): void
    {
        $this->get('/robots.txt')->assertOk()->assertSee('User-agent: *');
    }

    public function test_security_headers_are_sent(): void
    {
        $this->get('/')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }
}
