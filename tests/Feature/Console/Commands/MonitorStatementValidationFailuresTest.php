<?php

namespace Tests\Feature\Console\Commands;

use Illuminate\Foundation\Testing\TestCase;
use Tests\CreatesApplication;

class MonitorStatementValidationFailuresTest extends TestCase
{
    use CreatesApplication;

    public function test_help_displays_clever_cloud_monitoring_options(): void
    {
        $this->artisan('statements:monitor-validation-failures --help')
            ->expectsOutputToContain('Monitor statement validation failure logs and summarize the worst offending platforms.')
            ->expectsOutputToContain('--clever-app')
            ->expectsOutputToContain('--clever-bin')
            ->expectsOutputToContain('--local')
            ->assertExitCode(0);
    }
}
