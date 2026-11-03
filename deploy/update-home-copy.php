<?php
// One-off: replace the homepage copy stored in site_settings with the new VM Newswire wording.
// Run from the app root:  php deploy/update-home-copy.php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$defaults = App\Services\SiteSettings::defaults();
$keys = ['hero_headline', 'hero_headline_highlight', 'hero_text', 'confidence_heading', 'journalists_eyebrow', 'journalists_heading', 'journalists_text'];

foreach ($keys as $key) {
    App\Models\SiteSetting::updateOrCreate(['setting_key' => $key], ['setting_value' => $defaults[$key]]);
    echo str_pad($key, 26), '=> ', json_encode($defaults[$key]), PHP_EOL;
}

app(App\Services\SiteSettings::class)->flush();
echo 'done', PHP_EOL;
