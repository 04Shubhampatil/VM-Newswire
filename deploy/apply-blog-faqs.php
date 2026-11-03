<?php
// One-off: give the placeholder blog posts their own Q&A (from BlogSeeder) when they have none yet.
// Run from the app root:  php deploy/apply-blog-faqs.php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$seeder = new Database\Seeders\BlogSeeder;
$faqs = (new ReflectionMethod($seeder, 'faqs'))->invoke($seeder);

foreach (App\Models\PressRelease::whereNull('faqs')->get() as $post) {
    if (isset($faqs[$post->title])) {
        $post->update(['faqs' => $faqs[$post->title]]);
        echo 'FAQs added: ', $post->title, PHP_EOL;
    }
}
echo 'done', PHP_EOL;
