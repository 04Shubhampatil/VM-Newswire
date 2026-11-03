<?php

namespace Tests\Feature;

use App\Support\Captcha;
use App\Support\ImageCaptcha;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnquiryImageCaptchaTest extends TestCase
{
    use RefreshDatabase;

    /** One browser session across requests, like a real visitor (the test client keeps no cookies itself). */
    private string $sessionId;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'vmnewswire.captcha.enabled' => true,
            'vmnewswire.captcha.type' => 'image',
            'vmnewswire.recaptcha.site_key' => null,
            'vmnewswire.recaptcha.secret_key' => null,
            'vmnewswire.turnstile.secret_key' => null,
            'vmnewswire.enquiries.per_minute' => 100,
        ]);
        Queue::fake();

        $this->sessionId = Str::random(40);
        $this->withCookie(config('session.cookie'), $this->sessionId)->withCredentials(); // JSON test requests only send cookies with credentials
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
            'captcha_form' => 'enq',
        ];
    }

    /** Loads the image (as the browser does), then plants a known answer for that form. */
    private function showCaptcha(string $word, string $form = 'enq'): void
    {
        $this->get(route('enquiries.captcha', $form))->assertOk();
        app('session.store')->setId($this->sessionId);
        app(ImageCaptcha::class)->remember($form, $word);
    }

    public function test_the_form_shows_the_image_reload_link_and_answer_box(): void
    {
        $this->assertSame('image', Captcha::driver());

        $this->get('/contact')->assertOk()
            ->assertSee('Security check')
            ->assertSee('data-captcha-src="'.route('enquiries.captcha', 'enq').'"', false)
            ->assertSee('Reload image')
            ->assertSee('placeholder="Type the characters above"', false)
            ->assertSee('name="captcha_form" value="enq"', false)
            ->assertDontSee('<altcha-widget', false);

        // The popup form on the home page has its own image.
        $this->get('/')->assertSee(route('enquiries.captcha', 'menq'), false);
    }

    public function test_the_image_is_a_fresh_uncached_png(): void
    {
        $response = $this->get(route('enquiries.captcha', 'enq'))->assertOk()->assertHeader('Content-Type', 'image/png');

        $this->assertStringStartsWith("\x89PNG", $response->getContent());
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        $this->get('/enquiries/captcha/BAD-1')->assertNotFound();
    }

    public function test_the_right_characters_are_accepted_case_insensitively(): void
    {
        $this->showCaptcha('k7hm3');

        $this->postJson('/enquiries', $this->payload(['captcha' => ' K7HM3 ']))->assertCreated();
        $this->assertDatabaseCount('enquiries', 1);
    }

    public function test_missing_or_wrong_characters_are_rejected(): void
    {
        $this->showCaptcha('k7hm3');
        $this->postJson('/enquiries', $this->payload())->assertStatus(422)->assertJsonValidationErrors('captcha');

        $this->postJson('/enquiries', $this->payload(['captcha' => 'wrong']))->assertStatus(422)->assertJsonValidationErrors('captcha');
        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_each_image_can_be_answered_only_once(): void
    {
        $this->showCaptcha('k7hm3');
        $this->postJson('/enquiries', $this->payload(['captcha' => 'wrong']))->assertStatus(422);

        // After a wrong try even the right answer fails: a new image is needed.
        $this->postJson('/enquiries', $this->payload(['captcha' => 'k7hm3']))->assertStatus(422)->assertJsonValidationErrors('captcha');

        $this->showCaptcha('p2xwq');
        $this->postJson('/enquiries', $this->payload(['captcha' => 'p2xwq']))->assertCreated();
        $this->postJson('/enquiries', $this->payload(['captcha' => 'p2xwq', 'email' => 'b@example.com']))->assertStatus(422);
        $this->assertDatabaseCount('enquiries', 1);
    }

    public function test_fixing_another_field_does_not_use_up_the_answer(): void
    {
        $this->showCaptcha('k7hm3');

        $this->postJson('/enquiries', $this->payload(['captcha' => 'k7hm3', 'email' => 'not-an-email']))
            ->assertStatus(422)->assertJsonValidationErrors('email')->assertJsonMissingValidationErrors('captcha');
        $this->postJson('/enquiries', $this->payload(['captcha' => 'k7hm3']))->assertCreated();
    }

    public function test_the_popup_and_page_forms_have_separate_answers(): void
    {
        $this->showCaptcha('aaaa2', 'enq');
        $this->showCaptcha('bbbb3', 'menq');

        $this->postJson('/enquiries', $this->payload(['captcha_form' => 'menq', 'captcha' => 'bbbb3']))->assertCreated();
        $this->postJson('/enquiries', $this->payload(['captcha_form' => 'enq', 'captcha' => 'aaaa2', 'email' => 'c@example.com']))->assertCreated();
        $this->assertDatabaseCount('enquiries', 2);
    }

    public function test_an_expired_image_is_rejected(): void
    {
        $this->showCaptcha('k7hm3');
        $this->travel(11)->minutes();

        $this->postJson('/enquiries', $this->payload(['captcha' => 'k7hm3']))->assertStatus(422)->assertJsonValidationErrors('captcha');
    }
}
