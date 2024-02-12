<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invitation extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['email', 'platform_id', 'completed_at'];

    public function platform(): HasOne
    {
        return $this->hasOne(Platform::class, 'id', 'platform_id');
    }

}
