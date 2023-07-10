<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Platform extends Model
{
    use HasFactory, SoftDeletes;

    public const LABEL_DSA_TEAM = 'DSA Team';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id'
    ];


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'platform_id', 'id');
    }

    public function statements()
    {
        return $this->hasMany(Statement::class, 'platform_id', 'id');
    }
}
