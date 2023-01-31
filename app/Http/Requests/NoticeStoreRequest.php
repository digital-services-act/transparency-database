<?php

namespace App\Http\Requests;

use App\Models\Notice;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Languages;

class NoticeStoreRequest extends FormRequest
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
        $languages = Languages::getLanguageCodes();
        $countries = Countries::getCountryCodes();

        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['string', 'nullable'],
            'language' => ['required', $this->in($languages)],
            'date_sent' => ['date', 'nullable'],
            'date_enacted' => ['date', 'nullable'],
            'date_abolished' => ['date', 'nullable'],
            'countries_list' => ['array', 'max:20', $this->in($countries)],
            'source' => ['required', $this->in(Notice::SOURCES)],
            'payment_status' => [$this->in(Notice::PAYMENT_STATUES)],
            'restriction_type' => [$this->in(Notice::RESTRICTION_TYPES)],
            'restriction_type_other' => ['string', 'nullable'],
            'automated_detection' => [$this->in(Notice::AUTOMATED_DETECTIONS)],
            'automated_detection_more' => ['string','nullable'],
            'illegal_content_legal_ground' => ['string', 'max:255', 'nullable'],
            'illegal_content_explanation' => ['string', 'nullable'],
            'toc_contractual_ground' => ['string', 'max:255', 'nullable'],
            'toc_explanation' => ['string', 'nullable'],
            'redress' => [$this->in(Notice::REDRESSES)],
            'redress_more' => ['string', 'nullable'],
        ];
    }

    private function in($array)
    {
        return 'in:' . implode(',',$array);
    }
}
