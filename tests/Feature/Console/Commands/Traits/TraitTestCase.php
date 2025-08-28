<?php

namespace Tests\Feature\Console\Commands\Traits;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\CreatesApplication;

abstract class TraitTestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        // Only create application, skip database setup
        $this->createApplication();
    }
}
