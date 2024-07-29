<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlatformUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->canAny(['create platforms', 'view platforms']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $platform = $this->route('platform');

        return [
            'name' => ['string', 'required', 'max:255'],
            'vlop' => ['int', 'required'],
            'onboarded' => ['int', 'nullable', 'sometimes'],
            'has_tokens' => ['int', 'nullable', 'sometimes'],
            'has_statements' => ['int', 'nullable', 'sometimes'],
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
