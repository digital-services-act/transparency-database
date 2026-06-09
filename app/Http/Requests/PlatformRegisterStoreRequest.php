<?php

namespace App\Http\Requests;

use App\Models\Platform;
use Illuminate\Foundation\Http\FormRequest;

class PlatformRegisterStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return ! ((bool) $this->user()->platform && $this->user()->platform->name !== Platform::LABEL_DSA_TEAM);
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
        ];
    }

    private function in(array $array): string
    {
        return 'in:'.implode(',', $array);
    }
}
