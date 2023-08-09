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

class StatementStoreRequest extends FormRequest
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
    public function rules()
    {
        return [
            'decision_visibility' => ['array',$this->in(array_keys(Statement::DECISION_VISIBILITIES), true), 'required_without_all:decision_monetary,decision_provision,decision_account', 'nullable'],

            'decision_visibility_other' => ['max:500',
                Rule::requiredIf($this->checkForDecisionVisibilityOther()),
                Rule::excludeIf(!$this->checkForDecisionVisibilityOther()),
            ],

            'decision_monetary' => [$this->in(array_keys(Statement::DECISION_MONETARIES), true), 'required_without_all:decision_visibility,decision_provision,decision_account', 'nullable'],
            'decision_monetary_other' => ['required_if:decision_monetary,DECISION_MONETARY_OTHER', 'exclude_unless:decision_monetary,DECISION_MONETARY_OTHER', 'max:500'],

            'decision_provision' => [$this->in(array_keys(Statement::DECISION_PROVISIONS), true), 'required_without_all:decision_visibility,decision_monetary,decision_account', 'nullable'],
            'decision_account' => [$this->in(array_keys(Statement::DECISION_ACCOUNTS), true), 'required_without_all:decision_visibility,decision_monetary,decision_provision', 'nullable'],
            'account_type' => [$this->in(array_keys(Statement::ACCOUNT_TYPES), true), 'nullable'],


            'decision_ground' => ['required', $this->in(array_keys(Statement::DECISION_GROUNDS))],
            'decision_ground_reference_url' => ['url','nullable','max:500'],
            'illegal_content_legal_ground' => ['required_if:decision_ground,DECISION_GROUND_ILLEGAL_CONTENT', 'exclude_unless:decision_ground,DECISION_GROUND_ILLEGAL_CONTENT', 'max:500'],
            'illegal_content_explanation' => ['required_if:decision_ground,DECISION_GROUND_ILLEGAL_CONTENT', 'exclude_unless:decision_ground,DECISION_GROUND_ILLEGAL_CONTENT', 'max:2000'],
            'incompatible_content_ground' => ['required_if:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT', 'exclude_unless:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT', 'max:500'],
            'incompatible_content_explanation' => ['required_if:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT', 'exclude_unless:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT', 'max:2000'],
            'incompatible_content_illegal' => [$this->in(Statement::INCOMPATIBLE_CONTENT_ILLEGALS), 'exclude_unless:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT'],

            'content_type' => ['array', 'required', $this->in(array_keys(Statement::CONTENT_TYPES))],

            'content_type_other' => ['max:500',
                Rule::requiredIf($this->checkForContentTypeOther()),
                Rule::excludeIf(!$this->checkForContentTypeOther()),
            ],

            'category' => ['required', $this->in(array_keys(Statement::STATEMENT_CATEGORIES))],
            'category_addition' => ['array', $this->in(array_keys(Statement::STATEMENT_CATEGORIES))],

            'territorial_scope' => ['array', 'nullable', $this->in(EuropeanCountriesService::EUROPEAN_COUNTRY_CODES)],

            'content_language' => [$this->in(array_keys(EuropeanLanguagesService::ALL_LANGUAGES)), 'nullable'],

            'content_date' => ['required', 'date_format:Y-m-d', 'after:2020-01-01'],
            'application_date' => ['required', 'date_format:Y-m-d', 'after:2020-01-01'],
            'end_date' => ['date_format:Y-m-d', 'nullable', 'after_or_equal:application_date'],

            'decision_facts' => ['required', 'max:5000'],
            'source_type' => ['required', $this->in(array_keys(Statement::SOURCE_TYPES))],
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
            'illegal_content_explanation.required_if' => 'The illegal content legal ground field is required when decision ground is illegal content.',
            'incompatible_content_ground.required_if' => 'The incompatible content ground field is required when decision ground is incompatible content.',
            'incompatible_content_explanation.required_if' => 'The incompatible content explanation field is required when decision ground is incompatible content.',
            'source.required_unless' => 'The source field is required when source type is a notice submission.',
            'content_date.date_format' => 'The content date does not match the format YYYY-MM-DD.',
            'application_date.date_format' => 'The application date does not match the format YYYY-MM-DD.',
            'end_date.date_format' => 'The end date does not match the format YYYY-MM-DD.',
        ];
    }


    private function checkForContentTypeOther(): bool
    {
        $check = (array)$this->get('content_type', []);
        return in_array('CONTENT_TYPE_OTHER', $check);
    }

    private function checkForDecisionVisibilityOther(): bool
    {
        $check = (array)$this->get('decision_visibility', []);
        return in_array('DECISION_VISIBILITY_OTHER', $check);
    }

}
