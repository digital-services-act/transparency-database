<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

trait ExceptionHandlingTrait
{
    public function handleQueryException(QueryException $e, string $subject): JsonResponse
    {

        Log::error($subject . ' Creation Query Exception Thrown', ['exception' => $e]);

        $errors = [
            'uncaught_exception' => [
                $subject . ' Creation Query Exception Occurred, information has been passed on to the development team.'
            ]
        ];

        $message = $subject . ' Creation Query Exception Occurred, information has been passed on to the development team.';

        return response()->json(['message' => $message, 'errors' => $errors], Response::HTTP_INTERNAL_SERVER_ERROR);
    }



    private function extractPUIDFromMessage($message): string
    {
        $pattern = "/Duplicate entry '(\d+)-(\d+)'/";

        preg_match($pattern, (string) $message, $matches);

        return $matches[2] ?? "Unknown Exception";
    }
}
