<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Platform;
use App\Models\Statement;
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
    private string $index_name;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->index_name = 'statement_' . config('app.env');
    }

    public function search(Request $request): callable|array|JsonResponse
    {
        try {
            return $this->client->search([
                'index' => $this->index_name,
                'body'  => $request->toArray(),
            ]);
        } catch (Exception $e) {
            Log::error('OpenSearch Count Exception: ' . $e->getMessage());
            return response()->json(['error' => 'invalid query attempt'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function count(Request $request): callable|array|JsonResponse
    {
        try {
            return $this->client->count([
                'index' => $this->index_name,
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
            content_type_single, source_type FROM " . $this->index_name . " WHERE 
            created_at >= '" . $date->format('Y-m-d') . ' 00:00:00' . "' AND created_at <= '" . $date->format('Y-m-d') . ' 23:59:59' . "' 
            GROUP BY platform_id, decision_visibility_single, decision_monetary, decision_provision, decision_account, 
            category, decision_ground, automated_detection, automated_decision, content_type_single, source_type LIMIT 10000";

            return $this->client->sql()->query([
                "query" => $sql
            ]);

        } catch (Exception $e) {
            Log::error('OpenSearch SQL Exception: ' . $e->getMessage());
            return response()->json(['error' => 'invalid query attempt'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function platforms(Request $request)
    {
        try {
            return Platform::nonDSA()->get()->pluck('name', 'id')->toArray();
        } catch (Exception $e) {
            Log::error('OpenSearch SQL Exception: ' . $e->getMessage());
            return response()->json(['error' => 'invalid query attempt'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function labels(Request $request)
    {
        try {
            return [
                'decision_visibilities' => Statement::DECISION_VISIBILITIES,
                'decision_monetaries' => Statement::DECISION_MONETARIES,
                'decision_provisions' => Statement::DECISION_PROVISIONS,
                'decision_accounts' => Statement::DECISION_ACCOUNTS,
                'categories' => Statement::STATEMENT_CATEGORIES,
                'decision_grounds' => Statement::DECISION_GROUNDS,
                'automated_detections' => Statement::AUTOMATED_DETECTIONS,
                'automated_decisions' => Statement::AUTOMATED_DECISIONS,
                'content_types' => Statement::CONTENT_TYPES,
                'source_types' => Statement::SOURCE_TYPES
            ];
        } catch (Exception $e) {
            Log::error('OpenSearch SQL Exception: ' . $e->getMessage());
            return response()->json(['error' => 'invalid query attempt'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}