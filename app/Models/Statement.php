<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

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
    public const CONTENT_TYPE_APP = 'App';
    public const CONTENT_TYPE_AUDIO = 'Audio';
    public const CONTENT_TYPE_PRODUCT = 'Product';
    public const CONTENT_TYPE_SYNTHETIC_MEDIA = 'Synthetic Media';
    public const CONTENT_TYPE_TEXT = 'Text';
    public const CONTENT_TYPE_VIDEO = 'Video';
    public const CONTENT_TYPE_IMAGE = 'Image';
    public const CONTENT_TYPE_OTHER = 'Other';
    public const CONTENT_TYPES = [
        'CONTENT_TYPE_APP' => self::CONTENT_TYPE_APP,
        'CONTENT_TYPE_AUDIO' => self::CONTENT_TYPE_AUDIO,
        'CONTENT_TYPE_IMAGE' => self::CONTENT_TYPE_IMAGE,
        'CONTENT_TYPE_PRODUCT' => self::CONTENT_TYPE_PRODUCT,
        'CONTENT_TYPE_SYNTHETIC_MEDIA' => self::CONTENT_TYPE_SYNTHETIC_MEDIA,
        'CONTENT_TYPE_TEXT' => self::CONTENT_TYPE_TEXT,
        'CONTENT_TYPE_VIDEO' => self::CONTENT_TYPE_VIDEO,
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

    public const LABEL_STATEMENT_DECISION_ACCOUNT = 'Suspension or termination of the recipient of the service\'s account';
    public const DECISION_ACCOUNT_SUSPENDED = 'Suspension of the account';
    public const DECISION_ACCOUNT_TERMINATED = 'Termination of the account';

    public const DECISION_ACCOUNTS = [
        'DECISION_ACCOUNT_SUSPENDED' => self::DECISION_ACCOUNT_SUSPENDED,
        'DECISION_ACCOUNT_TERMINATED' => self::DECISION_ACCOUNT_TERMINATED
    ];

    public const LABEL_STATEMENT_TERRITORIAL_SCOPE = 'Territorial scope of the decision';


    public const LABEL_STATEMENT_CATEGORY = 'Category';

    public const STATEMENT_CATEGORY_ANIMAL_WELFARE = 'Animal welfare';
    public const STATEMENT_CATEGORY_DATA_PROTECTION_AND_PRIVACY_VIOLATIONS = 'Data protection and privacy violations';
    public const STATEMENT_CATEGORY_ILLEGAL_OR_HARMFUL_SPEECH = 'Illegal or harmful speech';
    public const STATEMENT_CATEGORY_INTELLECTUAL_PROPERTY_INFRINGEMENTS = 'Intellectual property infringements';
    public const STATEMENT_CATEGORY_NEGATIVE_EFFECTS_ON_CIVIC_DISCOURSE_OR_ELECTIONS = 'Negative effects on civic discourse or elections';
    public const STATEMENT_CATEGORY_NON_CONSENSUAL_BEHAVIOUR = 'Non-consensual behaviour';
    public const STATEMENT_CATEGORY_PORNOGRAPHY_OR_SEXUALIZED_CONTENT = 'Pornography or sexualized content';
    public const STATEMENT_CATEGORY_PROTECTION_OF_MINORS = 'Protection of minors';
    public const STATEMENT_CATEGORY_RISK_FOR_PUBLIC_SECURITY = 'Risk for public security';
    public const STATEMENT_CATEGORY_SCAMS_AND_FRAUD = 'Scams and/or fraud';
    public const STATEMENT_CATEGORY_SELF_HARM = 'Self-harm';
    public const STATEMENT_CATEGORY_SCOPE_OF_PLATFORM_SERVICE = 'Scope of platform service';
    public const STATEMENT_CATEGORY_UNSAFE_AND_ILLEGAL_PRODUCTS_OR_SERVICES = 'Unsafe and/or illegal products or services';
    public const STATEMENT_CATEGORY_VIOLENCE = 'Violence';

    public const STATEMENT_CATEGORY_UNCATEGORISED = 'Uncategorised';

    public const STATEMENT_CATEGORIES = [
        'STATEMENT_CATEGORY_ANIMAL_WELFARE' => self::STATEMENT_CATEGORY_ANIMAL_WELFARE,
        'STATEMENT_CATEGORY_DATA_PROTECTION_AND_PRIVACY_VIOLATIONS' => self::STATEMENT_CATEGORY_DATA_PROTECTION_AND_PRIVACY_VIOLATIONS,
        'STATEMENT_CATEGORY_ILLEGAL_OR_HARMFUL_SPEECH' => self::STATEMENT_CATEGORY_ILLEGAL_OR_HARMFUL_SPEECH,
        'STATEMENT_CATEGORY_INTELLECTUAL_PROPERTY_INFRINGEMENTS' => self::STATEMENT_CATEGORY_INTELLECTUAL_PROPERTY_INFRINGEMENTS,
        'STATEMENT_CATEGORY_NEGATIVE_EFFECTS_ON_CIVIC_DISCOURSE_OR_ELECTIONS' => self::STATEMENT_CATEGORY_NEGATIVE_EFFECTS_ON_CIVIC_DISCOURSE_OR_ELECTIONS,
        'STATEMENT_CATEGORY_NON_CONSENSUAL_BEHAVIOUR' => self::STATEMENT_CATEGORY_NON_CONSENSUAL_BEHAVIOUR,
        'STATEMENT_CATEGORY_PORNOGRAPHY_OR_SEXUALIZED_CONTENT' => self::STATEMENT_CATEGORY_PORNOGRAPHY_OR_SEXUALIZED_CONTENT,
        'STATEMENT_CATEGORY_PROTECTION_OF_MINORS' => self::STATEMENT_CATEGORY_PROTECTION_OF_MINORS,
        'STATEMENT_CATEGORY_RISK_FOR_PUBLIC_SECURITY' => self::STATEMENT_CATEGORY_RISK_FOR_PUBLIC_SECURITY,
        'STATEMENT_CATEGORY_SCAMS_AND_FRAUD' => self::STATEMENT_CATEGORY_SCAMS_AND_FRAUD,
        'STATEMENT_CATEGORY_SELF_HARM' => self::STATEMENT_CATEGORY_SELF_HARM,
        'STATEMENT_CATEGORY_SCOPE_OF_PLATFORM_SERVICE' => self::STATEMENT_CATEGORY_SCOPE_OF_PLATFORM_SERVICE,
        'STATEMENT_CATEGORY_UNSAFE_AND_ILLEGAL_PRODUCTS_OR_SERVICES' => self::STATEMENT_CATEGORY_UNSAFE_AND_ILLEGAL_PRODUCTS_OR_SERVICES,
        'STATEMENT_CATEGORY_VIOLENCE' => self::STATEMENT_CATEGORY_VIOLENCE
    ];


    public const LABEL_STATEMENT_URL = 'URL/Hyperlink';
    public const LABEL_STATEMENT_PUID = 'Platform Unique Identifier';
    public const LABEL_STATEMENT_DECISION_FACTS = 'Facts and circumstances relied on in taking the decision';
    public const LABEL_STATEMENT_APPLICATION_DATE = 'Application date of the decision';
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
        'application_date' => 'datetime:d-m-Y',
        'end_date' => 'datetime:d-m-Y',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'territorial_scope' => 'array',
        'content_type' => 'array',
    ];

    protected $hidden = [
        'deleted_at',
        'updated_at',
        'method',
        'user_id',
        'id',
        'platform_id',
        'platform',
        'puid'
    ];

    protected $appends = [
        'territorial_scope',
        'content_type',
        'platform_name',
        'permalink',
        'self'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($statement) {
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
            'decision_visibility_other' => $this->decision_visibilit_other,
            'decision_monetary' => $this->decision_monetary,
            'decision_monetary_other' => $this->decision_monetary_other,
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
            'created_at' => $this->created_at,
            'uuid' => $this->uuid,
            'puid' => $this->puid,
            'territorial_scope' => $this->territorial_scope
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
        return route('api.v' . config('app.api_latest') . '.statement.show', [$this]);
    }

    /**
     * @return string
     */
    public function getPlatformNameAttribute(): string
    {
        return $this->platform->name;
    }

    public function getTerritorialScopeAttribute(): array
    {
        return $this->getRawKeys('territorial_scope');
    }

    public function getContentTypeAttribute($value)
    {
        return $this->getRawKeys('content_type');
    }


    // Function to convert enum keys to their corresponding values
    public static function getEnumValues(array $keys): array
    {
        $enumValues = [];

        foreach ($keys as $key) {
            // Use constant() to get the value of the constant by its name
            $value = constant('App\Models\Statement::' . $key);

            if ($value !== null) {
                $enumValues[] = $value;
            }
        }

        sort($enumValues);

        return $enumValues;
    }

    /**
     * @return array|mixed
     */
    public function getRawKeys($key): mixed
    {
        $out = null;

        // Catch potential bad json here.
        try {
            $out = json_decode($this->getRawOriginal($key));
        } catch (Exception $e) {
            $out = [];
        }


        if (is_array($out)) {
            sort($out);
        } else {
            $out = [];
        }

        return $out;
    }

}
