<?php

namespace App\Models;

use Database\Factories\DownloadEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DownloadEvent extends Model
{
    /** @use HasFactory<DownloadEventFactory> */
    use HasFactory;

    protected $fillable = [
        'day_archive_id',
        'platform_id',
        'archive_date',
        'download_kind',
        'file_type',
        'filename',
        'route_name',
        'session_hash',
    ];

    protected $casts = [
        'archive_date' => 'date:Y-m-d',
    ];

    public function dayArchive(): BelongsTo
    {
        return $this->belongsTo(DayArchive::class);
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }
}
