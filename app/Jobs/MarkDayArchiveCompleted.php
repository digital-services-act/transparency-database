<?php

namespace App\Jobs;

use App\Models\DayArchive;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class MarkDayArchiveCompleted implements ShouldQueue
{
    use Queueable;
    public int $archive_id;

    public function __construct(int $archive_id)
    {
        $this->archive_id = $archive_id;
    }

    public function handle()
    {
        $day_archive = DayArchive::find($this->archive_id);
        $day_archive->completed_at = Carbon::now();
        $day_archive->save();
    }

}