<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Database\QueryException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

trait ExceptionHandlingTrait
{
    function handleQueryException(QueryException $e, String $subject)
    {

        Log::error("$subject Creation Query Exception Thrown: " . $e->getMessage());

        $errors = [
            'uncaught_exception' => [
                "$subject Creation Query Exception Thrown: " . $e->getMessage()
            ]
        ];

        $message = "$subject Creation Query Exception Thrown: " . $e->getMessage();

        return response()->json(['message' => $message, 'errors' => $errors], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
