<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    #[\Override]
    public function register(): void
    {
        $this->reportable(static function (Throwable $e) {
            //
        });
    }

    #[\Override]
    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        // logging here (i'm using a trait to have custom logger but you can use default logger too)
        Log::info($request, $e->errors());

        return parent::convertValidationExceptionToResponse($e, $request);
    }
}
