<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\MediaOutlet;
use App\Models\Package;
use Illuminate\Database\Seeder;

/**
 * DEVELOPMENT / DEMO DATA ONLY.
 *
 * Package names and headline outlets come from the VM Newswire requirements.
 * Prices, package-to-outlet mappings and every "Demo Outlet" row are placeholders
 * and do not represent confirmed commercial distribution. Replace in /admin.
 */
class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        if (Package::withTrashed()->exists()) {
            $this->command?->info('Packages already exist: demo catalog skipped.');

            return;
        }

        $real = [
            // name, category, highlighted, website, short description
            ['AccessWire', 'Newswire', true, 'https://www.accesswire.com', 'Newswire distribution to financial and business media.'],
            ['GlobeNewswire', 'Newswire', false, 'https://www.globenewswire.com', 'Global newswire with regional and industry circuits.'],
            ['AP News', 'News', true, 'https://apnews.com', 'National and international news coverage.'],
            ['Business Insider', 'Business', true, 'https://www.businessinsider.com', 'Business, markets and technology publication.'],
            ['Yahoo Finance', 'Finance', true, 'https://finance.yahoo.com', 'Finance news and market data platform.'],
            ['MSN', 'Digital Media', true, 'https://www.msn.com', 'Digital media portal with broad consumer reach.'],
            ['USA Today', 'News', true, 'https://www.usatoday.com', 'National news and lifestyle coverage.'],
            ['Benzinga', 'Markets', true, 'https://www.benzinga.com', 'Markets, trading and investor news.'],
            ['Digital Journal', 'Digital Media', true, 'https://www.digitaljournal.com', 'Digital news and press coverage.'],
            ['StreetInsider', 'Markets', false, 'https://www.streetinsider.com', 'Market-moving news for investors.'],
        ];

        $outlets = [];
        foreach ($real as $i => [$name, $category, $highlighted, $url, $short]) {
            $outlets[$name] = MediaOutlet::create([
                'name' => $name, 'category' => $category, 'website_url' => $url, 'short_description' => $short,
                'is_highlighted' => $highlighted, 'display_order' => $i + 1,
            ]);
        }

        $categories = config('vmnewswire.media_categories');
        $demo = collect(range(1, 60))->map(fn (int $n) => MediaOutlet::create([
            'name' => sprintf('Demo Outlet %03d', $n),
            'category' => $categories[$n % count($categories)],
            'display_order' => 100 + $n,
        ]));

        $standard = ['Major media distribution', 'Digital publication network', 'Professional reporting', 'Sample report'];

        $packages = [
            [
                'name' => 'AccessWire', 'brand' => 'AccessWire', 'price' => 149,
                'short_description' => 'Core newswire distribution for a single announcement.',
                'distribution_summary' => 'AccessWire newswire plus the digital publication network',
                'featured' => ['AccessWire'], 'network' => [1, 40],
            ],
            [
                'name' => 'AccessWire + BI + AP News', 'slug' => 'accesswire-bi-ap-news', 'brand' => 'AccessWire', 'price' => 399, 'is_highlighted' => true,
                'short_description' => 'AccessWire distribution with Business Insider and AP News.',
                'distribution_summary' => 'AccessWire, two premium placements and 200+ outlets',
                'features' => ['AccessWire distribution', 'Business Insider placement', 'AP News placement', 'Digital publication network', 'Professional reporting', 'Sample report'],
                'featured' => ['AccessWire', 'Business Insider', 'AP News', 'Yahoo Finance', 'Benzinga', 'StreetInsider', 'Digital Journal'], 'network' => [1, 60],
            ],
            [
                'name' => 'GlobeNewswire Basic', 'brand' => 'GlobeNewswire', 'price' => 299,
                'short_description' => 'GlobeNewswire distribution with network pickup.',
                'distribution_summary' => 'GlobeNewswire plus the digital publication network',
                'featured' => ['GlobeNewswire'], 'network' => [1, 40],
            ],
            [
                'name' => 'GlobeNewswire New York Metro', 'slug' => 'globenewswire-new-york-metro', 'brand' => 'GlobeNewswire', 'price' => 449,
                'short_description' => 'GlobeNewswire distribution focused on the New York metro area.',
                'distribution_summary' => 'GlobeNewswire New York Metro circuit and regional media',
                'featured' => ['GlobeNewswire'], 'network' => [20, 50],
            ],
            [
                'name' => 'MSN', 'brand' => 'MSN', 'price' => 249,
                'short_description' => 'Publication on MSN with extended network reach.',
                'distribution_summary' => 'MSN plus the digital publication network',
                'featured' => ['MSN'], 'network' => [1, 40],
            ],
            [
                'name' => 'USA Today', 'brand' => 'USA Today', 'price' => 499,
                'short_description' => 'Publication on USA Today with extended network reach.',
                'distribution_summary' => 'USA Today plus the digital publication network',
                'featured' => ['USA Today'], 'network' => [1, 40],
            ],
        ];

        foreach ($packages as $order => $data) {
            $package = Package::create([
                'name' => $data['name'],
                'slug' => $data['slug'] ?? null,
                'brand' => $data['brand'],
                'short_description' => $data['short_description'],
                'full_content' => "[Demo content] {$data['short_description']} Replace this description in the admin panel with the confirmed package details: who it is for, turnaround and any conditions.",
                'distribution_summary' => $data['distribution_summary'],
                'features' => $data['features'] ?? $standard,
                'price' => $data['price'],
                'currency' => 'USD',
                'is_highlighted' => $data['is_highlighted'] ?? false,
                'display_order' => $order + 1,
            ]);

            $attach = [];
            foreach ($data['featured'] as $i => $name) {
                $attach[$outlets[$name]->id] = ['is_featured' => true, 'display_order' => $i + 1];
            }
            [$from, $to] = $data['network'];
            foreach ($demo->slice($from - 1, $to - $from + 1) as $i => $outlet) {
                $attach[$outlet->id] = ['is_featured' => false, 'display_order' => 100 + $i];
            }
            $package->mediaOutlets()->attach($attach);
        }

        $flagship = Package::where('slug', 'accesswire-bi-ap-news')->first();
        foreach ([
            ['What is included in this package?', 'AccessWire newswire distribution, placement on Business Insider and AP News, and distribution across the extended network of digital publications, followed by a distribution report.'],
            ['Can I see where my release will appear?', 'Yes. Download the sample report for this package to see the outlets and live links from a previous distribution.'],
        ] as $i => [$q, $a]) {
            Faq::create(['package_id' => $flagship->id, 'category' => 'Packages', 'question' => $q, 'answer' => $a, 'display_order' => $i + 1]);
        }

        $this->command?->warn('Seeded DEMO packages and outlets (placeholder prices). Replace them in /admin before launch.');
    }
}
