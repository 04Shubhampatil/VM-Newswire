<?php

namespace App\Services;

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use App\Support\SpamAssessment;

/**
 * Scores an enquiry for spam using the rules in config/spam.php. Pure scoring: it never stores,
 * deletes or emails anything. EnquiryService decides what to do with the result.
 */
class SpamDetector
{
    /**
     * @param  array{name?: ?string, email?: ?string, company?: ?string, message?: ?string}  $data
     * @param  array{honeypot?: bool, too_fast?: bool, ip?: ?string, ignore_id?: ?int}  $context
     */
    public function assess(array $data, array $context = []): SpamAssessment
    {
        $config = config('spam');
        $name = (string) ($data['name'] ?? '');
        $company = (string) ($data['company'] ?? '');
        $message = (string) ($data['message'] ?? '');
        $text = $name."\n".$company."\n".$message;

        $reasons = [];
        $add = function (string $rule, int $points, string $label) use (&$reasons) {
            if ($points > 0) {
                $reasons[] = ['rule' => $rule, 'points' => $points, 'label' => $label];
            }
        };

        // Submission behaviour.
        if (! empty($context['honeypot'])) {
            $add('honeypot', (int) $config['honeypot'], 'Hidden bot-trap field was filled in');
        }
        if (! empty($context['too_fast'])) {
            $add('too_fast', (int) $config['too_fast'], 'Sent faster than a person can type');
        }

        // Text patterns.
        foreach ($config['patterns'] as $rule => [$points, $regex, $label]) {
            if (preg_match($regex, $text)) {
                $add($rule, (int) $points, $label);
            }
        }

        // Links.
        $links = $config['links'];
        $count = preg_match_all('~(https?://|www\.)\S+~i', $message);
        if ($count >= $links['many'][0]) {
            $add('links_many', (int) $links['many'][1], "{$count} links in the message");
        }
        if ($count >= $links['very_many'][0]) {
            $add('links_very_many', (int) $links['very_many'][1], 'Very many links');
        }
        if (preg_match($links['shortener'][1], $text)) {
            $add('link_shortener', (int) $links['shortener'][0], 'Shortened (hidden) link');
        }
        if (preg_match($links['price_sheet'][1], $message)) {
            $add('price_sheet', (int) $links['price_sheet'][0], 'Link to a price-list spreadsheet');
        }
        if (preg_match('~(https?://|www\.|\.(com|ru|net|org|xyz|top|info|io)\b|t\.me/)~i', $name.' '.$company)) {
            $add('link_in_name', (int) $links['in_name'], 'Link in the name or company field');
        }

        $email = (string) ($data['email'] ?? '');
        if ($email !== '' && preg_match($config['seo_sender'][1], $email)) {
            $add('seo_sender', (int) $config['seo_sender'][0], 'SEO / guest-post wording in the sender address');
        }
        if (preg_match('/\d{4,}/', $name)) {
            $add('name_digits', (int) $config['name_digits'], 'Digits in the name');
        }

        if (self::looksRandom($message)) {
            $add('random_text', (int) $config['random_text'], 'Message is random characters');
        }

        $this->duplicates($data, $context, $add);

        return new SpamAssessment(
            min(100, array_sum(array_column($reasons, 'points'))),
            $reasons,
            (int) $config['spam_threshold'],
            (int) $config['review_threshold'],
        );
    }

    /**
     * True for messages made mostly of random letter/digit strings ("IywOV7f 2KzA TxH0LC4 ..."),
     * which real enquiries never are.
     */
    public static function looksRandom(string $message): bool
    {
        $words = preg_split('/\s+/u', trim($message), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (count($words) < 3) {
            return false;
        }

        $random = 0;
        foreach ($words as $word) {
            // A capital letter after the first character, mixed with lower case: "PtwM", "nQFSViY".
            $mixedCase = preg_match('/\p{Ll}/u', $word) && preg_match('/\p{Lu}/u', mb_substr($word, 1));
            $lettersAndDigits = preg_match('/\p{L}/u', $word) && preg_match('/\d/', $word);
            if (mb_strlen($word) >= 4 && preg_match('/^[\p{L}\d]+$/u', $word) && ($lettersAndDigits || $mixedCase)) {
                $random++;
            }
        }

        return $random >= 3 && $random / count($words) >= 0.5;
    }

    /** Normalised text used to spot the same message sent again. */
    public static function fingerprint(string $message): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $message)));
    }

    private function duplicates(array $data, array $context, callable $add): void
    {
        $rules = config('spam.duplicates');
        $email = mb_strtolower((string) ($data['email'] ?? ''));
        $fingerprint = self::fingerprint((string) ($data['message'] ?? ''));
        $ignoreId = $context['ignore_id'] ?? null;

        if ($fingerprint === '') {
            return;
        }

        $recent = Enquiry::query()
            ->where('created_at', '>=', now()->subDays(7))
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->latest('id')
            ->limit(500)
            ->get(['id', 'email', 'message', 'created_at', 'ip_address', 'status']);

        $sameMessage = $recent->filter(fn (Enquiry $e) => self::fingerprint((string) $e->message) === $fingerprint);

        if ($email !== '' && $sameMessage->contains(fn (Enquiry $e) => mb_strtolower($e->email) === $email && $e->created_at >= now()->subDay())) {
            $add('duplicate', (int) $rules['same_email_same_message'], 'Same message already sent from this address');
        }

        $otherSenders = $sameMessage->map(fn (Enquiry $e) => mb_strtolower($e->email))->reject(fn ($e) => $e === $email)->unique()->count();
        if ($otherSenders >= $rules['same_message_other_emails'][0]) {
            $add('bulk_message', (int) $rules['same_message_other_emails'][1], "Same message sent from {$otherSenders} other addresses");
        }

        $ip = $context['ip'] ?? null;
        if ($ip) {
            $fromIp = $recent->where('ip_address', $ip)->where('created_at', '>=', now()->subDay())->count();
            if ($fromIp + 1 >= $rules['same_ip_per_day'][0]) {
                $add('ip_flood', (int) $rules['same_ip_per_day'][1], ($fromIp + 1).' enquiries from one IP today');
            }
        }
    }
}
