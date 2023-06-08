<?php

namespace App\Http\Requests;

use App\Models\Statement;
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
        return $this->user()->can('create statements');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'decision_visibility' => [ $this->in(array_keys(Statement::DECISIONS_VISIBILITY)),'required_without_all:decision_monetary,decision_provision,decision_account'],
            'decision_visibility_other' => ['required_if:decision_visibility,CONTENT_OTHER','exclude_unless:decision_visibility,CONTENT_OTHER'],

            'decision_monetary' => [ $this->in(array_keys(Statement::DECISIONS_MONETARY)),'required_without_all:decision_visibility,decision_provision,decision_account'],
            'decision_monetary_other' => ['required_if:decision_monetary,MONETARY_OTHER','exclude_unless:decision_monetary,MONETARY_OTHER'],

            'decision_provision' => [ $this->in(array_keys(Statement::DECISIONS_PROVISION)),'required_without_all:decision_visibility,decision_monetary,decision_account'],
            'decision_account' => [ $this->in(array_keys(Statement::DECISIONS_ACCOUNT)),'required_without_all:decision_visibility,decision_monetary,decision_provision'],


            'decision_ground' => ['required', $this->in(array_keys(Statement::DECISION_GROUNDS))],
            'illegal_content_legal_ground' => ['required_if:decision_ground,ILLEGAL_CONTENT','exclude_unless:decision_ground,ILLEGAL_CONTENT'],
            'illegal_content_explanation' => ['required_if:decision_ground,ILLEGAL_CONTENT','exclude_unless:decision_ground,ILLEGAL_CONTENT'],
            'incompatible_content_ground' => ['required_if:decision_ground,INCOMPATIBLE_CONTENT','exclude_unless:decision_ground,INCOMPATIBLE_CONTENT'],
            'incompatible_content_explanation' => ['required_if:decision_ground,INCOMPATIBLE_CONTENT','exclude_unless:decision_ground,INCOMPATIBLE_CONTENT'],
            'incompatible_content_illegal' => ['boolean','nullable','exclude_unless:decision_ground,INCOMPATIBLE_CONTENT'],

            'content_type' => ['required', $this->in(array_keys(Statement::CONTENT_TYPES))],
            'content_type_other' => ['required_if:content_type,CONTENT_TYPE_OTHER','exclude_unless:content_type,CONTENT_TYPE_OTHER'],

            'category' => ['required', $this->in(array_keys(Statement::SOR_CATEGORIES))],
            'countries_list' => ['array', 'required', $this->in(Statement::EUROPEAN_COUNTRY_CODES)],
            'start_date' => ['required', 'date'],
            'end_date' => ['date', 'nullable','after_or_equal:start_date'],
            'decision_facts' => ['required'],
            'source' => ['required', $this->in(array_keys(Statement::SOURCES))],
            'automated_detection' => ['required', $this->in(Statement::AUTOMATED_DETECTIONS)],
            'automated_decision' => ['required', $this->in(Statement::AUTOMATED_DECISIONS)],
//            'automated_takedown' => ['required', $this->in(Statement::AUTOMATED_TAKEDOWNS)],
            'url' => ['url','required'],
        ];
    }

    private function in($array): string
    {
        return 'in:' . implode(',', $array);
    }
}
