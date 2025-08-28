<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @codeCoverageIgnore
 */
class ApiLog extends Model
{
    protected $fillable = [
        'endpoint',
        'method',
        'platform_id',
        'request_data',
        'response_data',
        'response_code',
        'error_message',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
    ];

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }
}
