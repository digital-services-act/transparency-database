<?php

namespace App\Http\Requests;

use App\Models\Statement;
use App\Services\EuropeanCountriesService;
use Illuminate\Foundation\Http\FormRequest;

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
            'decision_visibility' => [$this->in(array_keys(Statement::DECISION_VISIBILITIES), true),'required_without_all:decision_monetary,decision_provision,decision_account','nullable'],
            'decision_visibility_other' => ['required_if:decision_visibility,DECISION_VISIBILITY_OTHER','exclude_unless:decision_visibility,DECISION_VISIBILITY_OTHER','max:500'],

            'decision_monetary' => [$this->in(array_keys(Statement::DECISION_MONETARIES), true),'required_without_all:decision_visibility,decision_provision,decision_account','nullable'],
            'decision_monetary_other' => ['required_if:decision_monetary,DECISION_MONETARY_OTHER','exclude_unless:decision_monetary,DECISION_MONETARY_OTHER','max:500'],

            'decision_provision' => [$this->in(array_keys(Statement::DECISION_PROVISIONS), true),'required_without_all:decision_visibility,decision_monetary,decision_account','nullable'],
            'decision_account' => [$this->in(array_keys(Statement::DECISION_ACCOUNTS), true),'required_without_all:decision_visibility,decision_monetary,decision_provision','nullable'],


            'decision_ground' => ['required', $this->in(array_keys(Statement::DECISION_GROUNDS))],
            'illegal_content_legal_ground' => ['required_if:decision_ground,DECISION_GROUND_ILLEGAL_CONTENT','exclude_unless:decision_ground,DECISION_GROUND_ILLEGAL_CONTENT','max:500'],
            'illegal_content_explanation' => ['required_if:decision_ground,DECISION_GROUND_ILLEGAL_CONTENT','exclude_unless:decision_ground,DECISION_GROUND_ILLEGAL_CONTENT','max:2000'],
            'incompatible_content_ground' => ['required_if:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT','exclude_unless:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT','max:500'],
            'incompatible_content_explanation' => ['required_if:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT','exclude_unless:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT','max:2000'],
            'incompatible_content_illegal' => ['required_if:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT', $this->in(Statement::INCOMPATIBLE_CONTENT_ILLEGALS),'exclude_unless:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT'],

            'content_type' => ['required', $this->in(array_keys(Statement::CONTENT_TYPES))],
            'content_type_other' => ['required_if:content_type,CONTENT_TYPE_OTHER','exclude_unless:content_type,CONTENT_TYPE_OTHER','max:500'],

            'category' => ['required', $this->in(array_keys(Statement::STATEMENT_CATEGORIES))],
            'territorial_scope' => ['array', 'nullable', $this->in(EuropeanCountriesService::EUROPEAN_COUNTRY_CODES)],
            'start_date' => ['required', 'date_format:d-m-Y', 'after:2020-01-01'],
            'end_date' => ['date_format:d-m-Y', 'nullable','after_or_equal:start_date'],
            'decision_facts' => ['required','max:5000'],
            'source_type' => ['required', $this->in(array_keys(Statement::SOURCE_TYPES))],
            'source' => ['required_unless:source_type,SOURCE_VOLUNTARY','exclude_if:source_type,SOURCE_VOLUNTARY','max:500'],
            'automated_detection' => ['required', $this->in(Statement::AUTOMATED_DETECTIONS)],
            'automated_decision' => ['required', $this->in(Statement::AUTOMATED_DECISIONS)],
            'url' => ['required','max:500'],
            'puid' => ['required','max:500'],
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
            'incompatible_content_illegal.required_if' => 'The incompatible content illegal field is required when decision ground is incompatible content.',
            'source.required_unless' => 'The source field is required when source type is a notice submission.',
        ];
    }
}
