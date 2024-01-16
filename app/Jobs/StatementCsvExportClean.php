<?php

namespace App\Jobs;

use App\Models\Platform;
use App\Services\DayArchiveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StatementCsvExportClean implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $date;

    public function __construct(string $date)
    {
        $this->date     = $date;
    }

    public function handle(): void
    {
        $path = Storage::path('');
        shell_exec('rm ' . $path . '*' . $this->date . '*');
    }
}