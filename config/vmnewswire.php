<?php

return [

    /*
    | Categories used to group media outlets on the public site and in the admin.
    */
    'media_categories' => [
        'Newswire',
        'News',
        'Business',
        'Finance',
        'Technology',
        'Markets',
        'Digital Media',
    ],

    /*
    | One-line descriptions for the category cards on the home page and Media Network page.
    */
    'media_category_descriptions' => [
        'Newswire' => 'Newswire distribution to business and financial media.',
        'News' => 'Top news and current affairs coverage.',
        'Business' => 'Business insights and industry updates.',
        'Finance' => 'Finance, investment and market trends.',
        'Technology' => 'Tech innovations and digital transformation.',
        'Markets' => 'Financial markets, investing and economy news.',
        'Digital Media' => 'Digital publications with broad consumer reach.',
    ],

    /*
    | Where new-enquiry notifications go when no address is set in admin settings.
    */
    'admin_notification_email' => env('ADMIN_NOTIFICATION_EMAIL', env('MAIL_FROM_ADDRESS')),

    'enquiries' => [
        // Submissions allowed per IP address.
        'per_minute' => (int) env('ENQUIRY_RATE_PER_MINUTE', 3),
        'per_day' => (int) env('ENQUIRY_RATE_PER_DAY', 20),
        'message_max' => 5000,
        'release_counts' => ['1', '2–5', '6–10', 'More than 10'],
    ],

    'sample_reports' => [
        'disk' => env('SAMPLE_REPORTS_DISK', 'local'),
        'directory' => 'sample-reports',
        'max_kb' => (int) env('SAMPLE_REPORT_MAX_KB', 10240),
    ],

    /*
    | Hero posters for media outlets. Uploads are re-encoded to WebP at max_width.
    */
    'posters' => [
        'disk' => env('POSTERS_DISK', 'public'),
        'directory' => 'media-network',
        'max_kb' => 5120,
        'min_width' => 300,
        'min_height' => 200,
        'max_width' => 800,
        'quality' => 82,
    ],

    'media_logos' => [
        'disk' => env('MEDIA_LOGOS_DISK', 'public'),
        'directory' => 'media-logos',
        'max_kb' => 1024,
    ],

    'countries' => [
        'United States', 'United Kingdom', 'India', 'Canada', 'Australia', 'United Arab Emirates',
        'Singapore', 'Germany', 'France', 'Netherlands', 'Hong Kong', 'Japan', 'South Africa', 'Other',
    ],

    /*
    | Optional Cloudflare Turnstile. Leave both keys empty to disable.
    */
    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],

    'ga4_measurement_id' => env('GA4_MEASUREMENT_ID'),

    /*
    | First admin account, used by the seeder and php artisan vmn:create-admin.
    | Leave ADMIN_PASSWORD empty to generate a one-time password.
    */
    'admin' => [
        'name' => env('ADMIN_NAME', 'Administrator'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    'seed_demo_data' => (bool) env('SEED_DEMO_DATA', false),

];
