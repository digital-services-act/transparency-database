<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PuidNotUniqueSingleException extends Exception
{
    public function __construct(protected $duplicate)
    {
        parent::__construct('The identifier given is not unique within this platform.');
    }

    public function getJsonResponse(): JsonResponse
    {
        $errors = [
            'puid' => [
                $this->getMessage(),
            ],
        ];

        $response = ['message' => $this->getMessage(), 'errors' => $errors, 'existing' => (object) ['puid' => $this->duplicate]];

        return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
