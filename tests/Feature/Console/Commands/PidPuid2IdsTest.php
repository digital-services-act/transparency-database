<?php

namespace Tests\Feature\Console\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\VarDumper\VarDumper;
use Tests\Support\ElasticMocker;
use Tests\TestCase;

class PidPuid2IdsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_runs_without_errors(): void
    {
        ElasticMocker::fake()->puidSearchReturns(['id1', 'id2', 'id3']);

        // Mock VarDumper::dump
        VarDumper::setHandler(function ($var) {
            // Just capture that dump was called with the expected data
            $this->assertEquals(['id1', 'id2', 'id3'], $var);
        });

        // Run the command
        $this->artisan('pidpuid2ids', ['platform_id' => '123', 'puid' => 'test-puid-456'])
            ->assertExitCode(0);
    }

    public function test_it_handles_string_platform_id_conversion(): void
    {
        ElasticMocker::fake()->puidSearchReturns(['result1']);

        // Mock VarDumper::dump
        VarDumper::setHandler(function ($var) {
            $this->assertEquals(['result1'], $var);
        });

        // Run the command with string platform_id that should be converted to int
        $this->artisan('pidpuid2ids', ['platform_id' => '999', 'puid' => 'another-puid'])
            ->assertExitCode(0);
    }

    public function test_it_handles_empty_result(): void
    {
        ElasticMocker::fake()->puidSearchReturns([]);

        // Mock VarDumper::dump
        VarDumper::setHandler(function ($var) {
            $this->assertEquals([], $var);
        });

        // Run the command
        $this->artisan('pidpuid2ids', ['platform_id' => '456', 'puid' => 'empty-puid'])
            ->assertExitCode(0);
    }
}
