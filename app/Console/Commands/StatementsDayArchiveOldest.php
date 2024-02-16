<?php

namespace App\Console\Commands;

use App\Models\DayArchive;
use Exception;
use Illuminate\Console\Command;
use Throwable;

class StatementsDayArchiveOldest extends Command
{
    use CommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:day-archive-oldest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue a day archive compile for the oldest existing day archive.';

    /**
     * Execute the console command.
     * @throws Exception
     * @throws Throwable
     */
    public function handle(): void
    {
        $oldest = DayArchive::query()->orderBy('created_at', 'asc')->first();
        if ($oldest) {
            $date = $oldest->date->format('Y-m-d');
            $this->call('statements:day-archive', ['date' => $date]);
        }
    }
}
