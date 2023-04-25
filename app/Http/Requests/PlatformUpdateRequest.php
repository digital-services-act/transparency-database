<?php

namespace App\Http\Requests;

use App\Models\Platform;
use Illuminate\Foundation\Http\FormRequest;

class PlatformUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('administrate');
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
            'url' => ['url', 'required', 'max:255'],
            'type' => ['required', $this->in(array_keys(Platform::PLATFORM_TYPES))],
        ];
    }

    private function in($array): string
    {
        return 'in:' . implode(',', $array);
    }
}
