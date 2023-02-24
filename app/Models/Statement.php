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

    public const SOURCE_ARTICLE_16 = 'Notice submitted in accordance with Article 16 DSA';
    public const SOURCE_VOLUNTARY = 'Own voluntary initiative';
    public const SOURCE_OTHER = 'Other';
    public const SOURCES = [
        'SOURCE_ARTICLE_16' => Statement::SOURCE_ARTICLE_16,
        'SOURCE_VOLUNTARY' => Statement::SOURCE_VOLUNTARY,
        'SOURCE_OTHER' => Statement::SOURCE_OTHER
    ];

    public const AUTOMATED_DETECTIONS_YES = 'Yes';
    public const AUTOMATED_DETECTIONS_NO = 'No';
    public const AUTOMATED_DETECTIONS = [
        Statement::AUTOMATED_DETECTIONS_YES,
        Statement::AUTOMATED_DETECTIONS_NO,
    ];
//
    public const REDRESS_INTERNAL_MECHANISM = 'Internal complaint-handling mechanism';
    public const REDRESS_OUT_OF_COURT = 'Out-of-court dispute settlement';
    public const REDRESS_JUDICIAL = 'Judicial redress';
    public const REDRESS_OTHER = 'Other';
    public const REDRESSES = [
        'REDRESS_INTERNAL_MECHANISM' => Statement::REDRESS_INTERNAL_MECHANISM,
        'REDRESS_OUT_OF_COURT' => Statement::REDRESS_OUT_OF_COURT,
        'REDRESS_JUDICIAL' => Statement::REDRESS_JUDICIAL,
        'REDRESS_OTHER' => Statement::REDRESS_OTHER
    ];
//
//    public const IC_LEGAL_GROUND_MISINFORMATION = 'misinformation';
//    public const IC_LEGAL_GROUND_OFFENDING = 'offending content';
//    public const IC_LEGAL_GROUND_PERSONAL = 'personal privacy';
//    public const IC_LEGAL_GROUND_ABUSIVE = 'abusive';
//    public const IC_LEGAL_GROUNDS = [
//        Statement::IC_LEGAL_GROUND_MISINFORMATION,
//        Statement::IC_LEGAL_GROUND_OFFENDING,
//        Statement::IC_LEGAL_GROUND_PERSONAL,
//        Statement::IC_LEGAL_GROUND_ABUSIVE
//    ];

    public const DECISION_GROUNDS = [
        'ILLEGAL_CONTENT' => 'Illegal Content',
        'INCOMPATIBLE_CONTENT' => 'Content incompatible with terms and conditions'
    ];


    public const ILLEGAL_CONTENT_GROUND = 'Legal ground relied on';
    public const ILLEGAL_CONTENT_EXPLANATION = 'Explanation of why the content is considered to be illegal on that ground';
    public const INCOMPATIBLE_CONTENT_GROUND = 'Reference to contractual ground';
    public const INCOMPATIBLE_CONTENT_EXPLANATION = 'Explanation of why the content is considered as incompatible on that ground';

    public const ILLEGAL_CONTENT_FIELDS = [
        Statement::ILLEGAL_CONTENT_GROUND,
        Statement::ILLEGAL_CONTENT_EXPLANATION,
    ];

    public const INCOMPATIBLE_CONTENT_FIELDS = [
        Statement::INCOMPATIBLE_CONTENT_GROUND,
        Statement::INCOMPATIBLE_CONTENT_EXPLANATION,
    ];

    public const DECISION_ALL = 'any restrictions of the visibility of specific items of information provided by the recipient of the service, including removal of content, disabling access to content, or demoting content';
    public const DECISION_MONETARY = 'suspension, termination or other restriction of monetary payments';
    public const DECISION_PROVISION = 'suspension or termination of the provision of the service in whole or in part';
    public const DECISION_TERMINATION = 'suspension or termination of the recipient of the service\'s account';
    public const DECISIONS = [
        'DECISION_ALL' => Statement::DECISION_ALL,
        'DECISION_MONETARY' => Statement::DECISION_MONETARY,
        'DECISION_PROVISION' => Statement::DECISION_PROVISION,
        'DECISION_TERMINATION' => Statement::DECISION_TERMINATION
    ];


    public const EUROPEAN_COUNTRY_CODES = [
        'AT', 'BE', 'BG', 'HR', 'CY', 'FR', 'CZ', 'DK', 'EE', 'FI', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO'
        , 'SK', 'SI', 'ES', 'SE', 'GB'
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
            return array_map(function ($iso) {
                return Countries::getName($iso);
            }, $this->countries_list);
        }
        return [];
    }

    public function entities()
    {
        return $this->belongsToMany(Entity::class)->withPivot('role');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
