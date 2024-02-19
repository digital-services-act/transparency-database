<?php

namespace App\Http\Requests;

use App\Models\Platform;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlatformUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->canAny(['create platforms', 'view platforms']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $platform = $this->route('platform');

        return [
            'name' => ['string', 'required', 'max:255'],
            'vlop' => ['int', 'required'],
            'dsa_common_id' => [
                'string',
                'nullable',
                Rule::unique('platforms')->ignore($platform->id),
            ]
        ];
    }

    private function in($array): string
    {
        return 'in:' . implode(',', $array);
    }
}
