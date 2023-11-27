<?php

namespace App\Console\Commands;

use App\Jobs\VerifyIndex;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class VerifyStatementIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:verify-index {min=default} {max=default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify and fix the Opensearch Statements Index';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {

        $chunk = 10000;

        $min = $this->argument('min') === 'default' ? DB::table('statements')->selectRaw('MIN(id) AS min')->first()->min : (int)$this->argument('min');
        $max = $this->argument('max') === 'default' ? DB::table('statements')->selectRaw('MAX(id) AS max')->first()->max : (int)$this->argument('max');

        VerifyIndex::dispatch($max, $chunk, $min);
    }
}
