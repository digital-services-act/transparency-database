<?php

namespace App\Http\Requests;

use App\Models\Statement;
use App\Services\EuropeanCountriesService;
use App\Services\EuropeanLanguagesService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Fluent;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StatementsStoreRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create statements') && $this->user()->platform;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'statements' => 'required|array|min:1|max:100',
            'statements.*.decision_visibility' => ['array', $this->in(array_keys(Statement::DECISION_VISIBILITIES), true), 'required_without_all:statements.*.decision_monetary,statements.*.decision_provision,statements.*.decision_account', 'nullable'],

            'statements.*.decision_visibility_other' => ['max:500',
                Rule::requiredIf($this->checkForDecisionVisibilityOther()),
                Rule::excludeIf(!$this->checkForDecisionVisibilityOther()),
            ],

            'statements.*.decision_monetary' => [$this->in(array_keys(Statement::DECISION_MONETARIES), true), 'required_without_all:statements.*.decision_visibility,statements.*.decision_provision,statements.*.decision_account', 'nullable'],
            'statements.*.decision_monetary_other' => ['required_if:decision_monetary,DECISION_MONETARY_OTHER', 'exclude_unless:decision_monetary,DECISION_MONETARY_OTHER', 'max:500'],

            'statements.*.decision_provision' => [$this->in(array_keys(Statement::DECISION_PROVISIONS), true), 'required_without_all:statements.*.decision_visibility,statements.*.decision_monetary,statements.*.decision_account', 'nullable'],
            'statements.*.decision_account' => [$this->in(array_keys(Statement::DECISION_ACCOUNTS), true), 'required_without_all:statements.*.decision_visibility,statements.*.decision_monetary,statements.*.decision_provision', 'nullable'],
            'statements.*.account_type' => [$this->in(array_keys(Statement::ACCOUNT_TYPES), true), 'nullable'],
            'statements.*.category_specification' => ['array', $this->in(array_keys(Statement::KEYWORDS), true), 'nullable'],
            'statements.*.category_specification_other' => ['max:500'],

            'statements.*.decision_ground' => ['required', $this->in(array_keys(Statement::DECISION_GROUNDS))],
            'statements.*.decision_ground_reference_url' => ['url', 'nullable', 'max:500'],
            'statements.*.illegal_content_legal_ground' => ['required_if:decision_ground,DECISION_GROUND_ILLEGAL_CONTENT', 'exclude_unless:statements.*.decision_ground,DECISION_GROUND_ILLEGAL_CONTENT', 'max:500'],
            'statements.*.illegal_content_explanation' => ['required_if:decision_ground,DECISION_GROUND_ILLEGAL_CONTENT', 'exclude_unless:statements.*.decision_ground,DECISION_GROUND_ILLEGAL_CONTENT', 'max:2000'],
            'statements.*.incompatible_content_ground' => ['required_if:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT', 'exclude_unless:statements.*.decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT', 'max:500'],
            'statements.*.incompatible_content_explanation' => ['required_if:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT', 'exclude_unless:statements.*.decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT', 'max:2000'],
            'statements.*.incompatible_content_illegal' => [$this->in(Statement::INCOMPATIBLE_CONTENT_ILLEGALS), 'exclude_unless:statements.*.decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT'],

            'statements.*.content_type' => ['array', 'required', $this->in(array_keys(Statement::CONTENT_TYPES))],

            'statements.*.content_type_other' => ['max:500',
                Rule::requiredIf($this->checkForContentTypeOther()),
                Rule::excludeIf(!$this->checkForContentTypeOther()),
            ],

            'statements.*.category' => ['required', $this->in(array_keys(Statement::STATEMENT_CATEGORIES))],
            'statements.*.category_addition' => ['array', $this->in(array_keys(Statement::STATEMENT_CATEGORIES))],

            'statements.*.territorial_scope' => ['array', 'nullable', $this->in(EuropeanCountriesService::EUROPEAN_COUNTRY_CODES)],

            'statements.*.content_language' => [$this->in(array_keys(EuropeanLanguagesService::ALL_LANGUAGES)), 'nullable'],

            'statements.*.content_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:2000-01-01','before_or_equal:2038-01-01'],
            'statements.*.application_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:2020-01-01','before_or_equal:2038-01-01'],
            'statements.*.end_date_account_restriction' => ['date_format:Y-m-d', 'nullable','before_or_equal:2038-01-01'],
            'statements.*.end_date_monetary_restriction' => ['date_format:Y-m-d', 'nullable','before_or_equal:2038-01-01'],
            'statements.*.end_date_service_restriction' => ['date_format:Y-m-d', 'nullable','before_or_equal:2038-01-01'],
            'statements.*.end_date_visibility_restriction' => ['date_format:Y-m-d', 'nullable','before_or_equal:2038-01-01'],

            'statements.*.decision_facts' => ['required', 'max:5000'],
            'statements.*.source_type' => ['required', $this->in(array_keys(Statement::SOURCE_TYPES))],
            'statements.*.source_identity' => ['max:500',
                Rule::excludeIf($this->checkForSourceVoluntary())
            ],
            'statements.*.automated_detection' => ['required', $this->in(Statement::AUTOMATED_DETECTIONS)],
            'statements.*.automated_decision' => ['required', $this->in(array_keys(Statement::AUTOMATED_DECISIONS))],
            'statements.*.puid' => ['required', 'max:500'],
        ];
    }

    private function in($array, $nullable = false): string
    {
        return ($nullable ? 'in:null,' : 'in:') . implode(',', $array);
    }

    private function checkForContentTypeOther(): bool
    {
        $check = (array)$this->get('content_type', []);
        return in_array('CONTENT_TYPE_OTHER', $check);
    }

    private function checkForDecisionVisibilityOther(): bool
    {
        $check = (array)$this->get('statements.*.decision_visibility', []);
        return in_array('DECISION_VISIBILITY_OTHER', $check);
    }

    private function checkForSourceVoluntary(): bool
    {
        $check = (array)$this->get('statements.*.source_type', []);
        return in_array('SOURCE_VOLUNTARY', $check);
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'statements.*.decision_visibility_other.required_if' => 'The decision visibility other field is required when decision visibility is other.',
            'statements.*.decision_monetary_other.required_if' => 'The decision monetary other field is required when decision monetary is other.',
            'statements.*.content_type_other.required_if' => 'The content type other field is required when content is other.',
            'statements.*.illegal_content_legal_ground.required_if' => 'The illegal content legal ground field is required when decision ground is illegal content.',
            'statements.*.illegal_content_explanation.required_if' => 'The illegal content explanation field is required when decision ground is illegal content.',
            'statements.*.incompatible_content_ground.required_if' => 'The incompatible content ground field is required when decision ground is incompatible content.',
            'statements.*.incompatible_content_explanation.required_if' => 'The incompatible content explanation field is required when decision ground is incompatible content.',
            'statements.*.content_date.date_format' => 'The content date does not match the format YYYY-MM-DD.',
            'statements.*.application_date.date_format' => 'The application date does not match the format YYYY-MM-DD.',
            'statements.*.end_date_account_restriction.date_format' => 'The end date of account restriction does not match the format YYYY-MM-DD.',
            'statements.*.end_date_monetary_restriction.date_format' => 'The end date of monetary restriction does not match the format YYYY-MM-DD.',
            'statements.*.end_date_service_restriction.date_format' => 'The end date of service restriction does not match the format YYYY-MM-DD.',
            'statements.*.end_date_visibility_restriction.date_format' => 'The end date of visibility restriction does not match the format YYYY-MM-DD.',
        ];
    }
}
