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
        $this->get('/')->assertSee('Put your story in front of')->assertSee('Six packages.')->assertSee('200+ Media Outlets')->assertSee('Trusted distribution across');

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
            ->assertSee('200+ outlets')
            ->assertSee('&lt;b&gt;Fast&lt;/b&gt; turnaround', false)
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
}
