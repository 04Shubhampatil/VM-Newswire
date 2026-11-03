<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Self-hosted ALTCHA captcha (https://altcha.org): the browser widget must find the number whose
 * SHA-256 with a random salt equals the challenge. That takes a person's browser well under a second,
 * but a bot that only posts the form cannot produce it. No third-party service or account involved.
 *
 * Challenges are signed with an HMAC derived from APP_KEY, expire after a few minutes and can be used once.
 */
class Altcha
{
    public const FIELD = 'altcha';

    private const EXPIRES_MINUTES = 20;

    public static function enabled(): bool
    {
        // Google reCAPTCHA or Cloudflare Turnstile replace it when their keys are configured.
        return Captcha::driver() === 'altcha';
    }

    /**
     * @return array{algorithm: string, challenge: string, maxnumber: int, salt: string, signature: string}
     */
    public static function challenge(): array
    {
        $max = (int) config('vmnewswire.captcha.max_number', 100000);
        $salt = bin2hex(random_bytes(12)).'?expires='.now()->addMinutes(self::EXPIRES_MINUTES)->timestamp;
        $number = random_int((int) ($max / 10), $max);
        $challenge = hash('sha256', $salt.$number);

        return [
            'algorithm' => 'SHA-256',
            'challenge' => $challenge,
            'maxnumber' => $max,
            'salt' => $salt,
            'signature' => self::sign($challenge),
        ];
    }

    /** The decoded widget payload when it is a correctly solved, signed and unexpired challenge; null otherwise. */
    public static function verify(mixed $payload): ?array
    {
        if (! is_string($payload) || strlen($payload) > 2000) {
            return null;
        }

        $data = json_decode((string) base64_decode($payload, true), true);
        if (! is_array($data)) {
            return null;
        }

        foreach (['algorithm', 'challenge', 'number', 'salt', 'signature'] as $key) {
            if (! isset($data[$key]) || ! is_scalar($data[$key])) {
                return null;
            }
        }

        if ($data['algorithm'] !== 'SHA-256') {
            return null;
        }

        // The salt carries its own expiry: "<random>?expires=<unix time>" (covered by the signature).
        $salt = (string) $data['salt'];
        $query = str_contains($salt, '?') ? substr($salt, strpos($salt, '?') + 1) : '';
        parse_str($query, $params);
        if (! isset($params['expires']) || ! ctype_digit((string) $params['expires']) || (int) $params['expires'] < now()->timestamp) {
            return null;
        }

        $challenge = (string) $data['challenge'];

        if (! hash_equals(hash('sha256', $data['salt'].$data['number']), $challenge)) {
            return null;
        }

        if (! hash_equals(self::sign($challenge), (string) $data['signature'])) {
            return null;
        }

        return $data;
    }

    /** Marks a solved challenge as used. False when it was already used (a replayed solution). */
    public static function consume(string $challenge): bool
    {
        return Cache::add('altcha-used:'.$challenge, 1, now()->addMinutes(self::EXPIRES_MINUTES + 5));
    }

    private static function sign(string $challenge): string
    {
        return hash_hmac('sha256', $challenge, hash_hmac('sha256', 'altcha', (string) config('app.key')));
    }
}
