<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PuidNotUniqueMultipleException extends Exception
{

    protected $duplicates;
    public function __construct($duplicates)
    {
        parent::__construct('The platform identifier(s) are not all unique within this call.');
        $this->duplicates = $duplicates;
    }



    public function getJsonResponse(): JsonResponse
    {
        $errors = [
            'puid' => [
                $this->getMessage(),
            ],
            'existing_puids' => $this->duplicates
        ];

        $response = ['message' => $this->getMessage(), 'errors' => $errors];

        return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function getDuplicates(){
        return $this->duplicates;
    }

}
