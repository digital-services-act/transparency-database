<?php

namespace App\Http\Requests;

use App\Models\Statement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Languages;

class StatementStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [

            'decision_taken' => [$this->in(array_keys(Statement::DECISIONS))],
            'decision_ground' => [$this->in(array_keys(Statement::DECISION_GROUNDS))],
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
            'redress' => [$this->in(array_keys(Statement::REDRESSES)), 'nullable'],
            'redress_more' => ['string','nullable'],


        ];
    }

    private function in($array)
    {
        return 'in:' . implode(',', $array);
    }
}
