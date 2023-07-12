<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Symfony\Component\Intl\Countries;

class Statement extends Model
{
    use HasFactory, Searchable, SoftDeletes;


    public const METHOD_FORM = 'FORM';
    public const METHOD_API = 'API';
    public const METHODS = [
        'METHOD_FORM' => self::METHOD_FORM,
        'METHOD_API' => self::METHOD_API
    ];


    public const LABEL_STATEMENT_SOURCE_TYPE = 'Information source';
    public const LABEL_STATEMENT_SOURCE = 'Notifier';
    public const SOURCE_ARTICLE_16 = 'Notice submitted in accordance with Article 16 DSA';
    public const SOURCE_TRUSTED_FLAGGER = 'Notice submitted by a trusted flagger';
    public const SOURCE_VOLUNTARY = 'Own voluntary initiative';
    public const SOURCE_TYPES = [
        'SOURCE_ARTICLE_16' => self::SOURCE_ARTICLE_16,
        'SOURCE_TRUSTED_FLAGGER' => self::SOURCE_TRUSTED_FLAGGER,
        'SOURCE_VOLUNTARY' => self::SOURCE_VOLUNTARY,
    ];


    public const LABEL_STATEMENT_CONTENT_TYPE = 'Content Type';
    public const CONTENT_TYPE_TEXT = 'Text';
    public const CONTENT_TYPE_VIDEO = 'Video';
    public const CONTENT_TYPE_IMAGE = 'Image';
    public const CONTENT_TYPE_OTHER = 'Other';
    public const CONTENT_TYPES = [
        'CONTENT_TYPE_TEXT' => self::CONTENT_TYPE_TEXT,
        'CONTENT_TYPE_VIDEO' => self::CONTENT_TYPE_VIDEO,
        'CONTENT_TYPE_IMAGE' => self::CONTENT_TYPE_IMAGE,
        'CONTENT_TYPE_OTHER' => self::CONTENT_TYPE_OTHER,
    ];

    public const LABEL_STATEMENT_AUTOMATED_DETECTION = 'Was the content detected/identified using automated means?';
    public const AUTOMATED_DETECTION_YES = 'Yes';
    public const AUTOMATED_DETECTION_NO = 'No';
    public const AUTOMATED_DETECTIONS = [
        self::AUTOMATED_DETECTION_YES,
        self::AUTOMATED_DETECTION_NO,
    ];


    public const LABEL_STATEMENT_AUTOMATED_DECISION = 'Was the decision taken using other automated means?';
    public const AUTOMATED_DECISION_YES = 'Yes';
    public const AUTOMATED_DECISION_NO = 'No';
    public const AUTOMATED_DECISIONS = [
        self::AUTOMATED_DECISION_YES,
        self::AUTOMATED_DECISION_NO,
    ];



    public const LABEL_STATEMENT_DECISION_GROUND = 'Ground for Decision';
    public const DECISION_GROUND_ILLEGAL_CONTENT = 'Illegal Content';
    public const DECISION_GROUND_INCOMPATIBLE_CONTENT = 'Content incompatible with terms and conditions';
    public const DECISION_GROUNDS = [
        'DECISION_GROUND_ILLEGAL_CONTENT' => self::DECISION_GROUND_ILLEGAL_CONTENT,
        'DECISION_GROUND_INCOMPATIBLE_CONTENT' => self::DECISION_GROUND_INCOMPATIBLE_CONTENT
    ];


    public const LABEL_STATEMENT_ILLEGAL_CONTENT_GROUND = 'Legal ground relied on';
    public const LABEL_STATEMENT_ILLEGAL_CONTENT_EXPLANATION = 'Explanation of why the content is considered to be illegal on that ground';
    public const LABEL_STATEMENT_INCOMPATIBLE_CONTENT_GROUND = 'Reference to contractual ground';
    public const LABEL_STATEMENT_INCOMPATIBLE_CONTENT_EXPLANATION = 'Explanation of why the content is considered as incompatible on that ground';
    public const LABEL_STATEMENT_INCOMPATIBLE_CONTENT_ILLEGAL = 'Is the content considered as illegal?';
    public const INCOMPATIBLE_CONTENT_ILLEGAL_YES = 'Yes';
    public const INCOMPATIBLE_CONTENT_ILLEGAL_NO = 'No';
    public const INCOMPATIBLE_CONTENT_ILLEGALS = [
        self::INCOMPATIBLE_CONTENT_ILLEGAL_YES,
        self::INCOMPATIBLE_CONTENT_ILLEGAL_NO,
    ];

    public const ILLEGAL_CONTENT_FIELDS = [
        self::LABEL_STATEMENT_ILLEGAL_CONTENT_GROUND,
        self::LABEL_STATEMENT_ILLEGAL_CONTENT_EXPLANATION,
    ];

    public const INCOMPATIBLE_CONTENT_FIELDS = [
        self::LABEL_STATEMENT_INCOMPATIBLE_CONTENT_GROUND,
        self::LABEL_STATEMENT_INCOMPATIBLE_CONTENT_EXPLANATION,
        self::LABEL_STATEMENT_INCOMPATIBLE_CONTENT_ILLEGAL,
    ];

    public const LABEL_STATEMENT_DECISION_VISIBILITY = 'Visibility restriction of specific items of information provided by the recipient of the service';
    public const DECISION_VISIBILITY_CONTENT_REMOVED = 'Removal of content';
    public const DECISION_VISIBILITY_CONTENT_DISABLED = 'Disabling access to content';
    public const DECISION_VISIBILITY_CONTENT_DEMOTED = 'Demotion of content';
    public const DECISION_VISIBILITY_OTHER = 'Other restriction (please specify)';
    public const DECISION_VISIBILITIES = [
        'DECISION_VISIBILITY_CONTENT_REMOVED' => self::DECISION_VISIBILITY_CONTENT_REMOVED,
        'DECISION_VISIBILITY_CONTENT_DISABLED' => self::DECISION_VISIBILITY_CONTENT_DISABLED,
        'DECISION_VISIBILITY_CONTENT_DEMOTED' => self::DECISION_VISIBILITY_CONTENT_DEMOTED,
        'DECISION_VISIBILITY_OTHER' => self::DECISION_VISIBILITY_OTHER
    ];

    public const LABEL_STATEMENT_DECISION_MONETARY = 'Monetary payments suspension, termination or other restriction';
    public const DECISION_MONETARY_SUSPENSION = 'Suspension of monetary payments';
    public const DECISION_MONETARY_TERMINATION = 'Termination of monetary payments';
    public const DECISION_MONETARY_OTHER = 'Other restriction (please specify)';

    public const DECISION_MONETARIES = [
        'DECISION_MONETARY_SUSPENSION' => self::DECISION_MONETARY_SUSPENSION,
        'DECISION_MONETARY_TERMINATION' => self::DECISION_MONETARY_TERMINATION,
        'DECISION_MONETARY_OTHER' => self::DECISION_MONETARY_OTHER
    ];

    public const LABEL_STATEMENT_DECISION_PROVISION = 'Suspension or termination of the provision of the service';
    public const DECISION_PROVISION_PARTIAL_SUSPENSION = 'Partial suspension of the provision of the service';
    public const DECISION_PROVISION_TOTAL_SUSPENSION = 'Total suspension of the provision of the service';
    public const DECISION_PROVISION_PARTIAL_TERMINATION = 'Partial termination of the provision of the service';
    public const DECISION_PROVISION_TOTAL_TERMINATION = 'Total termination of the provision of the service';
    public const DECISION_PROVISIONS = [
        'DECISION_PROVISION_PARTIAL_SUSPENSION' => self::DECISION_PROVISION_PARTIAL_SUSPENSION,
        'DECISION_PROVISION_TOTAL_SUSPENSION' => self::DECISION_PROVISION_TOTAL_SUSPENSION,
        'DECISION_PROVISION_PARTIAL_TERMINATION' => self::DECISION_PROVISION_PARTIAL_TERMINATION,
        'DECISION_PROVISION_TOTAL_TERMINATION' => self::DECISION_PROVISION_TOTAL_TERMINATION,
    ];

    public const LABEL_STATEMENT_DECISION_ACCOUNT = 'Suspension or termination of the recipient of the service\'s account.';
    public const DECISION_ACCOUNT_SUSPENDED = 'Suspension of the account';
    public const DECISION_ACCOUNT_TERMINATED = 'Termination of the account';

    public const DECISION_ACCOUNTS = [
        'DECISION_ACCOUNT_SUSPENDED' => self::DECISION_ACCOUNT_SUSPENDED,
        'DECISION_ACCOUNT_TERMINATED' => self::DECISION_ACCOUNT_TERMINATED
    ];

    public const LABEL_STATEMENT_COUNTRY_LIST = 'Territorial scope of the decision';
    public const EUROPEAN_COUNTRY_CODES = [
        'AT',
        'BE',
        'BG',
        'CY',
        'CZ',
        'DE',
        'DK',
        'EE',
        'ES',
        'FI',
        'FR',
        'GR',
        'HR',
        'HU',
        'IE',
        'IT',
        'LT',
        'LU',
        'LV',
        'MT',
        'NL',
        'PL',
        'PT',
        'RO',
        'SE',
        'SI',
        'SK'
    ];

    public const LABEL_STATEMENT_CATEGORY = 'Category';
    public const STATEMENT_CATEGORY_PIRACY = 'Pirated content (eg. music, films, books)';
    public const STATEMENT_CATEGORY_DISCRIMINATION = 'Discrimination and hate speech (e.g. race, gender identity, sexual orientation, religion, disability)';
    public const STATEMENT_CATEGORY_COUNTERFEIT = 'Counterfeit goods (e.g. fake perfume, fake designer brands)';
    public const STATEMENT_CATEGORY_FRAUD = 'Scams, frauds, subscription traps or other illegal commercial practices';
    public const STATEMENT_CATEGORY_TERRORISM = 'Terrorist content (e.g. extremists, hate groups)';
    public const STATEMENT_CATEGORY_CHILD_SAFETY = 'Child safety (e.g. child nudity, sexual abuse, unsolicited contact with minors)';
    public const STATEMENT_CATEGORY_NON_CONSENT = 'Non-consensual nudity (e.g. hidden camera, deepfake, revenge porn, upskirts)';
    public const STATEMENT_CATEGORY_MISINFORMATION = 'Harmful False or Deceptive Information (e.g. denying tragic events, synthetic media, false context)';
    public const STATEMENT_CATEGORY_VIOLATION_TOS = 'Violation of the terms of service of the Internet hosting service (e.g. spam, platform manipulation)';
    public const STATEMENT_CATEGORY_UNCATEGORISED = 'Uncategorised';
    public const STATEMENT_CATEGORIES = [
        'STATEMENT_CATEGORY_PIRACY' => self::STATEMENT_CATEGORY_PIRACY,
        'STATEMENT_CATEGORY_DISCRIMINATION' => self::STATEMENT_CATEGORY_DISCRIMINATION,
        'STATEMENT_CATEGORY_COUNTERFEIT' => self::STATEMENT_CATEGORY_COUNTERFEIT,
        'STATEMENT_CATEGORY_FRAUD' => self::STATEMENT_CATEGORY_FRAUD,
        'STATEMENT_CATEGORY_TERRORISM' => self::STATEMENT_CATEGORY_TERRORISM,
        'STATEMENT_CATEGORY_CHILD_SAFETY' => self::STATEMENT_CATEGORY_CHILD_SAFETY,
        'STATEMENT_CATEGORY_NON_CONSENT' => self::STATEMENT_CATEGORY_NON_CONSENT,
        'STATEMENT_CATEGORY_MISINFORMATION' => self::STATEMENT_CATEGORY_MISINFORMATION,
        'STATEMENT_CATEGORY_VIOLATION_TOS' => self::STATEMENT_CATEGORY_VIOLATION_TOS,
        'STATEMENT_CATEGORY_UNCATEGORISED' => self::STATEMENT_CATEGORY_UNCATEGORISED
    ];


    public const LABEL_STATEMENT_URL = 'URL/Hyperlink';
    public const LABEL_STATEMENT_DECISION_FACTS = 'Facts and circumstances relied on in taking the decision';
    public const LABEL_STATEMENT_START_DATE = 'Start date of the decision';
    public const LABEL_STATEMENT_END_DATE = 'End date of the decision';
    public const LABEL_STATEMENT_FORM_OTHER = 'Other';


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
        'uuid' => 'string',
        'start_date' => 'datetime:Y-m-d H:i:s',
        'end_date' => 'datetime:Y-m-d H:i:s',
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
        return 'statement_' . env('APP_ENV');
    }

    public function toSearchableArray()
    {
        return [
            'decision_visibility' => $this->decision_visibility,
            'decision_monetary' => $this->decision_monetary,
            'decision_provision' => $this->decision_provision,
            'decision_account' => $this->decision_account,
            'decision_ground' => $this->decision_ground,
            'content_type' => $this->content_type,
            'content_type_other' => $this->content_type_other,
            'illegal_content_legal_ground' => $this->illegal_content_legal_ground,
            'illegal_content_explanation' => $this->illegal_content_explanation,
            'incompatible_content_ground' => $this->incompatible_content_ground,
            'incompatible_content_explanation' => $this->incompatible_content_explanation,
            'source_type' => $this->source_type,
            'source' => $this->source,
            'decision_facts' => $this->decision_facts,
            'automated_detection' => $this->automated_detection === self::AUTOMATED_DECISION_YES,
            'automated_decision' => $this->automated_decision === self::AUTOMATED_DECISION_YES,
            'category' => $this->category,
            'platform_id' => $this->platform_id,
            'url' => $this->url,
            'created_at' => $this->created_at
        ];
    }

    /**
     * Get the value used to index the model.
     */
    public function getScoutKey(): mixed
    {
        return $this->id;
    }

    /**
     * Get the key name used to index the model.
     */
    public function getScoutKeyName(): mixed
    {
        return 'id';
    }

    /**
     * @return array
     */
    public function getCountriesListNames(): array
    {
        if(count($this->countries_list) == 27) return ['European Union'];
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

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function platform(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Platform::class, 'id', 'platform_id');
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
