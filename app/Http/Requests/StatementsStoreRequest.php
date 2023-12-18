<?php

namespace App\Http\Requests;

use App\Models\Statement;
use App\Services\EuropeanCountriesService;
use App\Services\EuropeanLanguagesService;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Fluent;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StatementsStoreRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

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
        $statementRules = (new StatementStoreRequest())->rules();

        return [
            'statements' => [
                'required',
                'array',
                Rule::forEach(function ($attribute, $value, Closure $fail) use ($statementRules) {
                    // Create a new validator instance for each statement
                    $validator = Validator::make([$attribute => $value], $statementRules);

                    // Check if validation fails and manually call the fail method if needed
                    if ($validator->fails()) {
                        $fail($validator->errors()->first());
                    }
                }),



            ],
        ];

    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
        ];
    }
}
