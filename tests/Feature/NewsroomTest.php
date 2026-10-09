<?php

namespace Tests\Feature;

use App\Models\PressRelease;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsroomTest extends TestCase
{
    use RefreshDatabase;

    public function test_newsroom_lists_published_releases_newest_first_with_packages_section(): void
    {
        $old = PressRelease::factory()->create(['title' => 'Older announcement', 'published_at' => now()->subDays(5)]);
        $new = PressRelease::factory()->create(['title' => 'Newest announcement', 'published_at' => now()->subDay()]);
        PressRelease::factory()->unpublished()->create(['title' => 'Draft announcement']);
        PressRelease::factory()->scheduled()->create(['title' => 'Scheduled announcement']);

        $this->get('/newsroom')
            ->assertOk()
            ->assertSeeInOrder(['Newest announcement', 'Older announcement'])
            ->assertDontSee('Draft announcement')
            ->assertDontSee('Scheduled announcement')
            ->assertSee(route('newsroom.show', $new->slug), false)
            ->assertSee('Load More')
            ->assertSee('id="packages"', false)
            ->assertSee('<link rel="canonical"', false);
    }

    public function test_newsroom_filters_by_category_and_search(): void
    {
        // The "Latest Releases" sidebar always lists the newest items, so the main list is checked via its bylines.
        PressRelease::factory()->create(['title' => 'Fintech funding round', 'category' => 'Finance', 'author' => 'Fiona Finance']);
        PressRelease::factory()->create(['title' => 'Hospital opens new wing', 'category' => 'Health', 'author' => 'Harry Health']);

        $this->get('/newsroom?category=Finance')->assertOk()->assertSee('By Fiona Finance')->assertDontSee('By Harry Health');
        $this->get('/newsroom?q=hospital')->assertOk()->assertSee('By Harry Health')->assertDontSee('By Fiona Finance');
        $this->get('/newsroom?category=Nope')->assertSessionHasErrors('category');
    }

    public function test_release_page_shows_body_similar_releases_and_hides_unpublished(): void
    {
        $release = PressRelease::factory()->create([
            'title' => 'Acme launches widget',
            'category' => 'Technology',
            'body' => "## Launch details\n\nThe widget ships **today**.",
        ]);
        $similar = PressRelease::factory()->create(['title' => 'Other tech story', 'category' => 'Technology']);
        $draft = PressRelease::factory()->unpublished()->create();

        $this->get('/newsroom/'.$release->slug)
            ->assertOk()
            ->assertSee('Acme launches widget')
            ->assertSee('<h2>Launch details</h2>', false)
            ->assertSee('<strong>today</strong>', false)
            ->assertSee('Related press releases')
            ->assertSee('min read')
            ->assertSee('Other tech story')
            ->assertSee('"@type":"NewsArticle"', false)
            ->assertSee('id="packages"', false);

        $this->get('/newsroom/'.$draft->slug)->assertNotFound();
        $this->get('/newsroom/does-not-exist')->assertNotFound();
    }

    public function test_sitemap_includes_published_releases_only(): void
    {
        $live = PressRelease::factory()->create();
        $draft = PressRelease::factory()->unpublished()->create();

        $this->get('/sitemap.xml')
            ->assertSee(route('newsroom.index'), false)
            ->assertSee(route('newsroom.show', $live->slug), false)
            ->assertDontSee(route('newsroom.show', $draft->slug), false);
    }

    public function test_admin_can_create_update_and_delete_press_releases(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.newsroom.store'), [
            'title' => 'Board appoints new CEO',
            'category' => 'Business',
            'author' => 'Jane Doe',
            'excerpt' => 'Short summary.',
            'body' => 'Full announcement.',
            'published_at' => now()->subHour()->format('Y-m-d\TH:i'),
            'is_published' => 1,
        ])->assertRedirect(route('admin.newsroom.index'))->assertSessionHas('toast');

        $release = PressRelease::firstWhere('title', 'Board appoints new CEO');
        $this->assertSame('board-appoints-new-ceo', $release->slug);
        $this->get('/newsroom/board-appoints-new-ceo')->assertOk();

        $this->actingAs($admin)->get(route('admin.newsroom.index'))->assertOk()->assertSee('Board appoints new CEO');
        $this->actingAs($admin)->get(route('admin.newsroom.edit', $release))->assertOk();

        $this->actingAs($admin)->put(route('admin.newsroom.update', $release), [
            'title' => 'Board appoints new CEO',
            'slug' => 'new-ceo',
            'category' => 'Business',
            'body' => 'Updated announcement.',
            'is_published' => 0,
        ])->assertRedirect(route('admin.newsroom.index'));

        $release->refresh();
        $this->assertSame('new-ceo', $release->slug);
        $this->assertFalse($release->is_published);
        $this->get('/newsroom/new-ceo')->assertNotFound();

        $this->actingAs($admin)->delete(route('admin.newsroom.destroy', $release))->assertRedirect(route('admin.newsroom.index'));
        $this->assertDatabaseMissing('press_releases', ['id' => $release->id]);
    }

    public function test_guests_cannot_reach_the_newsroom_admin(): void
    {
        $this->get(route('admin.newsroom.index'))->assertRedirect(route('login'));
    }
}
