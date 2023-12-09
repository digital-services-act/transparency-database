<?php

namespace App\Models;

use Exception;
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
    public const METHOD_API_MULTI = 'API_MULTI';
    public const METHODS = [
        'METHOD_FORM' => self::METHOD_FORM,
        'METHOD_API' => self::METHOD_API,
        'METHOD_API_MULTI' => self::METHOD_API_MULTI
    ];

    public const LABEL_STATEMENT_ACCOUNT_TYPE = "Type of Account";
    public const ACCOUNT_TYPE_BUSINESS = "Business";
    public const ACCOUNT_TYPE_PRIVATE = "Private";
    public const ACCOUNT_TYPES = [
        'ACCOUNT_TYPE_BUSINESS' => self::ACCOUNT_TYPE_BUSINESS,
        'ACCOUNT_TYPE_PRIVATE' => self::ACCOUNT_TYPE_PRIVATE
    ];


    public const LABEL_STATEMENT_SOURCE_TYPE = 'Information source';
    public const LABEL_STATEMENT_SOURCE_IDENTITY = 'Source identity';
    public const SOURCE_ARTICLE_16 = 'Notice submitted in accordance with Article 16 DSA';
    public const SOURCE_TRUSTED_FLAGGER = 'Notice submitted by a trusted flagger';
    public const SOURCE_VOLUNTARY = 'Own voluntary initiative';
    public const SOURCE_TYPE_OTHER_NOTIFICATION = 'Other type of notification';
    public const SOURCE_TYPES = [
        'SOURCE_ARTICLE_16' => self::SOURCE_ARTICLE_16,
        'SOURCE_TRUSTED_FLAGGER' => self::SOURCE_TRUSTED_FLAGGER,
        'SOURCE_TYPE_OTHER_NOTIFICATION' => self::SOURCE_TYPE_OTHER_NOTIFICATION,
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
    public const AUTOMATED_DECISION_FULLY = 'Fully automated';
    public const AUTOMATED_DECISION_PARTIALLY = 'Partially automated';
    public const AUTOMATED_DECISION_NOT_AUTOMATED = 'Not Automated';
    public const AUTOMATED_DECISIONS = [
        'AUTOMATED_DECISION_FULLY' => self::AUTOMATED_DECISION_FULLY,
        'AUTOMATED_DECISION_PARTIALLY' => self::AUTOMATED_DECISION_PARTIALLY,
        'AUTOMATED_DECISION_NOT_AUTOMATED' => self::AUTOMATED_DECISION_NOT_AUTOMATED
    ];


    public const LABEL_STATEMENT_DECISION_GROUND = 'Ground for Decision';
    public const LABEL_STATEMENT_DECISION_GROUND_REFERENCE_URL = 'TOS or Law relied upon in taking the decision';
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
    public const DECISION_VISIBILITY_CONTENT_AGE_RESTRICTED = 'Age restricted content';
    public const DECISION_VISIBILITY_CONTENT_INTERACTION_RESTRICTED = 'Restricting interaction with content';
    public const DECISION_VISIBILITY_CONTENT_LABELLED = 'Labelled content';
    public const DECISION_VISIBILITY_OTHER = 'Other restriction (please specify)';
    public const DECISION_VISIBILITIES = [
        'DECISION_VISIBILITY_CONTENT_REMOVED' => self::DECISION_VISIBILITY_CONTENT_REMOVED,
        'DECISION_VISIBILITY_CONTENT_DISABLED' => self::DECISION_VISIBILITY_CONTENT_DISABLED,
        'DECISION_VISIBILITY_CONTENT_DEMOTED' => self::DECISION_VISIBILITY_CONTENT_DEMOTED,
        'DECISION_VISIBILITY_CONTENT_AGE_RESTRICTED' => self::DECISION_VISIBILITY_CONTENT_AGE_RESTRICTED,
        'DECISION_VISIBILITY_CONTENT_INTERACTION_RESTRICTED' => self::DECISION_VISIBILITY_CONTENT_INTERACTION_RESTRICTED,
        'DECISION_VISIBILITY_CONTENT_LABELLED' => self::DECISION_VISIBILITY_CONTENT_LABELLED,
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
    public const LABEL_STATEMENT_CATEGORY_ADDITION = 'Additional Categories';

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
    public const STATEMENT_CATEGORY_UNSAFE_AND_ILLEGAL_PRODUCTS = 'Unsafe and/or illegal products';
    public const STATEMENT_CATEGORY_VIOLENCE = 'Violence';


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
        'STATEMENT_CATEGORY_UNSAFE_AND_ILLEGAL_PRODUCTS' => self::STATEMENT_CATEGORY_UNSAFE_AND_ILLEGAL_PRODUCTS,
        'STATEMENT_CATEGORY_VIOLENCE' => self::STATEMENT_CATEGORY_VIOLENCE
    ];

    public const KEYWORD_ANIMAL_HARM = 'Animal Harm';
    public const KEYWORD_ADULT_SEXUAL_MATERIAL = 'Adult Sexual Material';
    public const KEYWORD_AGE_SPECIFIC_RESTRICTIONS_MINORS = 'Age-Specific Restrictions Concerning Minors';
    public const KEYWORD_AGE_SPECIFIC_RESTRICTIONS = 'Age-Specific Restrictions';
    public const KEYWORD_BIOMETRIC_DATA_BREACH = 'Biometric Data Breach';
    public const KEYWORD_CHILD_SEXUAL_ABUSE_MATERIAL = 'Child Sexual Abuse Material';
    public const KEYWORD_CONTENT_PROMOTING_EATING_DISORDERS = 'Content Promoting Eating Disorders';
    public const KEYWORD_COORDINATED_HARM = 'Coordinated Harm';
    public const KEYWORD_COPYRIGHT_INFRINGEMENT = 'Copyright Infringement';
    public const KEYWORD_DANGEROUS_TOYS = 'Dangerous Toys';
    public const KEYWORD_DATA_FALSIFICATION = 'Data Falsification';
    public const KEYWORD_DEFAMATION = 'Defamation';
    public const KEYWORD_DESIGN_INFRINGEMENT = 'Design Infringement';
    public const KEYWORD_DISCRIMINATION = 'Discrimination';
    public const KEYWORD_DISINFORMATION = 'Disinformation';
    public const KEYWORD_FOREIGN_INFORMATION_MANIPULATION = 'Foreign Information Manipulation and Interference';
    public const KEYWORD_GENDER_BASED_VIOLENCE = 'Gender-Based Violence';
    public const KEYWORD_GEOGRAPHIC_INDICATIONS_INFRINGEMENT = 'Geographic Indications Infringements';
    public const KEYWORD_GEOGRAPHICAL_REQUIREMENTS = 'Geographical Requirements';
    public const KEYWORD_GOODS_SERVICES_NOT_PERMITTED = 'Goods/Services Not Permitted to Be Offered on the Platform';
    public const KEYWORD_GROOMING_SEXUAL_ENTICEMENT_MINORS = 'Grooming/Sexual Enticement of Minors';
    public const KEYWORD_HATE_SPEECH = 'Hate Speech';
    public const KEYWORD_HUMAN_EXPLOITATION = 'Human Exploitation';
    public const KEYWORD_HUMAN_TRAFFICKING = 'Human Trafficking';
    public const KEYWORD_ILLEGAL_ORGANIZATIONS = 'Illegal Organizations';
    public const KEYWORD_IMAGE_BASED_SEXUAL_ABUSE = 'Image-Based Sexual Abuse (excluding content depicting minors)';
    public const KEYWORD_IMPERSONATION_ACCOUNT_HIJACKING = 'Impersonation or Account Hijacking';
    public const KEYWORD_INAUTHENTIC_ACCOUNTS = 'Inauthentic Accounts';
    public const KEYWORD_INAUTHENTIC_LISTINGS = 'Inauthentic Listings';
    public const KEYWORD_INAUTHENTIC_USER_REVIEWS = 'Inauthentic User Reviews';
    public const KEYWORD_INCITEMENT_VIOLENCE_HATRED = 'Incitement to Violence and/or Hatred';
    public const KEYWORD_INSUFFICIENT_INFORMATION_TRADERS = 'Insufficient Information on Traders';
    public const KEYWORD_LANGUAGE_REQUIREMENTS = 'Language Requirements';
    public const KEYWORD_MISINFORMATION = 'Misinformation';
    public const KEYWORD_MISSING_PROCESSING_GROUND = 'Missing Processing Ground for Data';
    public const KEYWORD_NON_CONSENSUAL_IMAGE_SHARING = 'Non-Consensual Image Sharing';
    public const KEYWORD_NON_CONSENSUAL_ITEMS_DEEPFAKE = 'Non-Consensual Items Containing Deepfake or Similar Technology Using a Third Party’s Features';
    public const KEYWORD_NUDITY = 'Nudity';
    public const KEYWORD_ONLINE_BULLYING_INTIMIDATION = 'Online Bullying/Intimidation';
    public const KEYWORD_PATENT_INFRINGEMENT = 'Patent Infringement';
    public const KEYWORD_PHISHING = 'Phishing';
    public const KEYWORD_PYRAMID_SCHEMES = 'Pyramid Schemes';
    public const KEYWORD_REGULATED_GOODS_SERVICES = 'Regulated Goods and Services';
    public const KEYWORD_RIGHT_TO_BE_FORGOTTEN = 'Right to Be Forgotten';
    public const KEYWORD_RISK_ENVIRONMENTAL_DAMAGE = 'Risk for Environmental Damage';
    public const KEYWORD_RISK_PUBLIC_HEALTH = 'Risk for Public Health';
    public const KEYWORD_SELF_MUTILATION = 'Self-Mutilation';
    public const KEYWORD_STALKING = 'Stalking';
    public const KEYWORD_SUICIDE = 'Suicide';
    public const KEYWORD_TERRORIST_CONTENT = 'Terrorist Content';
    public const KEYWORD_TRADE_SECRET_INFRINGEMENT = 'Trade Secret Infringement';
    public const KEYWORD_TRADEMARK_INFRINGEMENT = 'Trademark Infringement';
    public const KEYWORD_UNLAWFUL_SALE_ANIMALS = 'Unlawful Sale of Animals';
    public const KEYWORD_UNSAFE_CHALLENGES = 'Unsafe Challenges';
    public const KEYWORD_OTHER = 'Other';

    public const LABEL_KEYWORDS = 'Keywords';
    public const LABEL_KEYWORDS_OTHER = 'Other Keyword';

    public const KEYWORDS = [
        'KEYWORD_ANIMAL_HARM' => self::KEYWORD_ANIMAL_HARM,
        'KEYWORD_ADULT_SEXUAL_MATERIAL' => self::KEYWORD_ADULT_SEXUAL_MATERIAL,
        'KEYWORD_AGE_SPECIFIC_RESTRICTIONS_MINORS' => self::KEYWORD_AGE_SPECIFIC_RESTRICTIONS_MINORS,
        'KEYWORD_AGE_SPECIFIC_RESTRICTIONS' => self::KEYWORD_AGE_SPECIFIC_RESTRICTIONS,
        'KEYWORD_BIOMETRIC_DATA_BREACH' => self::KEYWORD_BIOMETRIC_DATA_BREACH,
        'KEYWORD_CHILD_SEXUAL_ABUSE_MATERIAL' => self::KEYWORD_CHILD_SEXUAL_ABUSE_MATERIAL,
        'KEYWORD_CONTENT_PROMOTING_EATING_DISORDERS' => self::KEYWORD_CONTENT_PROMOTING_EATING_DISORDERS,
        'KEYWORD_COORDINATED_HARM' => self::KEYWORD_COORDINATED_HARM,
        'KEYWORD_COPYRIGHT_INFRINGEMENT' => self::KEYWORD_COPYRIGHT_INFRINGEMENT,
        'KEYWORD_DANGEROUS_TOYS' => self::KEYWORD_DANGEROUS_TOYS,
        'KEYWORD_DATA_FALSIFICATION' => self::KEYWORD_DATA_FALSIFICATION,
        'KEYWORD_DEFAMATION' => self::KEYWORD_DEFAMATION,
        'KEYWORD_DESIGN_INFRINGEMENT' => self::KEYWORD_DESIGN_INFRINGEMENT,
        'KEYWORD_DISCRIMINATION' => self::KEYWORD_DISCRIMINATION,
        'KEYWORD_DISINFORMATION' => self::KEYWORD_DISINFORMATION,
        'KEYWORD_FOREIGN_INFORMATION_MANIPULATION' => self::KEYWORD_FOREIGN_INFORMATION_MANIPULATION,
        'KEYWORD_GENDER_BASED_VIOLENCE' => self::KEYWORD_GENDER_BASED_VIOLENCE,
        'KEYWORD_GEOGRAPHIC_INDICATIONS_INFRINGEMENT' => self::KEYWORD_GEOGRAPHIC_INDICATIONS_INFRINGEMENT,
        'KEYWORD_GEOGRAPHICAL_REQUIREMENTS' => self::KEYWORD_GEOGRAPHICAL_REQUIREMENTS,
        'KEYWORD_GOODS_SERVICES_NOT_PERMITTED' => self::KEYWORD_GOODS_SERVICES_NOT_PERMITTED,
        'KEYWORD_GROOMING_SEXUAL_ENTICEMENT_MINORS' => self::KEYWORD_GROOMING_SEXUAL_ENTICEMENT_MINORS,
        'KEYWORD_HATE_SPEECH' => self::KEYWORD_HATE_SPEECH,
        'KEYWORD_HUMAN_EXPLOITATION' => self::KEYWORD_HUMAN_EXPLOITATION,
        'KEYWORD_HUMAN_TRAFFICKING' => self::KEYWORD_HUMAN_TRAFFICKING,
        'KEYWORD_ILLEGAL_ORGANIZATIONS' => self::KEYWORD_ILLEGAL_ORGANIZATIONS,
        'KEYWORD_IMAGE_BASED_SEXUAL_ABUSE' => self::KEYWORD_IMAGE_BASED_SEXUAL_ABUSE,
        'KEYWORD_IMPERSONATION_ACCOUNT_HIJACKING' => self::KEYWORD_IMPERSONATION_ACCOUNT_HIJACKING,
        'KEYWORD_INAUTHENTIC_ACCOUNTS' => self::KEYWORD_INAUTHENTIC_ACCOUNTS,
        'KEYWORD_INAUTHENTIC_LISTINGS' => self::KEYWORD_INAUTHENTIC_LISTINGS,
        'KEYWORD_INAUTHENTIC_USER_REVIEWS' => self::KEYWORD_INAUTHENTIC_USER_REVIEWS,
        'KEYWORD_INCITEMENT_VIOLENCE_HATRED' => self::KEYWORD_INCITEMENT_VIOLENCE_HATRED,
        'KEYWORD_INSUFFICIENT_INFORMATION_TRADERS' => self::KEYWORD_INSUFFICIENT_INFORMATION_TRADERS,
        'KEYWORD_LANGUAGE_REQUIREMENTS' => self::KEYWORD_LANGUAGE_REQUIREMENTS,
        'KEYWORD_MISINFORMATION' => self::KEYWORD_MISINFORMATION,
        'KEYWORD_MISSING_PROCESSING_GROUND' => self::KEYWORD_MISSING_PROCESSING_GROUND,
        'KEYWORD_NON_CONSENSUAL_IMAGE_SHARING' => self::KEYWORD_NON_CONSENSUAL_IMAGE_SHARING,
        'KEYWORD_NON_CONSENSUAL_ITEMS_DEEPFAKE' => self::KEYWORD_NON_CONSENSUAL_ITEMS_DEEPFAKE,
        'KEYWORD_NUDITY' => self::KEYWORD_NUDITY,
        'KEYWORD_ONLINE_BULLYING_INTIMIDATION' => self::KEYWORD_ONLINE_BULLYING_INTIMIDATION,
        'KEYWORD_PATENT_INFRINGEMENT' => self::KEYWORD_PATENT_INFRINGEMENT,
        'KEYWORD_PHISHING' => self::KEYWORD_PHISHING,
        'KEYWORD_PYRAMID_SCHEMES' => self::KEYWORD_PYRAMID_SCHEMES,
        'KEYWORD_REGULATED_GOODS_SERVICES' => self::KEYWORD_REGULATED_GOODS_SERVICES,
        'KEYWORD_RIGHT_TO_BE_FORGOTTEN' => self::KEYWORD_RIGHT_TO_BE_FORGOTTEN,
        'KEYWORD_RISK_ENVIRONMENTAL_DAMAGE' => self::KEYWORD_RISK_ENVIRONMENTAL_DAMAGE,
        'KEYWORD_RISK_PUBLIC_HEALTH' => self::KEYWORD_RISK_PUBLIC_HEALTH,
        'KEYWORD_SELF_MUTILATION' => self::KEYWORD_SELF_MUTILATION,
        'KEYWORD_STALKING' => self::KEYWORD_STALKING,
        'KEYWORD_SUICIDE' => self::KEYWORD_SUICIDE,
        'KEYWORD_TERRORIST_CONTENT' => self::KEYWORD_TERRORIST_CONTENT,
        'KEYWORD_TRADE_SECRET_INFRINGEMENT' => self::KEYWORD_TRADE_SECRET_INFRINGEMENT,
        'KEYWORD_TRADEMARK_INFRINGEMENT' => self::KEYWORD_TRADEMARK_INFRINGEMENT,
        'KEYWORD_UNLAWFUL_SALE_ANIMALS' => self::KEYWORD_UNLAWFUL_SALE_ANIMALS,
        'KEYWORD_UNSAFE_CHALLENGES' => self::KEYWORD_UNSAFE_CHALLENGES,
        'KEYWORD_OTHER' => self::KEYWORD_OTHER,
    ];






    public const LABEL_STATEMENT_PUID = 'Platform Unique Identifier';
    public const LABEL_STATEMENT_DECISION_FACTS = 'Facts and circumstances relied on in taking the decision';
    public const LABEL_STATEMENT_CONTENT_DATE = 'When the content was posted or uploaded';
    public const LABEL_STATEMENT_APPLICATION_DATE = 'Application date of the decision';

    public const LABEL_STATEMENT_FORM_OTHER = 'Other';
    public const LABEL_STATEMENT_CONTENT_LANGUAGE = "The language of the content";

    public const LABEL_STATEMENT_END_DATE_ACCOUNT_RESTRICTION = 'End date of the account restriction';
    public const LABEL_STATEMENT_END_DATE_MONETARY_RESTRICTION = 'End date of the monetary restriction';
    public const LABEL_STATEMENT_END_DATE_SERVICE_RESTRICTION = 'End date of the service restriction decision';
    public const LABEL_STATEMENT_END_DATE_VISIBILITY_RESTRICTION = 'End date of the visibility restriction';

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
        'content_date' => 'datetime:Y-m-d',
        'application_date' => 'datetime:Y-m-d',
        'end_date_account_restriction' => 'datetime:Y-m-d',
        'end_date_monetary_restriction' => 'datetime:Y-m-d',
        'end_date_service_restriction' => 'datetime:Y-m-d',
        'end_date_visibility_restriction' => 'datetime:Y-m-d',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'territorial_scope' => 'array',
        'content_type' => 'array',
        'decision_visibility' => 'array',
        'category_addition' => 'array',
        'category_specification' => 'array'
    ];

    protected $hidden = [
        'deleted_at',
        'updated_at',
        'method',
        'user_id',
        'id',
        'platform',
        'platform_id',
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
        // This is an alias, not the actual index.
        // So in your opensearch you need to have an index with an alias 'statement_index'
        return 'statement_index';
    }

    public function toSearchableArray()
    {
        $received_date = $this->created_at->clone();
        $received_date->hour = 0;
        $received_date->minute = 0;
        $received_date->second = 0;

        return [
            'decision_visibility' => $this->decision_visibility,
            'decision_visibility_single' => implode("__", $this->decision_visibility),
            'category_specification' => $this->category_specification,
            'decision_visibility_other' => $this->decision_visibilit_other,
            'decision_monetary' => $this->decision_monetary,
            'decision_monetary_other' => $this->decision_monetary_other,
            'decision_provision' => $this->decision_provision,
            'decision_account' => $this->decision_account,
            'account_type' => $this->account_type,
            'decision_ground' => $this->decision_ground,
            'content_type' => $this->content_type,
            'content_type_single' => implode('__', $this->content_type),
            'content_type_other' => $this->content_type_other,
            'content_language' => $this->content_language,
            'illegal_content_legal_ground' => $this->illegal_content_legal_ground,
            'illegal_content_explanation' => $this->illegal_content_explanation,
            'incompatible_content_ground' => $this->incompatible_content_ground,
            'incompatible_content_explanation' => $this->incompatible_content_explanation,
            'source_type' => $this->source_type,
            'source_identity' => $this->source_identity,
            'decision_facts' => $this->decision_facts,
            'automated_detection' => $this->automated_detection === self::AUTOMATED_DETECTION_YES,
            'automated_decision' => $this->automated_decision,
            'category' => $this->category,
            'category_addition' => $this->category_addition,
            'platform_id' => $this->platform_id,
            'platform_name' => $this->platform->name,
            'platform_uuid' => $this->platform->uuid,
            'content_date' => $this->content_date,
            'application_date' => $this->application_date,
            'created_at' => $this->created_at,
            'received_date' => $received_date,
            'uuid' => $this->uuid,
            'puid' => $this->puid,
            'territorial_scope' => $this->territorial_scope,
            'method' => $this->method
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

    public function getContentTypeAttribute()
    {
        return $this->getRawKeys('content_type');
    }

    public function getDecisionVisibilityAttribute()
    {
        return $this->getRawKeys('decision_visibility');
    }

    public function getCategoryAdditionAttribute()
    {
        return $this->getRawKeys('category_addition');
    }

    public function getCategorySpecificationAttribute()
    {
        return $this->getRawKeys('category_specification');
    }


    /**
     * Return a nice string of the restrictions this statement had.
     *
     * (window dressing)
     *
     * @return string
     */
    public function restrictions(): string
    {
        $decisions = [];

//        dd($this->decision_visibility);

        if ($this->decision_visibility) {
            $decisions[] = 'Visibility';
        }
        if ($this->decision_monetary) {
            $decisions[] = 'Monetary';
        }
        if ($this->decision_provision) {
            $decisions[] = 'Provision';
        }
        if ($this->decision_account) {
            $decisions[] = 'Account';
        }

        return implode(", ", $decisions);
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
     * @return array
     */
    public function getRawKeys($key): array
    {
        if(is_null($this->getRawOriginal($key))) {
            return [];
        }
        $out = null;

        // Catch potential bad json here.
        try {
            $out = json_decode($this->getRawOriginal($key), false, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            return [];
        }


        if (is_array($out)) {
            $out = array_unique($out);
            sort($out);
        } else {
            $out = [];
        }

        return $out;
    }

}
