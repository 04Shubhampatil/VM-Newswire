<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Content (settings, legal drafts, FAQs) and the admin account are always seeded.
     * Packages and media outlets are DEMO data and only seeded outside production,
     * or when SEED_DEMO_DATA=true is set explicitly.
     */
    public function run(): void
    {
        $this->call([
            SiteContentSeeder::class,
            AdminUserSeeder::class,
        ]);

        if (! app()->isProduction() || config('vmnewswire.seed_demo_data')) {
            $this->call(DemoCatalogSeeder::class);
        } else {
            $this->command?->warn('Production: skipped demo packages and media outlets. Add real ones in /admin.');
        }
    }
}
