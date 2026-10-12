<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\PressRelease;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_lists_published_posts_newest_first_with_packages_section(): void
    {
        $old = PressRelease::factory()->create(['title' => 'Older article', 'published_at' => now()->subDays(5)]);
        $new = PressRelease::factory()->create(['title' => 'Newest article', 'published_at' => now()->subDay()]);
        PressRelease::factory()->unpublished()->create(['title' => 'Draft article']);
        PressRelease::factory()->scheduled()->create(['title' => 'Scheduled article']);

        $this->get('/blogs')
            ->assertOk()
            ->assertSee('Insights on PR, media and communication')
            ->assertSeeInOrder(['Newest article', 'Older article'])
            ->assertDontSee('Draft article')
            ->assertDontSee('Scheduled article')
            ->assertSee(route('blogs.show', $new->slug), false)
            ->assertSee('Read article')
            ->assertSee('id="packages"', false)
            ->assertSee('<link rel="canonical"', false);
    }

    public function test_blog_filters_by_category_and_search_and_paginates(): void
    {
        PressRelease::factory()->create(['title' => 'Pitching reporters well', 'category' => 'Media Relations', 'author' => 'Mia Media']);
        PressRelease::factory()->create(['title' => 'Headline writing basics', 'category' => 'Press Release Writing', 'author' => 'Will Writer']);
        PressRelease::factory()->count(7)->create(['category' => 'PR Strategy']);

        $this->get('/blogs?category=Media+Relations')->assertOk()->assertSee('By Mia Media')->assertDontSee('By Will Writer');
        $this->get('/blogs?q=headline')->assertOk()->assertSee('By Will Writer')->assertDontSee('By Mia Media');
        $this->get('/blogs?category=Nope')->assertSessionHasErrors('category');
        $this->get('/blogs')->assertOk()->assertSee('aria-label="Pagination"', false)->assertSee('/blogs?page=2', false);
        $this->get('/blogs?page=2')->assertOk();
    }

    public function test_post_page_follows_the_editorial_layout_and_hides_unpublished(): void
    {
        $post = PressRelease::factory()->create([
            'title' => 'Acme writes a better release',
            'category' => 'Press Release Writing',
            'excerpt' => 'A short standfirst.',
            'body' => "## Lead with the news\n\nThe first line **matters**.\n\n> Keep it short.",
            'meta_title' => 'Custom SEO title',
            'meta_description' => 'Custom SEO description.',
        ]);
        $related = PressRelease::factory()->create(['title' => 'Related writing tip', 'category' => 'Press Release Writing']);
        PressRelease::factory()->count(5)->create(['category' => 'PR Strategy']);
        $draft = PressRelease::factory()->unpublished()->create();
        Faq::create(['category' => 'General', 'question' => 'Do I pay online?', 'answer' => 'No, enquiries are free.']);

        $this->get('/blogs/'.$post->slug)
            ->assertOk()
            ->assertSee('<title>Custom SEO title | VM Newswire</title>', false)
            ->assertSee('content="Custom SEO description."', false)
            ->assertSee('Acme writes a better release')
            ->assertSee('A short standfirst.')
            ->assertSee('<h2>Lead with the news</h2>', false)
            ->assertSee('<strong>matters</strong>', false)
            ->assertSee('<blockquote>', false)
            ->assertSee('Article details')
            ->assertSee('min read')
            ->assertSee('Related blog posts')
            ->assertSee('Related writing tip')
            ->assertSee('More from our blog')
            ->assertSee('Need help distributing your announcement?')
            ->assertSee('Frequently asked questions')
            ->assertSee('Do I pay online?')
            ->assertSee('Back to Blogs')
            ->assertSee('"@type":"BlogPosting"', false)
            ->assertSee('id="packages"', false);

        $this->get('/blogs/'.$draft->slug)->assertNotFound();
        $this->get('/blogs/does-not-exist')->assertNotFound();
    }

    public function test_old_newsroom_urls_redirect_to_blogs(): void
    {
        $post = PressRelease::factory()->create();

        $this->get('/newsroom')->assertStatus(301)->assertRedirect('/blogs');
        $this->get('/newsroom/'.$post->slug)->assertStatus(301)->assertRedirect('/blogs/'.$post->slug);
    }

    public function test_navigation_and_sitemap_point_at_blogs(): void
    {
        $live = PressRelease::factory()->create();
        $draft = PressRelease::factory()->unpublished()->create();

        $this->get('/')->assertSee('Blogs')->assertSee(route('blogs.index'), false);
        $this->get('/sitemap.xml')
            ->assertSee(route('blogs.index'), false)
            ->assertSee(route('blogs.show', $live->slug), false)
            ->assertDontSee(route('blogs.show', $draft->slug), false);
    }

    public function test_admin_can_create_update_and_delete_blog_posts(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.blogs.store'), [
            'title' => 'Board appoints new CEO',
            'category' => 'Communications',
            'author' => 'Jane Doe',
            'excerpt' => 'Short summary.',
            'body' => 'Full article.',
            'meta_title' => 'CEO appointment',
            'published_at' => now()->subHour()->format('Y-m-d\TH:i'),
            'is_published' => 1,
        ])->assertRedirect(route('admin.blogs.index'))->assertSessionHas('toast');

        $post = PressRelease::firstWhere('title', 'Board appoints new CEO');
        $this->assertSame('board-appoints-new-ceo', $post->slug);
        $this->assertSame('CEO appointment', $post->meta_title);
        $this->get('/blogs/board-appoints-new-ceo')->assertOk();

        $this->actingAs($admin)->get(route('admin.blogs.index'))->assertOk()->assertSee('Board appoints new CEO');
        $this->actingAs($admin)->get(route('admin.blogs.edit', $post))->assertOk()->assertSee('SEO title');

        $this->actingAs($admin)->put(route('admin.blogs.update', $post), [
            'title' => 'Board appoints new CEO',
            'slug' => 'new-ceo',
            'category' => 'Communications',
            'body' => 'Updated article.',
            'is_published' => 0,
        ])->assertRedirect(route('admin.blogs.index'));

        $post->refresh();
        $this->assertSame('new-ceo', $post->slug);
        $this->assertFalse($post->is_published);
        $this->get('/blogs/new-ceo')->assertNotFound();

        $this->actingAs($admin)->delete(route('admin.blogs.destroy', $post))->assertRedirect(route('admin.blogs.index'));
        $this->assertDatabaseMissing('press_releases', ['id' => $post->id]);
    }

    public function test_guests_cannot_reach_the_blog_admin(): void
    {
        $this->get(route('admin.blogs.index'))->assertRedirect(route('login'));
    }
}
