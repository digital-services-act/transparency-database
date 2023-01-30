<?php

namespace App\Http\Requests;

use App\Models\Notice;
use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['string', 'nullable'],
            'language' => ['required', 'string', 'max:50'],
            'date_sent' => [''],
            'date_enacted' => [''],
            'date_abolished' => [''],
            'countries_list' => ['array', 'max:20'],
            'source' => ['in:' . implode(',',Notice::SOURCES)],
            'payment_status' => ['in:' . implode(',',Notice::PAYMENT_STATUES)],
            'restriction_type' => ['in:' . implode(',',Notice::RESTRICTION_TYPES)],
            'restriction_type_other' => ['string', 'nullable'],
            'automated_detection' => ['in:'. implode(',', Notice::AUTOMATED_DETECTIONS)],
            'automated_detection_more' => ['string','nullable'],
            'illegal_content_legal_ground' => ['string', 'max:255', 'nullable'],
            'illegal_content_explanation' => ['string', 'nullable'],
            'toc_contractual_ground' => ['string', 'max:255', 'nullable'],
            'toc_explanation' => ['string', 'nullable'],
            'redress' => ['in:' . implode(',', Notice::REDRESSES)],
            'redress_more' => ['string', 'nullable'],
        ];
    }
}
