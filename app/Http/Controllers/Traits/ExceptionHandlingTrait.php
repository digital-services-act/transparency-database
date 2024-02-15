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
                $subject . ' Creation Query Exception Occurred, information has been passed on the development team.'
            ]
        ];

        $message = $subject . ' Creation Query Exception Occurred, information has been passed on the development team.';

        return response()->json(['message' => $message, 'errors' => $errors], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function handleIntegrityConstraintException(QueryException $e, string $subject): JsonResponse
    {

        $duplicatePUID = $this->extractPUIDFromMessage($e->getMessage());

        // Fallback if no PUID is found. Means we have another error than a duplicate PUID. It should not happen but it is better to be safe than sorry.
        if (is_null($duplicatePUID)) {
            return $this->handleQueryException($e, $subject);
        }

        $errors = [
            'puid' => [
                'the platform identifier(s) are not all unique within this platform.'
            ],
            'existing_puids' => [$duplicatePUID]
        ];
        $message = 'the platform identifier(s) given are not all unique within this platform.';
        $out = ['message' => $message, 'errors' => $errors];

        return response()->json($out, Response::HTTP_UNPROCESSABLE_ENTITY);


    }

    private function extractPUIDFromMessage($message): string
    {
        $pattern = "/Duplicate entry '(\d+)-(\d+)'/";

        preg_match($pattern, (string) $message, $matches);

        return $matches[2] ?? "Unknown Exception";
    }
}
