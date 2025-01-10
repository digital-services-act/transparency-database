<?php

namespace App\Http\Requests;

use App\Models\Platform;
use App\Http\Controllers\Traits\ApiLoggingTrait;
use App\Models\ApiLog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class PlatformStoreRequest extends FormRequest
{
    use ApiLoggingTrait;

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
            'has_tokens' => ['int', 'nullable', 'sometimes'],
            'has_statements' => ['int', 'nullable', 'sometimes'],
            'dsa_common_id' => ['nullable','string', 'unique:platforms,dsa_common_id']
        ];
    }

    private function in(array $array): string
    {
        return 'in:' . implode(',', $array);
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     *
     * @throws ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        $responseData = [
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors()
        ];

        $response = response()->json($responseData, Response::HTTP_UNPROCESSABLE_ENTITY);

        // Create API log with validation error
        ApiLog::create([
            'endpoint' => $this->path(),
            'method' => $this->method(),
            'platform_id' => null,
            'request_data' => $this->all(),
            'response_data' => $responseData,
            'response_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'error_message' => $validator->errors()->first(),
        ]);

        throw new ValidationException($validator, $response);
    }
}
