<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'kind',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'zip',
        'phone',
        'email',
        'url',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
    ];

    public function notices()
    {
        return $this->belongsToMany(Notice::class)->withPivot('role');
    }
}
