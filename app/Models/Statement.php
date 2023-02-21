<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Languages;

class Statement extends Model
{
    use HasFactory, Searchable, LogsActivity;

    public const METHOD_FORM = 'FORM';
    public const METHOD_API = 'API';

    public const SOURCE_ARTICLE_16 = 'Article 16';
    public const SOURCE_VOLUNTARY = 'voluntary own-initiative investigation';
    public const SOURCES = [
        Statement::SOURCE_ARTICLE_16,
        Statement::SOURCE_VOLUNTARY];

    public const PAYMENT_STATUS_SUSPENSION = 'suspension';
    public const PAYMENT_STATUS_TERMINATION = 'termination';
    public const PAYMENT_STATUS_OTHER = 'other';
    public const PAYMENT_STATUES = [
        Statement::PAYMENT_STATUS_SUSPENSION,
        Statement::PAYMENT_STATUS_TERMINATION,
        Statement::PAYMENT_STATUS_OTHER
    ];

    public const RESTRICTION_TYPE_REMOVED = 'removed';
    public const RESTRICTION_TYPE_DISABLED = 'disabled';
    public const RESTRICTION_TYPE_DEMOTED = 'demoted';
    public const RESTRICTION_TYPE_OTHER = 'other';
    public const RESTRICTION_TYPES = [
        Statement::RESTRICTION_TYPE_REMOVED,
        Statement::RESTRICTION_TYPE_DISABLED,
        Statement::RESTRICTION_TYPE_DEMOTED,
        Statement::RESTRICTION_TYPE_OTHER
    ];


    public const AUTOMATED_DETECTIONS_YES = 'Yes';
    public const AUTOMATED_DETECTIONS_NO = 'No';
    public const AUTOMATED_DETECTIONS_PARTIAL = 'Partial';
    public const AUTOMATED_DETECTIONS = [
        Statement::AUTOMATED_DETECTIONS_YES,
        Statement::AUTOMATED_DETECTIONS_NO,
        Statement::AUTOMATED_DETECTIONS_PARTIAL,
    ];

    public const REDRESS_INTERNAL_MECHANISM = 'Internal Mechanism';
    public const REDRESS_INTERNAL_OUTOFCOURT = 'Out Of Court Settlement';
    public const REDRESS_INTERNAL_OTHER = 'Other';
    public const REDRESSES = [
        Statement::REDRESS_INTERNAL_MECHANISM,
        Statement::REDRESS_INTERNAL_OUTOFCOURT,
        Statement::REDRESS_INTERNAL_OTHER
    ];

    public const IC_LEGAL_GROUND_MISINFORMATION = 'misinformation';
    public const IC_LEGAL_GROUND_OFFENDING = 'offending content';
    public const IC_LEGAL_GROUND_PERSONAL = 'personal privacy';
    public const IC_LEGAL_GROUND_ABUSIVE = 'abusive';
    public const IC_LEGAL_GROUNDS = [
        Statement::IC_LEGAL_GROUND_MISINFORMATION,
        Statement::IC_LEGAL_GROUND_OFFENDING,
        Statement::IC_LEGAL_GROUND_PERSONAL,
        Statement::IC_LEGAL_GROUND_ABUSIVE
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


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    /**
     * Get the name of the index associated with the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return 'statements_body_fulltext';
    }

    public function toSearchableArray()
    {
        return [
            'title' => $this->title,
            'body' => $this->body
        ];
    }

    public function getLanguageName(): string
    {
        return Languages::getName($this->language);
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
