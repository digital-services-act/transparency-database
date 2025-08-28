<?php

namespace App\Http\Requests;

use App\Http\Controllers\Traits\ApiLoggingTrait;
use App\Models\ApiLog;
use App\Models\Platform;
use App\Rules\UniquePlatformAndUser;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PlatformUsersStoreRequest extends FormRequest
{
    use ApiLoggingTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->canAny(['create users', 'view users']);
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
                new UniquePlatformAndUser,
            ],
        ];
    }

    #[\Override]
    public function messages()
    {
        return [
            'emails.*.unique' => 'The email :input is already known in the system.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @return void
     *
     * @throws ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $firstError = $errors->first();
        $errorCount = count($errors->all()) - 1;

        $message = $firstError;
        if ($errorCount > 0) {
            $message .= " (and {$errorCount} more error".($errorCount > 1 ? 's' : '').')';
        }

        $responseData = [
            'message' => $message,
            'errors' => $errors->toArray(),
        ];

        $response = response()->json($responseData, Response::HTTP_UNPROCESSABLE_ENTITY);

        // Get the platform ID from the route parameter
        $platform = $this->route('platform');
        $platformId = $platform ? $platform->id : null;

        // Create API log directly since we need to include the error message
        ApiLog::create([
            'endpoint' => $this->path(),
            'method' => $this->method(),
            'platform_id' => $platformId,
            'request_data' => $this->all(),
            'response_data' => $responseData,
            'response_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'error_message' => $message,
        ]);

        throw new ValidationException($validator, $response);
    }
}
