<?php

namespace App\Jobs;

use App\Models\Statement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StatementCreation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $type;

    /**
     * Create a new job instance.
     */
    public function __construct($type = 'regular')
    {
        $this->type = $type;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {


        Statement::factory()->create();


    }
}
