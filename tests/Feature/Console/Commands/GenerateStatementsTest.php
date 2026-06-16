<?php

namespace Tests\Feature\Console\Commands;

use App\Jobs\StatementCreation;
use App\Models\Statement;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class GenerateStatementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_default_amount_of_jobs(): void
    {
        Queue::fake();

        // Run command with defaults (1000 statements, today)
        $this->artisan('statements:generate')
            ->assertExitCode(0);

        // Verify 1000 jobs were dispatched (default amount)
        Queue::assertPushed(StatementCreation::class, 1000);
    }

    public function test_it_dispatches_custom_amount_of_jobs(): void
    {
        Queue::fake();

        // Run command with custom amount
        $this->artisan('statements:generate', ['amount' => 5])
            ->assertExitCode(0);

        // Verify 5 jobs were dispatched
        Queue::assertPushed(StatementCreation::class, 5);
    }

    public function test_it_handles_zero_amount(): void
    {
        Queue::fake();

        // Run command with zero amount
        $this->artisan('statements:generate', ['amount' => 0])
            ->assertExitCode(0);

        // Verify no jobs were dispatched
        Queue::assertNothingPushed();
    }

    public function test_it_dispatches_jobs_with_custom_date(): void
    {
        Queue::fake();

        // Run command with custom date
        $this->artisan('statements:generate', ['amount' => 3, 'date' => '2025-01-15'])
            ->assertExitCode(0);

        // Verify 3 jobs were dispatched
        Queue::assertPushed(StatementCreation::class, 3);

        $timestamps = $this->pushedStatementCreationTimestamps();
        $startOfDay = Carbon::parse('2025-01-15 00:00:00')->timestamp;
        $endOfDay = Carbon::parse('2025-01-15 23:59:59')->timestamp;

        $this->assertContainsOnly('int', $timestamps);
        $this->assertNotCount(1, array_unique($timestamps));

        foreach ($timestamps as $timestamp) {
            $this->assertGreaterThanOrEqual($startOfDay, $timestamp);
            $this->assertLessThanOrEqual($endOfDay, $timestamp);
        }
    }

    public function test_it_handles_sod_option_correctly(): void
    {
        Queue::fake();

        // Run command with --sod option
        $this->artisan('statements:generate', ['amount' => 2, 'date' => '2025-01-15', '--sod' => true])
            ->assertExitCode(0);

        $timestamps = $this->pushedStatementCreationTimestamps();

        $this->assertSame([
            Carbon::parse('2025-01-15 00:00:00')->timestamp,
        ], array_values(array_unique($timestamps)));
    }

    public function test_it_handles_eod_option_correctly(): void
    {
        Queue::fake();

        // Run command with --eod option
        $this->artisan('statements:generate', ['amount' => 2, 'date' => '2025-01-15', '--eod' => true])
            ->assertExitCode(0);

        $timestamps = $this->pushedStatementCreationTimestamps();

        $this->assertSame([
            Carbon::parse('2025-01-15 23:59:59')->timestamp,
        ], array_values(array_unique($timestamps)));
    }

    public function test_it_handles_both_sod_and_eod_options(): void
    {
        Queue::fake();

        // Run command with both --sod and --eod options
        // EOD should override SOD since it's processed after
        $this->artisan('statements:generate', ['amount' => 1, 'date' => '2025-01-15', '--sod' => true, '--eod' => true])
            ->assertExitCode(0);

        $timestamps = $this->pushedStatementCreationTimestamps();

        $this->assertSame([
            Carbon::parse('2025-01-15 23:59:59')->timestamp,
        ], array_values(array_unique($timestamps)));
    }

    public function test_it_creates_statements_on_requested_date_with_random_times(): void
    {
        Statement::query()->forceDelete();

        $this->artisan('statements:generate', ['amount' => 25, 'date' => '2026-06-01'])
            ->assertExitCode(0);

        $statements = Statement::query()->get();

        $this->assertCount(25, $statements);
        $this->assertSame(25, Statement::query()
            ->whereBetween('created_at', [
                '2026-06-01 00:00:00',
                '2026-06-01 23:59:59',
            ])
            ->count());
        $this->assertGreaterThan(1, $statements
            ->map(static fn (Statement $statement): string => $statement->created_at->format('H:i:s'))
            ->unique()
            ->count());
    }

    public function test_it_creates_statements_at_start_of_day_when_sod_is_given(): void
    {
        Statement::query()->forceDelete();

        $this->artisan('statements:generate', ['amount' => 3, 'date' => '2026-06-01', '--sod' => true])
            ->assertExitCode(0);

        $this->assertSame(3, Statement::query()->count());
        Statement::query()->each(function (Statement $statement): void {
            $this->assertSame('2026-06-01 00:00:00', $statement->created_at->format('Y-m-d H:i:s'));
            $this->assertSame('2026-06-01 00:00:00', $statement->updated_at->format('Y-m-d H:i:s'));
        });
    }

    public function test_it_creates_statements_at_end_of_day_when_eod_is_given(): void
    {
        Statement::query()->forceDelete();

        $this->artisan('statements:generate', ['amount' => 3, 'date' => '2026-06-01', '--eod' => true])
            ->assertExitCode(0);

        $this->assertSame(3, Statement::query()->count());
        Statement::query()->each(function (Statement $statement): void {
            $this->assertSame('2026-06-01 23:59:59', $statement->created_at->format('Y-m-d H:i:s'));
            $this->assertSame('2026-06-01 23:59:59', $statement->updated_at->format('Y-m-d H:i:s'));
        });
    }

    private function pushedStatementCreationTimestamps(): array
    {
        return Queue::pushed(StatementCreation::class)
            ->map(static fn (StatementCreation $job): int => $job->when)
            ->all();
    }
}
