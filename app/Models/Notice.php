<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'body',
        'language',
        'date_sent',
        'date_enacted',
        'date_abolished',
        'countries_list',
        'source',
        'payment_status',
        'restriction_type',
        'restriction_type_other',
        'automated_detection',
        'automated_detection_more',
        'illegal_content_legal_ground',
        'illegal_content_explanation',
        'toc_contractual_ground',
        'toc_explanation',
        'redress',
        'redress_more',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'date_sent' => 'timestamp',
        'date_enacted' => 'timestamp',
        'date_abolished' => 'timestamp',
    ];

    public function entities()
    {
        return $this->belongsToMany(Entity::class);
    }
}
