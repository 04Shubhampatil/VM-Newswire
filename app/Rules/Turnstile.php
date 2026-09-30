<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Verifies a Cloudflare Turnstile token. Only applied when TURNSTILE_SECRET_KEY is set.
 */
class Turnstile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $response = Http::asForm()->timeout(5)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => config('vmnewswire.turnstile.secret_key'),
                'response' => $value,
                'remoteip' => request()->ip(),
            ]);

            if (! $response->json('success')) {
                $fail('The security check failed. Please try again.');
            }
        } catch (Throwable $e) {
            report($e);
            $fail('The security check could not be verified. Please try again.');
        }
    }
}
