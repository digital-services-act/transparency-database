<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PuidNotUniqueSingleException extends Exception
{

    protected $duplicate;
    public function __construct($duplicate)
    {
        parent::__construct('The identifier given is not unique within this platform.');
        $this->duplicate = $duplicate;
    }



    public function getJsonResponse(): JsonResponse
    {
        $errors = [
            'puid' => [
                $this->getMessage(),
            ],
        ];

        $response = ['message' => $this->getMessage(), 'errors' => $errors, 'existing' => (object)['puid' => $this->duplicate]];

        return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
    }



}
