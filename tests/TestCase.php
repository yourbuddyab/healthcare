<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Passport\Client;

abstract class TestCase extends BaseTestCase
{
    /**
     * Bootstrap the application for testing.
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Run migrations for fresh DB
        $this->artisan('migrate');

        // Create Passport personal access client manually for tests
        Client::create([
            'name' => 'Personal Access Client',
            'secret' => 'test-secret',
            'redirect_uris' => ['http://localhost'],
            'grant_types' => ['personal_access'],
            'revoked' => false,
        ]);


        // This will make debugging easier
        $this->withoutExceptionHandling();
    }
}
