<?php

namespace App\Jobs;

use App\Models\DayArchive;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class MarkDayArchiveCompleted implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $archive_id)
    {
    }

    public function handle(): void
    {
        $day_archive = DayArchive::find($this->archive_id);
        $day_archive->completed_at = Carbon::now();
        $day_archive->save();
    }

}