<?php

namespace App\Http\Requests;

use App\Models\Platform;
use Illuminate\Foundation\Http\FormRequest;

class PlatformStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->canAny(['create platforms']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => ['string', 'required', 'max:255'],
            'vlop' => ['int', 'required'],
            'onboarded' => ['int', 'nullable', 'sometimes'],
            'dsa_common_id' => ['nullable','string', 'unique:platforms,dsa_common_id']
        ];
    }

    private function in(array $array): string
    {
        return 'in:' . implode(',', $array);
    }
}
