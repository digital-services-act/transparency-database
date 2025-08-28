<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PuidNotUniqueMultipleException extends Exception
{
    public function __construct(protected $duplicates)
    {
        parent::__construct('The platform identifier(s) are not all unique within this call.');
    }

    public function getJsonResponse(): JsonResponse
    {
        $errors = [
            'puid' => [
                $this->getMessage(),
            ],
            'existing_puids' => $this->duplicates,
        ];

        $response = ['message' => $this->getMessage(), 'errors' => $errors];

        return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function getDuplicates()
    {
        return $this->duplicates;
    }
}
