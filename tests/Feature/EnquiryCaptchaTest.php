<?php

namespace Tests\Feature;

use App\Support\Altcha;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EnquiryCaptchaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['vmnewswire.captcha.enabled' => true, 'vmnewswire.captcha.type' => 'altcha', 'vmnewswire.captcha.max_number' => 2000, 'vmnewswire.turnstile.secret_key' => null]);
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

    /** Solves a challenge the way the browser widget does and returns its form value. */
    private function solve(?array $challenge = null): string
    {
        $challenge ??= $this->getJson(route('enquiries.challenge'))->assertOk()->json();
        for ($n = 0; $n <= $challenge['maxnumber']; $n++) {
            if (hash('sha256', $challenge['salt'].$n) === $challenge['challenge']) {
                return base64_encode(json_encode([
                    'algorithm' => 'SHA-256', 'challenge' => $challenge['challenge'], 'number' => $n,
                    'salt' => $challenge['salt'], 'signature' => $challenge['signature'],
                ]));
            }
        }
        $this->fail('Challenge not solvable');
    }

    public function test_form_shows_the_human_check_and_the_challenge_is_not_cached(): void
    {
        $this->get('/contact')->assertOk()->assertSee('<altcha-widget', false)->assertSee('altcha@1.5.1', false);

        $response = $this->getJson(route('enquiries.challenge'))->assertOk()
            ->assertJsonStructure(['algorithm', 'challenge', 'maxnumber', 'salt', 'signature']);
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    public function test_enquiries_without_a_solved_check_are_rejected(): void
    {
        $this->postJson('/enquiries', $this->payload())->assertStatus(422)->assertJsonValidationErrors('altcha');
        $this->postJson('/enquiries', $this->payload(['altcha' => base64_encode('{"algorithm":"SHA-256"}')]))->assertStatus(422)->assertJsonValidationErrors('altcha');
        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_a_solved_check_is_accepted_once(): void
    {
        $solution = $this->solve();

        $this->postJson('/enquiries', $this->payload(['altcha' => $solution]))->assertCreated();
        $this->postJson('/enquiries', $this->payload(['altcha' => $solution, 'email' => 'b@example.com']))
            ->assertStatus(422)->assertJsonValidationErrors('altcha');

        $this->assertDatabaseCount('enquiries', 1);
    }

    public function test_a_validation_error_elsewhere_does_not_use_up_the_solution(): void
    {
        $solution = $this->solve();

        $this->postJson('/enquiries', $this->payload(['altcha' => $solution, 'email' => 'not-an-email']))
            ->assertStatus(422)->assertJsonValidationErrors('email')->assertJsonMissingValidationErrors('altcha');
        $this->postJson('/enquiries', $this->payload(['altcha' => $solution]))->assertCreated();
    }

    public function test_forged_and_expired_solutions_are_rejected(): void
    {
        // Self-made challenge with a guessed signature.
        $salt = 'abc?expires='.now()->addHour()->timestamp;
        $forged = base64_encode(json_encode(['algorithm' => 'SHA-256', 'challenge' => hash('sha256', $salt.'5'), 'number' => 5, 'salt' => $salt, 'signature' => str_repeat('a', 64)]));
        $this->assertNull(Altcha::verify($forged));

        $challenge = $this->getJson(route('enquiries.challenge'))->json();
        $solution = $this->solve($challenge);
        $this->travel(21)->minutes();
        $this->assertNull(Altcha::verify($solution));
        $this->postJson('/enquiries', $this->payload(['altcha' => $solution]))->assertStatus(422)->assertJsonValidationErrors('altcha');
    }

    public function test_turnstile_keys_replace_the_self_hosted_check(): void
    {
        config(['vmnewswire.turnstile.secret_key' => 'secret', 'vmnewswire.turnstile.site_key' => 'site']);

        $this->assertFalse(Altcha::enabled());
        $this->getJson(route('enquiries.challenge'))->assertNotFound();
        $this->get('/contact')->assertDontSee('<altcha-widget', false)->assertSee('cf-turnstile', false);
    }
}
