<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Which "I'm not a robot" check the enquiry form uses:
 *  1. Google reCAPTCHA v2 when RECAPTCHA_SITE_KEY + RECAPTCHA_SECRET_KEY are set,
 *  2. otherwise Cloudflare Turnstile when its keys are set,
 *  3. otherwise the self-hosted picture captcha (App\Support\ImageCaptcha) — or the ALTCHA tick box
 *     when CAPTCHA_TYPE=altcha — so the form is never unprotected.
 */
class Captcha
{
    public const RECAPTCHA_FIELD = 'g-recaptcha-response';

    public static function driver(): ?string
    {
        if (config('vmnewswire.recaptcha.site_key') && config('vmnewswire.recaptcha.secret_key')) {
            return 'recaptcha';
        }
        if (config('vmnewswire.turnstile.secret_key')) {
            return 'turnstile';
        }

        if (! config('vmnewswire.captcha.enabled')) {
            return null;
        }

        return config('vmnewswire.captcha.type') === 'altcha' ? 'altcha' : 'image';
    }

    /** Asks Google whether the visitor passed the reCAPTCHA checkbox. Each token can be checked once. */
    public static function verifyRecaptcha(mixed $token, ?string $ip): bool
    {
        if (! is_string($token) || $token === '' || strlen($token) > 4000) {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(6)->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('vmnewswire.recaptcha.secret_key'),
                'response' => $token,
                'remoteip' => $ip,
            ]);

            return $response->ok() && $response->json('success') === true;
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }
}
