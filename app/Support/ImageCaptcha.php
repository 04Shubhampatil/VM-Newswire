<?php

namespace App\Support;

use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Cache;

/**
 * Classic picture captcha for the enquiry form: a short distorted word in an image that the visitor
 * types into a box ("Type the characters above"). Self-hosted, drawn with GD; no third-party service.
 *
 * The answer is kept server-side (as an HMAC, never in the page), keyed by the visitor's session id and
 * the form, so the popup form and the page form each have their own image. It lives in the cache rather
 * than the session itself: both images load at the same moment, and unlocked session writes could
 * overwrite each other. Each answer can be checked once and expires after a few minutes.
 */
class ImageCaptcha
{
    public const FIELD = 'captcha';

    public const FORM_FIELD = 'captcha_form';

    /** No look-alike characters (0/o, 1/l/i, …). */
    private const ALPHABET = 'abcdefghjkmnpqrstuvwxyz23456789';

    private const LENGTH = 5;

    private const EXPIRES_MINUTES = 10;

    public function __construct(private Session $session) {}

    /** Valid form keys are short ids like "enq" / "menq". */
    public static function formKey(mixed $form): ?string
    {
        return is_string($form) && preg_match('/^[a-z]{1,12}$/', $form) ? $form : null;
    }

    /** New random word for this form; returns the PNG to show. */
    public function issue(string $form): string
    {
        $code = '';
        for ($i = 0; $i < self::LENGTH; $i++) {
            $code .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
        }

        $this->remember($form, $code);

        return $this->render($code);
    }

    /** Stores the expected answer for this visitor and form. */
    public function remember(string $form, string $code): void
    {
        Cache::put($this->key($form), $this->hash($code), now()->addMinutes(self::EXPIRES_MINUTES));
    }

    /**
     * Checks the typed word and always uses it up (right or wrong), so it cannot be guessed repeatedly.
     */
    public function check(?string $form, mixed $answer): bool
    {
        $form = self::formKey($form);
        if ($form === null) {
            return false;
        }

        $expected = Cache::pull($this->key($form)); // used up whether right or wrong
        if (! is_string($expected) || ! is_string($answer)) {
            return false;
        }

        $typed = strtolower(preg_replace('/\s+/', '', $answer));

        return $typed !== '' && hash_equals($expected, $this->hash($typed));
    }

    private function key(string $form): string
    {
        return 'image-captcha:'.hash('sha256', $this->session->getId()).':'.$form;
    }

    private function hash(string $code): string
    {
        return hash_hmac('sha256', strtolower($code), (string) config('app.key'));
    }

    /** Draws the word: enlarged, individually rotated letters, a wave distortion, and noise. */
    private function render(string $code): string
    {
        $width = 200;
        $height = 64;

        $canvas = imagecreatetruecolor($width, $height);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 232, 236, 242));

        // Background noise.
        for ($i = 0; $i < 6; $i++) {
            $c = imagecolorallocate($canvas, random_int(170, 210), random_int(175, 215), random_int(190, 225));
            imagesetthickness($canvas, random_int(1, 2));
            imageline($canvas, random_int(0, $width), random_int(0, $height), random_int(0, $width), random_int(0, $height), $c);
        }
        for ($i = 0; $i < 260; $i++) {
            $c = imagecolorallocate($canvas, random_int(150, 220), random_int(150, 220), random_int(170, 230));
            imagesetpixel($canvas, random_int(0, $width - 1), random_int(0, $height - 1), $c);
        }

        // Letters: GD's built-in font, scaled up 3x and rotated one by one.
        $x = 14;
        foreach (str_split($code) as $char) {
            $small = imagecreatetruecolor(10, 16);
            imagealphablending($small, false);
            imagefill($small, 0, 0, imagecolorallocatealpha($small, 0, 0, 0, 127));
            imagealphablending($small, true);
            $ink = imagecolorallocate($small, random_int(30, 70), random_int(30, 60), random_int(100, 150));
            imagestring($small, 5, 0, 0, $char, $ink);
            imagestring($small, 5, 1, 0, $char, $ink); // twice, 1px apart: a bolder stroke

            $big = imagecreatetruecolor(30, 48);
            imagealphablending($big, false);
            imagefill($big, 0, 0, imagecolorallocatealpha($big, 0, 0, 0, 127));
            imagecopyresampled($big, $small, 0, 0, 0, 0, 30, 48, 10, 16);

            $rotated = imagerotate($big, random_int(-22, 22), imagecolorallocatealpha($big, 0, 0, 0, 127));
            imagealphablending($canvas, true);
            imagecopy($canvas, $rotated, $x, random_int(2, 12), 0, 0, imagesx($rotated), imagesy($rotated));

            $x += random_int(30, 35);
        }

        // Wave distortion.
        $waved = imagecreatetruecolor($width, $height);
        imagefill($waved, 0, 0, imagecolorallocate($waved, 232, 236, 242));
        $amplitude = random_int(2, 4);
        $period = random_int(28, 40);
        $phase = random_int(0, 100) / 10;
        for ($col = 0; $col < $width; $col++) {
            $shift = (int) round($amplitude * sin($col / $period * 2 * M_PI + $phase));
            imagecopy($waved, $canvas, $col, $shift, $col, 0, 1, $height);
        }

        // A thin line through the word.
        $c = imagecolorallocate($waved, 70, 70, 130);
        imagesetthickness($waved, 2);
        $y1 = random_int(24, 44);
        imageline($waved, 6, $y1, $width - 6, $y1 + random_int(-12, 12), $c);

        ob_start();
        imagepng($waved);

        return (string) ob_get_clean();
    }
}
