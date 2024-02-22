<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create users');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $user = $this->route('user');
        return [
            'email' => ['email', 'required', 'max:255',
                Rule::unique('users')->ignore($user->id)],
            'platform_id' => ['int', 'nullable'],
            'roles' => ['required', 'array']
        ];
    }
}
