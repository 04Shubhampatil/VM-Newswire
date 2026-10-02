<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public static function adminPages(): array
    {
        return [['/admin'], ['/admin/dashboard'], ['/admin/packages'], ['/admin/media'], ['/admin/media/import'], ['/admin/sample-reports'], ['/admin/enquiries'], ['/admin/enquiries/export'], ['/admin/content'], ['/admin/settings']];
    }

    #[DataProvider('adminPages')]
    public function test_guests_are_redirected_to_login(string $url): void
    {
        $this->get($url)->assertRedirect('/login');
    }

    public function test_non_admin_users_are_forbidden(): void
    {
        $user = User::factory()->create(['role' => 'editor']);

        $this->actingAs($user)->get('/admin/dashboard')->assertForbidden();
    }

    public function test_admin_can_log_in_and_out(): void
    {
        $admin = $this->admin(['email' => 'owner@example.com', 'password' => 'correct-horse-42-battery']);

        $this->post('/login', ['login' => 'owner@example.com', 'password' => 'correct-horse-42-battery'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);

        $this->post('/admin/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_admin_can_log_in_with_username(): void
    {
        $admin = $this->admin(['name' => 'admin', 'email' => 'admin@vmnewswire.test', 'password' => 'admin123']);

        $this->post('/login', ['login' => 'Admin', 'password' => 'admin123'])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->admin(['email' => 'owner@example.com']);

        $this->post('/login', ['login' => 'owner@example.com', 'password' => 'wrong'])->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_non_admin_cannot_log_in_to_admin(): void
    {
        User::factory()->create(['email' => 'user@example.com', 'role' => 'editor', 'password' => 'secret-password-1']);

        $this->post('/login', ['login' => 'user@example.com', 'password' => 'secret-password-1'])->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_login_is_rate_limited(): void
    {
        $this->admin(['email' => 'owner@example.com']);

        foreach (range(1, 5) as $i) {
            $this->post('/login', ['login' => 'owner@example.com', 'password' => 'wrong']);
        }

        $this->post('/login', ['login' => 'owner@example.com', 'password' => 'wrong'])->assertStatus(429);
    }

    public function test_admin_with_one_time_password_must_change_it(): void
    {
        $admin = $this->admin(['password' => 'one-time-password-1', 'must_change_password' => true]);

        $this->actingAs($admin)->get('/admin/dashboard')->assertRedirect(route('admin.account.password'));

        $this->put(route('admin.account.password.update'), [
            'current_password' => 'one-time-password-1',
            'password' => 'a-brand-new-password-2026',
            'password_confirmation' => 'a-brand-new-password-2026',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertFalse($admin->fresh()->must_change_password);
        $this->get('/admin/dashboard')->assertOk();
    }

    public function test_admin_pages_render_for_admin(): void
    {
        $admin = $this->admin();
        $package = $this->packageWithMedia();

        foreach (['/admin/dashboard', '/admin/packages', '/admin/packages/create', "/admin/packages/{$package->slug}/edit", '/admin/packages/archived',
            '/admin/media', '/admin/media/create', '/admin/media/import', '/admin/sample-reports', '/admin/sample-reports/create',
            '/admin/enquiries', '/admin/content', '/admin/faqs/create', '/admin/settings', '/admin/account/password'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_create_admin_command_generates_one_time_password(): void
    {
        $this->artisan('vmn:create-admin', ['email' => 'new-admin@example.com', '--generate' => true])->assertSuccessful();

        $user = User::where('email', 'new-admin@example.com')->sole();
        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->must_change_password);
    }
}
