<?php

namespace App\Console\Commands;

use App\Jobs\StatementCreation;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateStatements extends Command
{
    use CommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:generate {amount=1000} {date=today} {--sod} {--eod}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create X Statements';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (strtolower((string) config('app.env')) === 'production') {
            $this->error('The statements:generate command cannot run in production.');

            return self::FAILURE;
        }

        $date = $this->sanitizeDateArgument();
        $amount = $this->intifyArgument('amount');

        for ($cpt = 0; $cpt < $amount; $cpt++) {
            StatementCreation::dispatch($this->statementTimestamp($date));
        }

        return self::SUCCESS;
    }

    private function statementTimestamp(Carbon $date): int
    {
        if ($this->option('eod')) {
            return $date->copy()->endOfDay()->timestamp;
        }

        if ($this->option('sod')) {
            return $date->copy()->startOfDay()->timestamp;
        }

        $now = Carbon::now();

        return $date->copy()
            ->setTime($now->hour, $now->minute, $now->second)
            ->timestamp;
    }
}
