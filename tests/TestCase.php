<?php

namespace Tests;

use App\Models\MediaOutlet;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Tests must not depend on built frontend assets.
        $this->withoutVite();
    }

    protected function admin(array $attributes = []): User
    {
        return User::factory()->create($attributes + ['role' => User::ROLE_ADMIN]);
    }

    /**
     * A package with featured and network outlets attached.
     */
    protected function packageWithMedia(array $attributes = [], int $featured = 2, int $network = 3): Package
    {
        $package = Package::factory()->create($attributes);

        $order = 0;
        foreach (MediaOutlet::factory()->count($featured)->create() as $outlet) {
            $package->mediaOutlets()->attach($outlet->id, ['is_featured' => true, 'display_order' => ++$order]);
        }
        foreach (MediaOutlet::factory()->count($network)->create() as $outlet) {
            $package->mediaOutlets()->attach($outlet->id, ['is_featured' => false, 'display_order' => ++$order]);
        }

        return $package;
    }
}
