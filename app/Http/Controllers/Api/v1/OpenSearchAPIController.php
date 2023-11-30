<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Platform;
use App\Models\Statement;
use App\Services\StatementSearchService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use OpenSearch\Client;
use RuntimeException;

class OpenSearchAPIController extends Controller
{
    private Client $client;
    private StatementSearchService $statement_search_service;
    private string $index_name;

    public function __construct(Client $client, StatementSearchService $statement_search_service)
    {
        $this->client                   = $client;
        $this->statement_search_service = $statement_search_service;
        $this->index_name               = 'statement_' . config('app.env');
    }

    /**
     * @param Request $request
     *
     * @return callable|array|JsonResponse
     */
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

    /**
     * @param Request $request
     *
     * @return callable|array|JsonResponse
     */
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

    /**
     * @param Request $request
     *
     * @return array|JsonResponse
     */
    public function sql(Request $request): array|JsonResponse
    {
        try {
            return $this->client->sql()->query($request->toArray());
        } catch (Exception $e) {
            Log::error('OpenSearch SQL Exception: ' . $e->getMessage());

            return response()->json(['error' => 'invalid query attempt'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @param Request $request
     *
     * @return array|JsonResponse
     */
    public function explain(Request $request): array|JsonResponse
    {
        try {
            $results          = $this->client->sql()->explain($request->toArray());
            $query            = $results['root']['children'][0]['description']['request'] ?? false;
            $query            = '{' . ltrim(strstr($query, '{'), '{');
            $query            = substr($query, 0, strrpos($query, '}')) . '}';
            $results['query'] = json_decode($query, true, 512, JSON_THROW_ON_ERROR);

            return $results;
        } catch (Exception $e) {
            Log::error('OpenSearch SQL Exception: ' . $e->getMessage());

            return response()->json(['error' => 'invalid query attempt: ' . $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function clearAggregateCache(Request $request)
    {
        $this->statement_search_service->clearOSACache();
        return 'ok';
    }

    /**
     * @param Request $request
     * @param string $date_in
     * @param string|null $attributes_in
     *
     * @return JsonResponse|array
     */
    public function aggregatesForDate(Request $request, string $date_in, string $attributes_in = null): JsonResponse|array
    {
        try {
            if ($date_in === 'yesterday') {
                $date_in = Carbon::yesterday()->format('Y-m-d');
            }

            $date = Carbon::createFromFormat('Y-m-d', $date_in);
            $date->subSeconds($date->secondsSinceMidnight());

            $attributes = $this->sanitizeAttributes($attributes_in, true);

            $results = $this->statement_search_service->processDateAggregate($date, $attributes, (bool)$request->query('cache', 1));

            return response()->json($results);

        } catch (Exception $e) {
            Log::error('OpenSearch SQL Exception: ' . $e->getMessage());

            return response()->json(['error' => 'invalid aggregate attempt, see logs.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @param Request $request
     * @param string $start_in
     * @param string $end_in
     * @param string|null $attributes_in
     *
     * @return JsonResponse|array
     */
    public function aggregatesForRange(Request $request, string $start_in, string $end_in, string $attributes_in = null): JsonResponse|array
    {
        try {
            $dates = $this->sanitizeDateStartEndStrings($start_in, $end_in, true);

            if ($dates['start'] >= $dates['end'] || $dates['end'] >= Carbon::today()) {
                throw new RuntimeException('Start must be less than end, and end must be in the past');
            }

            $attributes = $this->sanitizeAttributes($attributes_in);

            $results = $this->statement_search_service->processRangeAggregate($dates['start'], $dates['end'], $attributes, (bool)$request->query('cache', 1));

            return response()->json($results);
        } catch (Exception $e) {
            Log::error('OpenSearch SQL Exception: ' . $e->getMessage());

            return response()->json(['error' => 'invalid aggregate range attempt, see logs.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @param Request $request
     * @param string $start_in
     * @param string $end_in
     * @param string|null $attributes_in
     *
     * @return JsonResponse|array
     */
    public function aggregatesForRangeDates(Request $request, string $start_in, string $end_in, string $attributes_in = null): JsonResponse|array
    {
        try {
            $dates = $this->sanitizeDateStartEndStrings($start_in, $end_in);

            if ($dates['start'] >= $dates['end'] || $dates['end'] >= Carbon::today()) {
                throw new RuntimeException('Start must be less than end, and end must be in the past');
            }

            $attributes = $this->sanitizeAttributes($attributes_in);

            $results = $this->statement_search_service->processDatesAggregate($dates['start'], $dates['end'], $attributes, (bool)$request->query('cache', 1), (bool)$request->query('daycache', 1));

            return response()->json($results);
        } catch (Exception $e) {
            Log::error('OpenSearch SQL Exception: ' . $e->getMessage());

            return response()->json(['error' => 'invalid aggregate dates attempt, see logs.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse|array
     */
    public function platforms(Request $request): JsonResponse|array
    {
        try {
            $platforms = Platform::all()->pluck('name', 'id')->toArray();
            $out       = array_map(static function ($id, $name) {
                return ['id' => $id, 'name' => $name];
            }, array_keys($platforms), array_values($platforms));

            return response()->json(['platforms' => $out]);
        } catch (Exception $e) {
            Log::error('OpenSearch SQL Exception: ' . $e->getMessage());

            return response()->json(['error' => 'invalid platforms attempt, see logs.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse|array
     */
    public function labels(Request $request): JsonResponse|array
    {
        try {
            return [
                'decision_visibilities' => Statement::DECISION_VISIBILITIES,
                'decision_monetaries'   => Statement::DECISION_MONETARIES,
                'decision_provisions'   => Statement::DECISION_PROVISIONS,
                'decision_accounts'     => Statement::DECISION_ACCOUNTS,
                'categories'            => Statement::STATEMENT_CATEGORIES,
                'decision_grounds'      => Statement::DECISION_GROUNDS,
                'automated_detections'  => Statement::AUTOMATED_DETECTIONS,
                'automated_decisions'   => Statement::AUTOMATED_DECISIONS,
                'content_types'         => Statement::CONTENT_TYPES,
                'source_types'          => Statement::SOURCE_TYPES
            ];
        } catch (Exception $e) {
            Log::error('OpenSearch SQL Exception: ' . $e->getMessage());
            return response()->json(['error' => 'invalid labels attempt, see logs.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function total(Request $request): JsonResponse
    {
        return response()->json($this->statement_search_service->grandTotal());
    }

    public function dateTotal(Request $request, string $date_in): JsonResponse
    {
        try {
            if ($date_in === 'yesterday') {
                $date_in = Carbon::yesterday()->format('Y-m-d');
            }
            $date = Carbon::createFromFormat('Y-m-d', $date_in);
            return response()->json($this->statement_search_service->totalForDate($date));
        } catch (Exception $e) {
            return response()->json(['error' => 'invalid date total attempt, see logs.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function dateTotalRange(Request $request, string $start_in, string $end_in): JsonResponse
    {
        try {

            $dates = $this->sanitizeDateStartEndStrings($start_in, $end_in);

            return response()->json($this->statement_search_service->totalForDateRange($dates['start'], $dates['end']));
        } catch (Exception $e) {
            return response()->json(['error' => 'invalid date total range attempt, see logs. ' . $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    private function sanitizeDateString(string $date_in): bool|Carbon
    {
        try {
            if ($date_in === 'start') {
                $date_in = (string)config('dsa.start_date');
            }

            if ($date_in === 'yesterday') {
                $date_in = Carbon::yesterday()->format('Y-m-d');
            }

            $date = Carbon::createFromFormat('Y-m-d', $date_in);
            $date->subSeconds($date->secondsSinceMidnight());

            return $date;
        } catch (Exception $e) {
            throw new RuntimeException("Can't sanitize this date: '".$date_in."'");
        }
    }

    private function sanitizeDateStartEndStrings(string $start_in, string $end_in, bool $range = false): array
    {
        try {
            $start = $this->sanitizeDateString($start_in);
            $end   = $this->sanitizeDateString($end_in);
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage());
        }

        $end->subSeconds($end->secondsSinceMidnight());

        if ($range) {
            $end->addDay()->subSecond();
        }

        return ['start' => $start, 'end' => $end];
    }

    private function sanitizeAttributes(string $attributes_in, bool $remove_received_date = false): array
    {
        $attributes = explode("__", $attributes_in);
        if ($attributes[0] === 'all') {
            $attributes = $this->statement_search_service->getAllowedAggregateAttributes($remove_received_date);
        }
        return $attributes;
    }
}