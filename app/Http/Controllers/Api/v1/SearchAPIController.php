<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use OpenSearch\Client;

class SearchAPIController extends Controller
{
    public function search(Request $request): callable|array|JsonResponse
    {
        /** @var Client $client */
        $client = app(Client::class);
        $index_name = 'statement_' . config('app.env');

        try {
            return $client->search([
                'index' => $index_name,
                'body'  => $request->toArray(),
            ]);
        } catch (Exception $e) {
            Log::error('OpenSearch Count Exception: ' . $e->getMessage());
            return response()->json(['error' => 'invalid query attempt'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    public function count(Request $request): callable|array|JsonResponse
    {
        /** @var Client $client */
        $client = app(Client::class);
        $index_name = 'statement_' . config('app.env');

        try {
            return $client->count([
                'index' => $index_name,
                'body'  => $request->toArray(),
            ]);
        } catch (Exception $e) {
            Log::error('OpenSearch Count Exception: ' . $e->getMessage());
            return response()->json(['error' => 'invalid query attempt'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function sql(Request $request): array|JsonResponse
    {
        /** @var Client $client */
        $client = app(Client::class);
        try {
            return $client->sql()->query($request->toArray());
        } catch (Exception $e) {
            Log::error('OpenSearch SQL Exception: ' . $e->getMessage());
            return response()->json(['error' => 'invalid query attempt'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}