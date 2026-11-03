<?php

/*
 * Enquiry spam scoring (App\Services\SpamDetector).
 *
 * Every rule that matches adds its points (each rule counts once). The total decides what happens:
 *   score >= spam_threshold   → saved with status "Spam": no confirmation email, no team notification.
 *   score >= review_threshold → saved as "New" with a "Possible spam" badge for the team to review;
 *                               emails go out as normal, so a real customer is never ignored.
 *   below                     → a normal enquiry.
 * Nothing is ever deleted.
 */
return [
    'spam_threshold' => (int) env('SPAM_THRESHOLD', 10),
    'review_threshold' => (int) env('SPAM_REVIEW_THRESHOLD', 5),

    /*
     * Text patterns, matched against the name, company and message (case-insensitive unless the
     * pattern says otherwise). [rule => [points, regex, label shown to the admin]]
     */
    'patterns' => [
        // SEO / link-selling outreach
        'seo_guest_post' => [4, '/\bguest[\s-]?(post|posts|posting|blog|blogging|article)s?\b/i', 'Guest post offer'],
        'seo_backlinks' => [3, '/\bback[\s-]?links?\b/i', 'Backlink selling'],
        'seo_link_building' => [4, '/\blink[\s-]?building\b/i', 'Link building'],
        'seo_link_exchange' => [4, '/\b(link[\s-]?(exchange|swap|insertion)s?|niche[\s-]?edits?)\b/i', 'Link exchange / insertion'],
        'seo_dofollow' => [3, '/\bdo[\s-]?follow\b/i', 'Dofollow links'],
        // DA/DR only in their SEO sense: "DA 50", "DA/DR", "high DA", "domain authority".
        'seo_da_dr' => [4, '/(\b(DA|DR|PA)\s*(\/|,|&|and)\s*(DA|DR|PA)\b|\b(high|strong|good)\s+(DA|DR)\b|\b(DA|DR)\s*:?\s*\d{2}\+?|\bdomain\s+(authority|rating)\b)/', 'DA / DR metrics'],
        'seo_services' => [3, '/\b(seo\s+(services?|packages?|experts?|agency|company|team)|first\s+page\s+of\s+google|rank(ing)?\s+(your\s+(website|site)\s+)?(on|in|at)\s+(the\s+)?(top|first\s+page)|sponsored\s+posts?|write\s+for\s+us|paid\s+(guest\s+)?posts?)\b/i', 'SEO service promotion'],
        'seo_your_site' => [2, '/\b(your\s+(website|site|domain)\b.{0,80}\b(traffic|ranking|rankings|seo|backlinks?|authority))/is', 'Pitch about your website\'s SEO'],
        'bulk_mail_footer' => [3, '/\b(unsubscribe|reply\s+with\s+(a\s+)?["“\']?no\b|anti[\s-]?spam\s+laws?|opt[\s-]?out)\b/i', 'Bulk-mail footer'],

        'content_placement' => [3, '/((content|articles?|posts?)\s+(placed|placements?)|editorial\s+(websites?|placements?|links?)|placements?\s+(like|on)\s+(these|this|that|editorial))/i', 'Content placement offer'],

        // Russian bait campaigns: "Вам перевод 187721 руб. забрать тут …" and "Ваш приз … по ссылке https://….buzz".
        // A money amount is required for the transfer words, because "перевод" also means "translation".
        'ru_money_bot' => [10, '/(\d[\d\s]*\s*(руб|рубл|₽)|(перевод|выплат|зачислен|начислен|компенсаци)\D{0,30}\d{3,}|(получить|забрать)\s+тут|(приз|выигрыш|вознагражд)[\s\S]{0,120}(ссылк|https?:))/iu', 'Russian money / prize bait'],
    ],

    // Links in the message.
    'links' => [
        'many' => [3, 3],          // [at least N links, points]
        'very_many' => [5, 3],     // added on top of "many"
        'shortener' => [5, '/\b(tinyurl\.com|bit\.ly|t\.co|goo\.gl|cutt\.ly|is\.gd|rb\.gy|shorturl\.at|tiny\.cc|ow\.ly|rebrand\.ly|t\.me)\//i'],
        'price_sheet' => [3, '/docs\.google\.com\/spreadsheets\//i'],
        // A link in the name or company field (real people do not put URLs there).
        'in_name' => 10,
    ],

    // The sender's address itself advertises SEO / guest posts ("azel.seomarketing@…", "@guestpostservicecompany.com").
    'seo_sender' => [4, '/(seo|guest[._-]?post|backlink|link[._-]?build|outreach)/i'],

    // A run of 4+ digits in the name ("NARTYTRYUT733223", "THRT158413"). The form no longer accepts
    // digits in names; this catches older records.
    'name_digits' => 10,

    // Message made mostly of random letter/digit strings ("IywOV7f 2KzA TxH0LC4").
    'random_text' => 10,

    // Signals from the submission itself.
    'honeypot' => 10,        // the hidden "website" field was filled in
    'too_fast' => 6,         // sent less than a few seconds after the form was opened

    // Repeats.
    'duplicates' => [
        'same_email_same_message' => 4,      // same address sent the same text in the last 24 hours
        'same_message_other_emails' => [2, 6], // same text from at least N other addresses in 7 days → points
        'same_ip_per_day' => [5, 3],          // at least N enquiries from one IP in 24 hours → points
    ],
];
