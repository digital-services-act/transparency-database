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
    public function rules($index = 0)
    {


        $currentStatement = request()->get('statements')[$index];



        return [
            'decision_visibility' => ['array', $this->in(array_keys(Statement::DECISION_VISIBILITIES), true), 'required_without_all:decision_monetary,decision_provision,decision_account', 'nullable'],
            'decision_visibility_other' => ['max:500',
                Rule::requiredIf(in_array('DECISION_VISIBILITY_OTHER', $currentStatement['decision_visibility']))
            ],
            'decision_monetary' => [$this->in(array_keys(Statement::DECISION_MONETARIES), true), 'required_without_all:decision_visibility,decision_provision,decision_account', 'nullable'],
            'decision_monetary_other' => ['required_if:decision_monetary,DECISION_MONETARY_OTHER', 'exclude_unless:decision_monetary,DECISION_MONETARY_OTHER', 'max:500'],

            'decision_provision' => [$this->in(array_keys(Statement::DECISION_PROVISIONS), true), 'required_without_all:decision_visibility,decision_monetary,decision_account', 'nullable'],
            'decision_account' => [$this->in(array_keys(Statement::DECISION_ACCOUNTS), true), 'required_without_all:decision_visibility,decision_monetary,decision_provision', 'nullable'],
            'account_type' => [$this->in(array_keys(Statement::ACCOUNT_TYPES), true), 'nullable'],
            'category_specification' => ['array', $this->in(array_keys(Statement::KEYWORDS), true), 'nullable'],
            'category_specification_other' => ['max:500'],

            'decision_ground' => ['required', $this->in(array_keys(Statement::DECISION_GROUNDS))],
            'decision_ground_reference_url' => ['url', 'nullable', 'max:500'],
            'illegal_content_legal_ground' => ['required_if:decision_ground,DECISION_GROUND_ILLEGAL_CONTENT', 'exclude_unless:decision_ground,DECISION_GROUND_ILLEGAL_CONTENT', 'max:500'],
            'illegal_content_explanation' => ['required_if:decision_ground,DECISION_GROUND_ILLEGAL_CONTENT', 'exclude_unless:decision_ground,DECISION_GROUND_ILLEGAL_CONTENT', 'max:2000'],
            'incompatible_content_ground' => ['required_if:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT', 'exclude_unless:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT', 'max:500'],
            'incompatible_content_explanation' => ['required_if:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT', 'exclude_unless:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT', 'max:2000'],
            'incompatible_content_illegal' => [$this->in(Statement::INCOMPATIBLE_CONTENT_ILLEGALS), 'exclude_unless:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT'],

            'content_type' => ['array', 'required', $this->in(array_keys(Statement::CONTENT_TYPES))],

            'content_type_other' => ['max:500',
                Rule::requiredIf(in_array('CONTENT_TYPE_OTHER', $currentStatement['content_type']))
            ],

            'category' => ['required', $this->in(array_keys(Statement::STATEMENT_CATEGORIES))],
            'category_addition' => ['array', $this->in(array_keys(Statement::STATEMENT_CATEGORIES))],

            'territorial_scope' => ['array', 'nullable', $this->in(EuropeanCountriesService::EUROPEAN_COUNTRY_CODES)],

            'content_language' => [$this->in(array_keys(EuropeanLanguagesService::ALL_LANGUAGES)), 'nullable'],

            'content_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:2000-01-01', 'before_or_equal:2038-01-01'],
            'application_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:2020-01-01', 'before_or_equal:2038-01-01'],
            'end_date_account_restriction' => ['date_format:Y-m-d', 'nullable', 'before_or_equal:2038-01-01'],
            'end_date_monetary_restriction' => ['date_format:Y-m-d', 'nullable', 'before_or_equal:2038-01-01'],
            'end_date_service_restriction' => ['date_format:Y-m-d', 'nullable', 'before_or_equal:2038-01-01'],
            'end_date_visibility_restriction' => ['date_format:Y-m-d', 'nullable', 'before_or_equal:2038-01-01'],

            'decision_facts' => ['required', 'max:5000'],
            'source_type' => ['required', $this->in(array_keys(Statement::SOURCE_TYPES))],
            'source_identity' => ['max:500','nullable'],
            'automated_detection' => ['required', $this->in(Statement::AUTOMATED_DETECTIONS)],
            'automated_decision' => ['required', $this->in(array_keys(Statement::AUTOMATED_DECISIONS))],
            'puid' => ['required', 'max:500'],
        ];
    }

    private function in($array, $nullable = false): string
    {
        return ($nullable ? 'in:null,' : 'in:') . implode(',', $array);
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'decision_visibility_other.required_if' => 'The decision visibility other field is required when decision visibility is other.',
            'decision_monetary_other.required_if' => 'The decision monetary other field is required when decision monetary is other.',
            'content_type_other.required_if' => 'The content type other field is required when content is other.',
            'illegal_content_legal_ground.required_if' => 'The illegal content legal ground field is required when decision ground is illegal content.',
            'illegal_content_explanation.required_if' => 'The illegal content explanation field is required when decision ground is illegal content.',
            'incompatible_content_ground.required_if' => 'The incompatible content ground field is required when decision ground is incompatible content.',
            'incompatible_content_explanation.required_if' => 'The incompatible content explanation field is required when decision ground is incompatible content.',
            'content_date.date_format' => 'The content date does not match the format YYYY-MM-DD.',
            'application_date.date_format' => 'The application date does not match the format YYYY-MM-DD.',
            'end_date_account_restriction.date_format' => 'The end date of account restriction does not match the format YYYY-MM-DD.',
            'end_date_monetary_restriction.date_format' => 'The end date of monetary restriction does not match the format YYYY-MM-DD.',
            'end_date_service_restriction.date_format' => 'The end date of service restriction does not match the format YYYY-MM-DD.',
            'end_date_visibility_restriction.date_format' => 'The end date of visibility restriction does not match the format YYYY-MM-DD.',
        ];
    }

    private function checkForSourceVoluntary($source_type): bool
    {
        Log::info($source_type);
        return false;
//        $check = (array)$this->get($source_type, []);
//        return in_array('SOURCE_VOLUNTARY', $check);
    }

}
