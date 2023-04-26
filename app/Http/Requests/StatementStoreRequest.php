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
            'decision_taken' => ['required', $this->in(array_keys(Statement::DECISIONS))],
            'decision_ground' => ['required', $this->in(array_keys(Statement::DECISION_GROUNDS))],
            'illegal_content_legal_ground' => ['required_if:decision_ground,ILLEGAL_CONTENT','exclude_unless:decision_ground,ILLEGAL_CONTENT'],
            'illegal_content_explanation' => ['required_if:decision_ground,ILLEGAL_CONTENT','exclude_unless:decision_ground,ILLEGAL_CONTENT'],
            'incompatible_content_ground' => ['required_if:decision_ground,INCOMPATIBLE_CONTENT','exclude_unless:decision_ground,INCOMPATIBLE_CONTENT'],
            'incompatible_content_explanation' => ['required_if:decision_ground,INCOMPATIBLE_CONTENT','exclude_unless:decision_ground,INCOMPATIBLE_CONTENT'],
            'countries_list' => ['array', 'max:28', $this->in(Statement::EUROPEAN_COUNTRY_CODES)],
            'date_abolished' => ['date', 'nullable'],
            'source' => ['required', $this->in(array_keys(Statement::SOURCES))],
            'source_identity' => ['string','nullable'],
            'source_other' => ['required_if:source,SOURCE_OTHER','exclude_unless:source,SOURCE_OTHER'],
            'automated_detection' => ['required', $this->in(Statement::AUTOMATED_DETECTIONS)],
            'automated_takedown' => ['required', $this->in(Statement::AUTOMATED_TAKEDOWNS)],
            'redress' => [$this->in(array_keys(Statement::REDRESSES)), 'nullable'],
            'redress_more' => ['string','nullable'],
            'statement_of_reason' => ['string','nullable'],
            'url' => ['string','nullable'],
        ];
    }

    private function in($array): string
    {
        return 'in:' . implode(',', $array);
    }
}
