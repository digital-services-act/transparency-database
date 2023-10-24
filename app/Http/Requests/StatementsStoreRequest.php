<?php

namespace App\Http\Requests;

use App\Models\Statement;
use App\Services\EuropeanCountriesService;
use App\Services\EuropeanLanguagesService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Fluent;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StatementsStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create statements') && $this->user()->platform;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $single = new StatementStoreRequest();
        $rules = $single->rules();

        $rulesout = [];
        foreach ($rules as $key => $rule)
        {
            $rulesout['*.'.$key] = $rule;
        }

        return $rulesout;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $single = new StatementStoreRequest();
        $messages = $single->messages();

        $messagesout = [];
        foreach ($messages as $key => $message)
        {
            $messagesout['*.'.$key] = $message;
        }

        return $messagesout;
    }
}
