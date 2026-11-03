<?php

namespace Tests\Feature;

use App\Support\Altcha;
use App\Support\Captcha;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EnquiryRecaptchaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'vmnewswire.captcha.enabled' => true,
            'vmnewswire.recaptcha.site_key' => 'site-key-123',
            'vmnewswire.recaptcha.secret_key' => 'secret-key-456',
        ]);
        Queue::fake();
    }

    private function payload(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+1 555 010 0199',
            'message' => 'We are launching a product next month.',
            'context' => 'general',
            'source_page' => '/contact',
        ];
    }

    public function test_recaptcha_replaces_the_self_hosted_check_when_keys_are_set(): void
    {
        $this->assertSame('recaptcha', Captcha::driver());
        $this->assertFalse(Altcha::enabled());

        $this->get('/contact')->assertOk()
            ->assertSee('data-sitekey="site-key-123"', false)
            ->assertSee('https://www.google.com/recaptcha/api.js', false)
            ->assertDontSee('<altcha-widget', false)
            ->assertDontSee('secret-key-456');
    }

    public function test_enquiries_without_the_checkbox_are_rejected(): void
    {
        Http::fake();

        $this->postJson('/enquiries', $this->payload())->assertStatus(422)->assertJsonValidationErrors('g-recaptcha-response');
        $this->assertDatabaseCount('enquiries', 0);
        Http::assertNothingSent();
    }

    public function test_a_passed_checkbox_is_verified_with_google_and_accepted(): void
    {
        Http::fake(['www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true])]);

        $this->postJson('/enquiries', $this->payload(['g-recaptcha-response' => 'valid-token']))->assertCreated();

        $this->assertDatabaseCount('enquiries', 1);
        Http::assertSent(fn (Request $r) => $r->url() === 'https://www.google.com/recaptcha/api/siteverify'
            && $r['secret'] === 'secret-key-456' && $r['response'] === 'valid-token');
    }

    public function test_a_failed_or_reused_token_is_rejected(): void
    {
        Http::fake(['www.google.com/recaptcha/api/siteverify' => Http::response(['success' => false, 'error-codes' => ['timeout-or-duplicate']])]);

        $this->postJson('/enquiries', $this->payload(['g-recaptcha-response' => 'used-token']))
            ->assertStatus(422)->assertJsonValidationErrors('g-recaptcha-response');
        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_google_is_only_asked_once_every_other_field_is_valid(): void
    {
        Http::fake(['www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true])]);

        $this->postJson('/enquiries', $this->payload(['g-recaptcha-response' => 'token', 'email' => 'not-an-email']))
            ->assertStatus(422)->assertJsonValidationErrors('email')->assertJsonMissingValidationErrors('g-recaptcha-response');
        Http::assertNothingSent();
    }

    public function test_an_unreachable_google_fails_closed(): void
    {
        Http::fake(['www.google.com/*' => fn () => throw new \Illuminate\Http\Client\ConnectionException('timeout')]);

        $this->postJson('/enquiries', $this->payload(['g-recaptcha-response' => 'token']))
            ->assertStatus(422)->assertJsonValidationErrors('g-recaptcha-response');
        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_without_keys_the_self_hosted_picture_captcha_stays_on(): void
    {
        config(['vmnewswire.recaptcha.site_key' => null, 'vmnewswire.recaptcha.secret_key' => null]);

        $this->assertSame('image', Captcha::driver());
        $this->get('/contact')->assertSee('enq-captcha-img', false)->assertDontSee('recaptcha/api.js', false);
    }
}
