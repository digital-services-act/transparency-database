<?php

namespace Tests\Feature\Console\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ElasticMocker;
use Tests\TestCase;

class UuidToIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_runs_without_errors(): void
    {
        ElasticMocker::fake()->uuidSearchReturns(12345);

        $this->artisan('uuid2id', ['uuid' => 'test-uuid-123'])
            ->expectsOutput('ID: 12345')
            ->assertExitCode(0);
    }
}
