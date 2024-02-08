<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Database\QueryException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

trait ExceptionHandlingTrait
{
    function handleQueryException(QueryException $e, string $subject)
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

    function handleIntegrityConstraintException(QueryException $e, string $subject)
    {

        $duplicatePUID = $this->extractPUIDFromMessage($e->getMessage());

        //Fallback if no PUID is found. Means we have another error than a duplicate PUID. It should not happen but it is better to be safe than sorry.
        if (is_null($duplicatePUID)) {
            return $this->handleQueryException($e, $subject);
        }

//        Log::error("$subject Integrity Constraint Exception Thrown: " . $e->getMessage());
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

    private function extractPUIDFromMessage($message){
        $pattern = "/Duplicate entry '(\d+)-(\d+)'/";

        preg_match($pattern, (string) $message, $matches);

        if (isset($matches[2])) {
            return $matches[2];
        } else {
            return "Unknown Exception";
        }
    }
}
