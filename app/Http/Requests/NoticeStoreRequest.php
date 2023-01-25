<?php

namespace App\Http\Requests;

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
            'body' => ['string'],
            'language' => ['required', 'string', 'max:50'],
            'date_sent' => [''],
            'date_enacted' => [''],
            'date_abolished' => [''],
            'countries_list' => ['string', 'max:255'],
            'source' => ['in:Article 16,voluntary own-initiative investigation'],
            'payment_status' => ['in:suspension,termination,other'],
            'restriction_type' => ['in:removed,disabled,demoted,other'],
            'restriction_type_other' => ['string'],
            'automated_detection' => ['in:Yes,No,Partial'],
            'automated_detection_more' => ['string'],
            'illegal_content_legal_ground' => ['string', 'max:255'],
            'illegal_content_explanation' => ['string'],
            'toc_contractual_ground' => ['string', 'max:255'],
            'toc_explanation' => ['string'],
            'redress' => ['in:Internal Mechanism,Out Of Court Settlement,Other'],
            'redress_more' => ['string'],
            'user_id' => ['integer'],
        ];
    }
}
