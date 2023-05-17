<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Symfony\Component\Intl\Countries;

class Statement extends Model
{
    use HasFactory, Searchable, LogsActivity, SoftDeletes;

    public const METHOD_FORM = 'FORM';
    public const METHOD_API = 'API';
    public const METHOD_EDELIVERY = 'EDELIVERY';
    public const METHODS = [
        'METHOD_FORM' => Statement::METHOD_FORM,
        'METHOD_API' => Statement::METHOD_API,
        'METHOD_EDELIVERY' => Statement::METHOD_EDELIVERY
    ];

    public const SOURCE_ARTICLE_16 = 'Notice submitted in accordance with Article 16 DSA';
    public const SOURCE_VOLUNTARY = 'Own voluntary initiative';
    //public const SOURCE_OTHER = 'Other';

    public const SOURCES = [
        'SOURCE_ARTICLE_16' => Statement::SOURCE_ARTICLE_16,
        'SOURCE_VOLUNTARY' => Statement::SOURCE_VOLUNTARY,
        //'SOURCE_OTHER' => Statement::SOURCE_OTHER
    ];

    public const AUTOMATED_DETECTIONS_YES = 'Yes';
    public const AUTOMATED_DETECTIONS_NO = 'No';
    public const AUTOMATED_DETECTIONS = [
        Statement::AUTOMATED_DETECTIONS_YES,
        Statement::AUTOMATED_DETECTIONS_NO,
    ];


    public const AUTOMATED_DECISIONS_YES = 'Yes';
    public const AUTOMATED_DECISIONS_NO = 'No';
    public const AUTOMATED_DECISIONS = [
        Statement::AUTOMATED_DECISIONS_YES,
        Statement::AUTOMATED_DECISIONS_NO,
    ];

    public const AUTOMATED_TAKEDOWN_YES = 'Yes';
    public const AUTOMATED_TAKEDOWN_NO = 'No';
    public const AUTOMATED_TAKEDOWNS = [
        Statement::AUTOMATED_TAKEDOWN_YES,
        Statement::AUTOMATED_TAKEDOWN_NO,
    ];


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

    public const DECISION_GROUND_ILLEGAL_CONTENT = 'Illegal Content';
    public const DECISION_GROUND_INCOMPATIBLE_CONTENT = 'Content incompatible with terms and conditions';
    public const DECISION_GROUNDS = [
        'ILLEGAL_CONTENT' => Statement::DECISION_GROUND_ILLEGAL_CONTENT,
        'INCOMPATIBLE_CONTENT' => Statement::DECISION_GROUND_INCOMPATIBLE_CONTENT
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

    public const DECISION_ALL = 'Any restrictions on visibility, such as removing, disabling, or demoting content provided by the service recipient.';
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
        'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK'
    ];

    public const SOR_CATEGORY_PIRACY = 'Pirated content (eg. music, films, books)';
    public const SOR_CATEGORY_DISCRIMINATION = 'Discrimination and hate speech (race, gender identity, sexual orientation, religion, disability)';
    public const SOR_CATEGORY_COUNTERFEIT = 'Counterfeit goods (e.g. fake perfume, fake designer brands)';
    public const SOR_CATEGORY_FRAUD = 'Scams, frauds, subscription traps or other illegal commercial practices';
    public const SOR_CATEGORY_TERRORISM = 'Terrorist content (extremists, hate groups)';
    public const SOR_CATEGORY_CHILD_SAFETY = 'Child safety (child nudity, sexual abuse, unsolicited contact with minors)';
    public const SOR_CATEGORY_NON_CONSENT = 'Non-consensual nudity (hidden camera, deepfake, revenge porn, upskirts)';
    public const SOR_CATEGORY_MISINFORMATION = 'Harmful False or Deceptive Information (denying tragic events, synthetic media, false context)';
    public const SOR_CATEGORY_VIOLATION_TOS = 'Violation of the terms of service of the Internet hosting service (spam, platform manipulation)';
    public const SOR_CATEGORIES = [
        'PIRACY' => Statement::SOR_CATEGORY_PIRACY,
        'DISCRIMINATION' => Statement::SOR_CATEGORY_DISCRIMINATION,
        'COUNTERFEIT' => Statement::SOR_CATEGORY_COUNTERFEIT,
        'FRAUD' => Statement::SOR_CATEGORY_FRAUD,
        'TERRORISM' => Statement::SOR_CATEGORY_TERRORISM,
        'CHILD_SAFETY' => Statement::SOR_CATEGORY_CHILD_SAFETY,
        'NON_CONSENT' => Statement::SOR_CATEGORY_NON_CONSENT,
        'MISINFORMATION' => Statement::SOR_CATEGORY_MISINFORMATION,
        'VIOLATION_TOS' => Statement::SOR_CATEGORY_VIOLATION_TOS,
    ];





    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'uuid'
    ];

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
        'created_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s',
        'update_at' => 'datetime:Y-m-d H:i:s',
        'countries_list' => 'array'
    ];

    protected $hidden = [
        'deleted_at',
        'updated_at',
        'method',
        'user_id',
        'id'
    ];

    protected $appends = [
        'permalink',
        'self'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function($statement){
            $statement->uuid = Str::uuid();
        });
    }

    /**
     * Get the name of the index associated with the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return 'statements_index';
    }

    public function toSearchableArray()
    {
        return [
            'illegal_content_explanation' => $this->illegal_content_explanation,
            'incompatible_content_explanation' => $this->incompatible_content_explanation,
            'source_identity' => $this->source_identity,
            'source_own_voluntary' => $this->source_own_voluntary,
        ];
    }

    /**
     * Get the value used to index the model.
     */
    public function getScoutKey(): mixed
    {
        return $this->uuid;
    }

    /**
     * Get the key name used to index the model.
     */
    public function getScoutKeyName(): mixed
    {
        return 'uuid';
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

    public function platform()
    {
        return $this->hasOneThrough(Platform::class, User::class, 'id', 'id', 'user_id', 'platform_id');
    }

    /**
     * @return string
     */
    public function getPermalinkAttribute(): string
    {
        return route('statement.show', [$this]);
    }

    /**
     * @return string
     */
    public function getSelfAttribute(): string
    {
        return route('api.v'.config('app.api_latest').'.statement.show', [$this]);
    }

    public function getCreatedAtAttribute($date)
    {
        return Carbon::parse($date)->setTimezone('Europe/Brussels');
    }
}
