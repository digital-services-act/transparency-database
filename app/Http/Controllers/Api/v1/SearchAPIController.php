<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use OpenSearch\Client;

class SearchAPIController extends Controller
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function search(Request $request): callable|array|JsonResponse
    {
        $index_name = 'statement_' . config('app.env');

        try {
            return $this->client->search([
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
        $index_name = 'statement_' . config('app.env');

        try {
            return $this->client->count([
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
        try {
            return $this->client->sql()->query($request->toArray());
        } catch (Exception $e) {
            Log::error('OpenSearch SQL Exception: ' . $e->getMessage());
            return response()->json(['error' => 'invalid query attempt'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function aggregate(Request $request, string $date_in)
    {
        try {
            $date = Carbon::createFromFormat('Y-m-d', $date_in);

            $sql = "SELECT count(id) as total, platform_id, decision_visibility_single, decision_monetary, 
            decision_provision, decision_account, category, decision_ground, automated_detection, automated_decision, 
            content_type_single, source_type FROM statement_local WHERE 
            created_at >= '" . $date->format('Y-m-d') . ' 00:00:00' . "' AND created_at <= '" . $date->format('Y-m-d') . ' 23:59:59' . "' 
            GROUP BY platform_id, decision_visibility_single, decision_monetary, decision_provision, decision_account, 
            category, decision_ground, automated_detection, automated_decision, content_type_single, source_type";

            return $this->client->sql()->query([
                "query" => $sql
            ]);

        } catch (Exception $e) {
            Log::error('OpenSearch SQL Exception: ' . $e->getMessage());
            return response()->json(['error' => 'invalid query attempt'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}