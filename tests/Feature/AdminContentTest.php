<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\MediaOutlet;
use App\Services\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_update_is_reflected_on_the_website(): void
    {
        $this->actingAs($this->admin())->put(route('admin.settings.update'), [
            'company_name' => 'VM Newswire',
            'company_email' => 'hello@vmnewswire.com',
            'phone' => '+44 20 0000 0000',
            'whatsapp' => '+44 7700 900000',
            'address' => '1 Example Street, London',
            'admin_notification_email' => 'leads@vmnewswire.com',
            'network_size_label' => '250+',
        ])->assertSessionHas('toast');

        $this->assertSame('leads@vmnewswire.com', app(SiteSettings::class)->adminNotificationEmail());

        $this->get('/contact')
            ->assertSee('hello@vmnewswire.com')
            ->assertSee('+44 7700 900000');
        $this->get('/')->assertSee('250+');
    }

    public function test_settings_are_validated(): void
    {
        $this->actingAs($this->admin())->put(route('admin.settings.update'), [
            'company_name' => '',
            'company_email' => 'nope',
            'network_size_label' => '200+',
            'social_linkedin' => 'javascript:alert(1)',
        ])->assertSessionHasErrors(['company_name', 'company_email', 'social_linkedin']);
    }

    public function test_home_page_copy_is_editable_from_website_content(): void
    {
        MediaOutlet::factory()->create(['is_highlighted' => true]);
        $this->get('/')->assertSee('The News Starts')->assertSee('Compare packages')->assertSee('Global Reach')->assertSee('can appear across 200+ leading media outlets');

        $this->actingAs($this->admin())->put(route('admin.content.update'), [
            'hero_eyebrow' => 'Press release distribution',
            'hero_headline' => 'Get your news in front of',
            'hero_headline_highlight' => 'the right readers.',
            'hero_text' => 'Custom hero text.',
            'hero_primary_label' => 'See Packages',
            'hero_secondary_label' => 'Talk to us',
            'hero_trust_items' => "{network} outlets\n<b>Fast</b> turnaround",
            'trust_strip_heading' => 'Seen on leading networks.',
            'packages_eyebrow' => 'Our packages',
            'packages_heading' => "Five packages.\nOne goal.",
            'packages_text' => 'Pick the reach you need.',
            'about_intro' => 'Intro.',
            'footer_text' => 'Footer.',
        ])->assertSessionHas('toast');

        $this->get('/')
            ->assertSee('Get your news in front of')
            ->assertSee('the right readers.')
            ->assertSee('Custom hero text.')
            ->assertSee('See Packages')
            ->assertSee('Talk to us')
            ->assertSee('Seen on leading networks.')
            ->assertSee('Our packages')
            ->assertSee('Five packages.<br class="hidden md:block"> One goal.', false)
            ->assertSee('Pick the reach you need.')
            ->assertDontSee('Put your story in front of');
    }

    public function test_content_update_renders_sanitised_markdown(): void
    {
        $this->actingAs($this->admin())->put(route('admin.content.update'), [
            'about_intro' => 'We distribute press releases.',
            'about_body' => "Our **story**.\n\n<img src=x onerror=alert(1)> [bad](javascript:alert(1))",
            'footer_text' => 'Custom footer text.',
            'privacy_content' => "## Section\n\nPrivacy words.",
            'privacy_updated_at' => '2026-09-01',
            'terms_content' => 'Terms words.',
        ])->assertSessionHas('toast');

        $this->get('/about')
            ->assertSee('<strong>story</strong>', false)
            ->assertDontSee('onerror', false)
            ->assertDontSee('javascript:alert', false)
            ->assertSee('Custom footer text.');

        $this->get('/privacy')->assertSee('Privacy words.')->assertSee('Last updated 1 September 2026');
    }

    public function test_about_photo_can_be_uploaded_replaced_and_removed(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $base = ['about_intro' => 'Intro.', 'footer_text' => 'Footer.'];

        $this->get('/about')->assertSee('Add a photo in Admin');

        $this->actingAs($admin)->put(route('admin.content.update'), $base + ['about_image' => UploadedFile::fake()->image('team.jpg', 1200, 900)])->assertSessionHas('toast');
        $first = app(SiteSettings::class)->get('about_image');
        $this->assertMatchesRegularExpression('#^media-network/about-[a-z0-9]{10}\.webp$#', $first);
        Storage::disk('public')->assertExists($first);
        $this->get('/about')->assertSee($first, false)->assertDontSee('Add a photo in Admin');

        $this->actingAs($admin)->put(route('admin.content.update'), $base + ['about_image' => UploadedFile::fake()->image('team2.jpg', 800, 600)]);
        Storage::disk('public')->assertMissing($first);

        $this->actingAs($admin)->put(route('admin.content.update'), $base + ['remove_about_image' => '1']);
        $this->assertEmpty(app(SiteSettings::class)->get('about_image'));
        $this->get('/about')->assertSee('Add a photo in Admin');
        $this->assertSame([], Storage::disk('public')->allFiles());

        $this->actingAs($admin)->put(route('admin.content.update'), $base + ['about_image' => UploadedFile::fake()->create('x.php', 10, 'text/plain')])->assertSessionHasErrors('about_image');
    }

    public function test_media_network_photo_defaults_and_can_be_replaced(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $base = ['about_intro' => 'Intro.', 'footer_text' => 'Footer.'];

        $this->get('/media-network')->assertOk()->assertSee('images/media-network-hero.webp', false);

        $this->actingAs($admin)->put(route('admin.content.update'), $base + ['media_network_image' => UploadedFile::fake()->image('newsroom.jpg', 1200, 900)])->assertSessionHas('toast');
        $first = app(SiteSettings::class)->get('media_network_image');
        $this->assertMatchesRegularExpression('#^media-network/media-network-hero-[a-z0-9]{10}\.webp$#', $first);
        Storage::disk('public')->assertExists($first);
        $this->get('/media-network')->assertSee($first, false)->assertDontSee('images/media-network-hero.webp', false);

        $this->actingAs($admin)->put(route('admin.content.update'), $base + ['media_network_image' => UploadedFile::fake()->image('newsroom2.jpg', 800, 600)]);
        Storage::disk('public')->assertMissing($first);

        $this->actingAs($admin)->put(route('admin.content.update'), $base + ['remove_media_network_image' => '1']);
        $this->assertEmpty(app(SiteSettings::class)->get('media_network_image'));
        $this->get('/media-network')->assertSee('images/media-network-hero.webp', false);
        $this->assertSame([], Storage::disk('public')->allFiles());

        $this->actingAs($admin)->put(route('admin.content.update'), $base + ['media_network_image' => UploadedFile::fake()->create('x.php', 10, 'text/plain')])->assertSessionHasErrors('media_network_image');
    }

    public function test_admin_can_manage_faqs(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.faqs.store'), [
            'question' => 'Is this a new question?', 'answer' => 'Yes it is.', 'category' => 'General', 'is_active' => '1',
        ])->assertRedirect();

        $faq = Faq::sole();
        $this->get('/faq')->assertSee('Is this a new question?');

        $this->actingAs($admin)->put(route('admin.faqs.update', $faq), [
            'question' => 'Is this a new question?', 'answer' => 'Yes it is.', 'category' => 'General', 'is_active' => '0',
        ]);
        $this->get('/faq')->assertDontSee('Is this a new question?');

        $this->actingAs($admin)->delete(route('admin.faqs.destroy', $faq));
        $this->assertModelMissing($faq);
    }

    public function test_admin_can_update_wire_release_items_and_settings(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $logoFile = UploadedFile::fake()->image('ap-logo.png', 100, 100);

        $this->actingAs($admin)->put(route('admin.content.update'), [
            'about_intro' => 'Intro.',
            'footer_text' => 'Footer.',
            'hero_wire_eyebrow' => 'Global Wire Syndication',
            'hero_wire_title' => '{company} Global Feed',
            'hero_wire_subtitle' => 'Live syndication across top news desks',
            'hero_wire_status' => 'Broadcasting',
            'hero_wire_items_present' => '1',
            'wire_items' => [
                [
                    'name' => 'Associated Press Custom',
                    'link' => 'https://apnews.com/feed',
                    'category' => 'Global Wire',
                    'existing_logo' => '',
                ],
                [
                    'name' => 'Bloomberg Terminal News',
                    'link' => 'https://bloomberg.com',
                    'category' => 'Financial Media',
                    'existing_logo' => '',
                ],
            ],
            'wire_logos' => [
                0 => $logoFile,
            ],
        ])->assertSessionHas('toast');

        $settings = app(SiteSettings::class);
        $this->assertSame('Global Wire Syndication', $settings->get('hero_wire_eyebrow'));
        $this->assertSame('Broadcasting', $settings->get('hero_wire_status'));

        $items = $settings->heroWireItems();
        $this->assertCount(2, $items);
        $this->assertSame('Associated Press Custom', $items[0]['name']);
        $this->assertSame('https://apnews.com/feed', $items[0]['link']);
        $this->assertSame('Global Wire', $items[0]['category']);
        $this->assertNotEmpty($items[0]['logo']);
        Storage::disk('public')->assertExists($items[0]['logo']);
    }

    public function test_admin_can_update_media_strip_logos(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $logoFile = UploadedFile::fake()->image('reuters.png', 120, 40);

        $this->actingAs($admin)->put(route('admin.content.update'), [
            'about_intro' => 'Intro.',
            'footer_text' => 'Footer.',
            'trust_strip_heading' => 'Featured across global news networks.',
            'media_strip_logos_present' => '1',
            'strip_logos' => [
                [
                    'name' => 'Reuters Wire',
                    'link' => 'https://reuters.com',
                    'existing_logo' => '',
                ],
                [
                    'name' => 'Wall Street Journal',
                    'link' => 'https://wsj.com',
                    'existing_logo' => '',
                ],
            ],
            'strip_logo_files' => [
                0 => $logoFile,
            ],
        ])->assertSessionHas('toast');

        $settings = app(SiteSettings::class);
        $logos = $settings->mediaStripLogos();
        $this->assertCount(2, $logos);
        $this->assertSame('Reuters Wire', $logos[0]['name']);
        $this->assertSame('https://reuters.com', $logos[0]['link']);
        Storage::disk('public')->assertExists($logos[0]['logo']);

        // Check home page renders the custom strip
        $this->get('/')
            ->assertSee('Featured across global news networks.')
            ->assertSee('https://reuters.com')
            ->assertSee('Wall Street Journal');
    }

    public function test_admin_can_update_network_orbit_hub_and_satellites(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put(route('admin.content.update'), [
            'about_intro' => 'Intro.',
            'footer_text' => 'Footer.',
            'network_kicker' => 'Syndication Reach',
            'network_heading' => 'Worldwide coverage *unlocked.*',
            'network_text' => 'Direct syndication to global financial terminals and digital publishers.',
            'network_hub_title' => '{company_name} HQ',
            'network_hub_subtitle' => 'Global Distribution Hub',
            'network_orbit_nodes_present' => '1',
            'orbit_nodes' => [
                ['category' => 'Newswire Tier 1', 'count' => '15 outlets'],
                ['category' => 'Financial Times & Markets', 'count' => '8 outlets'],
                ['category' => 'Crypto & Tech', 'count' => '25 outlets'],
            ],
        ])->assertSessionHas('toast');

        $settings = app(SiteSettings::class);
        $this->assertSame('Syndication Reach', $settings->get('network_kicker'));
        $this->assertSame('Global Distribution Hub', $settings->get('network_hub_subtitle'));

        $nodes = $settings->networkOrbitNodes();
        $this->assertCount(3, $nodes);
        $this->assertSame('Newswire Tier 1', $nodes[0]['category']);
        $this->assertSame('15 outlets', $nodes[0]['count']);
    }

    public function test_home_sections_are_editable_from_website_content(): void
    {
        Storage::fake('public');
        MediaOutlet::factory()->create(['is_highlighted' => true]);

        $this->actingAs($this->admin())->put(route('admin.content.update'), [
            'news_heading' => 'Where your story *lands.*',
            'news_button_label' => 'All outlets',
            'confidence_heading' => "Trusted by *teams* like yours",
            'confidence_button_label' => 'Read the reports',
            'confidence_cards_present' => '1',
            'confidence_cards' => [
                ['quote' => 'The release was live the same morning.', 'name' => 'Asha Verma', 'role' => 'Head of Communications'],
                ['quote' => '', 'name' => 'Ignored because the quote is empty', 'role' => ''],
            ],
            'confidence_card_logos' => [0 => UploadedFile::fake()->image('client.png', 200, 80)],
            'journalists_eyebrow' => 'Newsroom tools',
            'journalists_heading' => 'Find your next story',
            'journalists_text' => 'Search the {company} network by beat.',
            'journalists_link_label' => 'Open the directory',
        ])->assertSessionHas('toast');

        $settings = app(SiteSettings::class);
        $this->assertCount(1, $settings->confidenceCards());
        $this->assertSame('Asha Verma', $settings->confidenceCards()[0]['name']);
        $this->assertNotEmpty($settings->confidenceCards()[0]['logo']);
        Storage::disk('public')->assertExists($settings->confidenceCards()[0]['logo']);

        $this->get('/')
            ->assertSee('Where your story <em>lands.</em>', false)
            ->assertSee('All outlets')
            ->assertSee('Trusted by <em>teams</em> like yours', false)
            ->assertSee('Read the reports')
            ->assertSee('The release was live the same morning.')
            ->assertSee('Asha Verma')
            ->assertSee('Head of Communications')
            ->assertDontSee('Ignored because the quote is empty')
            ->assertSee('Newsroom tools')
            ->assertSee('Find your next story')
            ->assertSee('Search the VM Newswire network by beat.')
            ->assertSee('Open the directory');
    }

    public function test_media_network_page_copy_is_editable_from_website_content(): void
    {
        $this->get('/media-network')->assertSee('Featured publications')->assertSee('Where your press release <em>can appear.</em>', false);

        $this->actingAs($this->admin())->put(route('admin.content.update'), [
            'media_network_eyebrow' => 'Our reach',
            'media_network_heading' => 'Publications that *carry your story.*',
            'media_network_text' => '{company} reaches {network} outlets.',
            'directory_eyebrow' => 'Browse outlets',
            'directory_heading' => 'Every publication we work with',
            'directory_text' => 'See where each outlet appears.',
        ])->assertSessionHas('toast');

        $this->get('/media-network')
            ->assertSee('Our reach')
            ->assertSee('Publications that <em>carry your story.</em>', false)
            ->assertSee('VM Newswire reaches 200+ outlets.')
            ->assertSee('Browse outlets')
            ->assertSee('Every publication we work with')
            ->assertSee('See where each outlet appears.')
            ->assertDontSee('Filter by category to see which outlets');
    }

    public function test_admin_picks_which_outlets_appear_as_homepage_news_cards(): void
    {
        $shown = MediaOutlet::factory()->create(['name' => 'Chosen Outlet', 'is_highlighted' => false]);
        $hidden = MediaOutlet::factory()->create(['name' => 'Dropped Outlet', 'is_highlighted' => true]);

        $this->actingAs($this->admin())->put(route('admin.content.update'), [
            'homepage_outlets_present' => '1',
            'homepage_outlets' => [$shown->id],
        ])->assertSessionHas('toast');

        $this->assertTrue($shown->fresh()->is_highlighted);
        $this->assertFalse($hidden->fresh()->is_highlighted);
        $this->get('/')->assertSee('Chosen Outlet')->assertDontSee('Dropped Outlet');
    }

    public function test_homepage_shows_only_the_eight_newest_selected_outlets(): void
    {
        foreach (range(1, 9) as $i) {
            MediaOutlet::factory()->create(['name' => "Outlet Number {$i}", 'is_highlighted' => true, 'created_at' => now()->subDays(10 - $i)]);
        }

        $this->get('/')->assertSee('Outlet Number 9')->assertSee('Outlet Number 2')->assertDontSee('Outlet Number 1');
    }
}
