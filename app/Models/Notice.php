<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Attributes\SearchUsingPrefix;
use Laravel\Scout\Searchable;
use Symfony\Component\Intl\Countries;

class Notice extends Model
{
    use HasFactory, Searchable;

    public const METHOD_FORM = 'FORM';
    public const METHOD_API = 'API';

    public const SOURCE_ARTICLE_16 = 'Article 16';
    public const SOURCE_VOLUNTARY = 'voluntary own-initiative investigation';
    public const SOURCES = [
        Notice::SOURCE_ARTICLE_16,
        Notice::SOURCE_VOLUNTARY];

    public const PAYMENT_STATUS_SUSPENSION = 'suspension';
    public const PAYMENT_STATUS_TERMINATION = 'termination';
    public const PAYMENT_STATUS_OTHER = 'other';
    public const PAYMENT_STATUES = [
        Notice::PAYMENT_STATUS_SUSPENSION,
        Notice::PAYMENT_STATUS_TERMINATION,
        Notice::PAYMENT_STATUS_OTHER
    ];

    public const RESTRICTION_TYPE_REMOVED = 'removed';
    public const RESTRICTION_TYPE_DISABLED = 'disabled';
    public const RESTRICTION_TYPE_DEMOTED = 'demoted';
    public const RESTRICTION_TYPE_OTHER = 'other';
    public const RESTRICTION_TYPES = [
        Notice::RESTRICTION_TYPE_REMOVED,
        Notice::RESTRICTION_TYPE_DISABLED,
        Notice::RESTRICTION_TYPE_DEMOTED,
        Notice::RESTRICTION_TYPE_OTHER
    ];


    public const AUTOMATED_DETECTIONS_YES = 'Yes';
    public const AUTOMATED_DETECTIONS_NO = 'No';
    public const AUTOMATED_DETECTIONS_PARTIAL = 'Partial';
    public const AUTOMATED_DETECTIONS = [
        Notice::AUTOMATED_DETECTIONS_YES,
        Notice::AUTOMATED_DETECTIONS_NO,
        Notice::AUTOMATED_DETECTIONS_PARTIAL,
    ];

    public const REDRESS_INTERNAL_MECHANISM = 'Internal Mechanism';
    public const REDRESS_INTERNAL_OUTOFCOURT = 'Out Of Court Settlement';
    public const REDRESS_INTERNAL_OTHER = 'Other';
    public const REDRESSES = [
        Notice::REDRESS_INTERNAL_MECHANISM,
        Notice::REDRESS_INTERNAL_OUTOFCOURT,
        Notice::REDRESS_INTERNAL_OTHER
    ];


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'date_sent' => 'datetime:Y-m-d H:i:s',
        'date_enacted' => 'datetime:Y-m-d H:i:s',
        'date_abolished' => 'datetime:Y-m-d H:i:s',
        'countries_list' => 'array'
    ];

    /**
     * Get the name of the index associated with the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return 'notices_index';
    }

    public function toSearchableArray()
    {
        return [
            'title' => $this->title,
            'body' => $this->body
        ];
    }

    /**
     * @return array
     */
    public function getCountriesListNames(): array
    {
        if ($this->countries_list) {
            return array_map(function($iso){
                return Countries::getName($iso);
            }, $this->countries_list);
        }
        return [];
    }

    public function entities()
    {
        return $this->belongsToMany(Entity::class)->withPivot('role');
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
