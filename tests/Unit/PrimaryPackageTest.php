<?php

namespace Tests\Unit;

use App\Models\MediaOutlet;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrimaryPackageTest extends TestCase
{
    use RefreshDatabase;

    public function test_outlet_cards_link_to_the_matching_package_then_most_popular_then_first(): void
    {
        $accesswire = Package::factory()->create(['name' => 'AccessWire', 'brand' => 'AccessWire', 'display_order' => 1]);
        $bundle = Package::factory()->create(['name' => 'AccessWire + BI + AP News', 'brand' => 'AccessWire', 'is_highlighted' => true, 'display_order' => 3]);
        $benzinga = Package::factory()->create(['name' => 'Benzinga Markets Reach', 'brand' => 'Benzinga', 'display_order' => 7]);
        $globe = Package::factory()->create(['name' => 'GlobeNewswire Basic', 'brand' => 'GlobeNewswire', 'display_order' => 2]);

        $access = MediaOutlet::factory()->create(['name' => 'Access Newswire']);
        $access->packages()->attach([$accesswire->id => ['is_featured' => true], $bundle->id => ['is_featured' => true]]);

        $benz = MediaOutlet::factory()->create(['name' => 'Benzinga']);
        $benz->packages()->attach([$bundle->id => ['is_featured' => true], $benzinga->id => ['is_featured' => true]]);

        $yahoo = MediaOutlet::factory()->create(['name' => 'Yahoo Finance']);
        $yahoo->packages()->attach([$globe->id => ['is_featured' => false], $accesswire->id => ['is_featured' => false]]);

        $lonely = MediaOutlet::factory()->create(['name' => 'Nowhere Daily']);

        // No package named after the outlet → the "Most popular" one wins.
        $this->assertSame('AccessWire + BI + AP News', $access->toPosterArray()['package_name']);
        // A package named/branded after the outlet beats "Most popular".
        $this->assertSame('Benzinga Markets Reach', $benz->toPosterArray()['package_name']);
        $this->assertSame(route('packages.show', 'benzinga-markets-reach'), $benz->toPosterArray()['package_url']);
        // Otherwise the first package in display order.
        $this->assertSame('AccessWire', $yahoo->toPosterArray()['package_name']);
        $this->assertNull($lonely->toPosterArray()['package_url']);
    }
}
