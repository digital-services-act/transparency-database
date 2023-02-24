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
//        $languages = Languages::getLanguageCodes();
        $european_country_codes = Statement::EUROPEAN_COUNTRY_CODES;

        return [
//            'title' => ['required', 'string', 'max:255'],
//            'body' => ['string', 'nullable'],
//            'language' => ['required', $this->in($languages)],
//            'date_sent' => ['date', 'nullable'],
//            'date_enacted' => ['date', 'nullable'],
//            'date_abolished' => ['date', 'nullable'],
            'countries_list' => ['array', 'max:28', $this->in($european_country_codes)],
//            'source' => ['required', $this->in(Statement::SOURCES)],
//            'payment_status' => [$this->in(Statement::PAYMENT_STATUES)],
//            'restriction_type' => [$this->in(Statement::RESTRICTION_TYPES)],
//            'restriction_type_other' => ['string', 'nullable'],
//            'automated_detection' => [$this->in(Statement::AUTOMATED_DETECTIONS)],
//            'automated_detection_more' => ['string','nullable'],
//            'illegal_content_legal_ground' => ['string', 'max:255', 'nullable'],
//            'illegal_content_explanation' => ['string', 'nullable'],
//            'toc_contractual_ground' => ['string', 'max:255', 'nullable'],
//            'toc_explanation' => ['string', 'nullable'],
//            'redress' => [$this->in(Statement::REDRESSES)],
//            'redress_more' => ['string', 'nullable'],
              'decision_taken' => [$this->in(array_keys(Statement::DECISIONS))],
        ];
    }

    private function in($array)
    {
        return 'in:' . implode(',',$array);
    }
}
