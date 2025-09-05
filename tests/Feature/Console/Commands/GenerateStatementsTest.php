<?php

namespace Tests\Feature\Console\Commands;

use App\Jobs\StatementCreation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class GenerateStatementsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_dispatches_default_amount_of_jobs(): void
    {
        Queue::fake();

        // Run command with defaults (200 statements, today)
        $this->artisan('statements:generate')
            ->assertExitCode(0);

        // Verify 200 jobs were dispatched (default amount)
        Queue::assertPushed(StatementCreation::class, 200);
    }

    /**
     * @test
     */
    public function it_dispatches_custom_amount_of_jobs(): void
    {
        Queue::fake();

        // Run command with custom amount
        $this->artisan('statements:generate', ['amount' => 5])
            ->assertExitCode(0);

        // Verify 5 jobs were dispatched
        Queue::assertPushed(StatementCreation::class, 5);
    }

    /**
     * @test
     */
    public function it_handles_zero_amount(): void
    {
        Queue::fake();

        // Run command with zero amount
        $this->artisan('statements:generate', ['amount' => 0])
            ->assertExitCode(0);

        // Verify no jobs were dispatched
        Queue::assertNothingPushed();
    }

    /**
     * @test
     */
    public function it_dispatches_jobs_with_custom_date(): void
    {
        Queue::fake();

        // Run command with custom date
        $this->artisan('statements:generate', ['amount' => 3, 'date' => '2025-01-15'])
            ->assertExitCode(0);

        // Verify 3 jobs were dispatched
        Queue::assertPushed(StatementCreation::class, 3);

        // Verify jobs were dispatched with correct timestamp
        Queue::assertPushed(StatementCreation::class, function ($job) {
            // Check that the timestamp corresponds to 2025-01-15
            $date = \Carbon\Carbon::createFromTimestamp($job->when);

            return $date->format('Y-m-d') === '2025-01-15';
        });
    }

    /**
     * @test
     */
    public function it_handles_sod_option_correctly(): void
    {
        Queue::fake();

        // Run command with --sod option
        $this->artisan('statements:generate', ['amount' => 2, 'date' => '2025-01-15', '--sod' => true])
            ->assertExitCode(0);

        // Verify jobs were dispatched with start of day timestamp
        Queue::assertPushed(StatementCreation::class, function ($job) {
            $date = \Carbon\Carbon::createFromTimestamp($job->when);

            return $date->format('Y-m-d H:i:s') === '2025-01-15 00:00:00';
        });
    }

    /**
     * @test
     */
    public function it_handles_eod_option_correctly(): void
    {
        Queue::fake();

        // Run command with --eod option
        $this->artisan('statements:generate', ['amount' => 2, 'date' => '2025-01-15', '--eod' => true])
            ->assertExitCode(0);

        // Verify jobs were dispatched with end of day timestamp
        Queue::assertPushed(StatementCreation::class, function ($job) {
            $date = \Carbon\Carbon::createFromTimestamp($job->when);

            return $date->format('Y-m-d H:i:s') === '2025-01-15 23:59:59';
        });
    }

    /**
     * @test
     */
    public function it_handles_both_sod_and_eod_options(): void
    {
        Queue::fake();

        // Run command with both --sod and --eod options
        // EOD should override SOD since it's processed after
        $this->artisan('statements:generate', ['amount' => 1, 'date' => '2025-01-15', '--sod' => true, '--eod' => true])
            ->assertExitCode(0);

        // Verify job was dispatched with end of day timestamp (eod overrides sod)
        Queue::assertPushed(StatementCreation::class, function ($job) {
            $date = \Carbon\Carbon::createFromTimestamp($job->when);

            return $date->format('Y-m-d H:i:s') === '2025-01-15 23:59:59';
        });
    }
}
