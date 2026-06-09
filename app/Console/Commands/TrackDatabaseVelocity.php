<?php

namespace App\Console\Commands;

use App\Models\DatabaseVelocity;
use App\Models\Statement;
use Illuminate\Console\Command;

class TrackDatabaseVelocity extends Command
{
    protected $signature = 'statements:track-velocity';

    protected $description = 'Track the ingestion velocity of statements_beta every minute';

    public function handle(): void
    {
        $maxId = Statement::query()->max('id') ?? 0;

        $rowsPerSecond = (float) Statement::query()
            ->where('created_at', '>=', now()->subMinute())
            ->selectRaw('ROUND(COUNT(*) / 60.0, 2) as rps')
            ->value('rps');

        DatabaseVelocity::create([
            'max_statement_id' => $maxId,
            'rows_per_second' => $rowsPerSecond,
        ]);

        $this->info("Recorded: max_id={$maxId}, rps={$rowsPerSecond}");
    }
}
