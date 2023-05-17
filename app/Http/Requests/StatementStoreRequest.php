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
            'category' => ['required', $this->in(array_keys(Statement::SOR_CATEGORIES))],
            'countries_list' => ['array', 'nullable', $this->in(Statement::EUROPEAN_COUNTRY_CODES)],
            'date_abolished' => ['date', 'nullable'],
            'source' => ['required', $this->in(array_keys(Statement::SOURCES))],
            'source_identity' => ['required_if:source,SOURCE_ARTICLE_16','exclude_unless:source,SOURCE_ARTICLE_16'],
            'source_own_voluntary' => ['required_if:source,SOURCE_VOLUNTARY','exclude_unless:source,SOURCE_VOLUNTARY'],
            'automated_detection' => ['required', $this->in(Statement::AUTOMATED_DETECTIONS)],
            'automated_decision' => ['required', $this->in(Statement::AUTOMATED_DECISIONS)],
            'automated_takedown' => ['required', $this->in(Statement::AUTOMATED_TAKEDOWNS)],
//            'redress' => [$this->in(array_keys(Statement::REDRESSES)), 'nullable'],
//            'redress_more' => ['string','nullable'],
            'url' => ['string','nullable'],
        ];
    }

    private function in($array): string
    {
        return 'in:' . implode(',', $array);
    }
}
