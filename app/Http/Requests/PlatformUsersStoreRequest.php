<?php

namespace App\Http\Requests;

use App\Models\Platform;
use App\Rules\UniquePlatformAndUser;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlatformUsersStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->canAny(['administrate','create users','view users']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'emails' => ['required', 'array'],
            'emails.*' => [
                'email',
                Rule::unique('invitations', 'email'),
                new UniquePlatformAndUser()
            ],
        ];
    }

    public function messages()
    {
        return [
            'emails.*.unique' => 'The email :input is already known in the system.',
        ];
    }



}
