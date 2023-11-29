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
            return $this->client->sql()->explain($request->toArray());
        } catch (Exception $e) {
            Log::error('OpenSearch SQL Exception: ' . $e->getMessage());

            return response()->json(['error' => 'invalid query attempt'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
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
            $attributes = explode("__", $attributes_in);
            if ($attributes[0] === 'all') {
                $attributes = $this->statement_search_service->getAllowedAggregateAttributes(true);
            }

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
            $timestart = microtime(true);

            if ($start_in === 'start') {
                $start_in = Carbon::createFromDate(2023, 9, 25)->format('Y-m-d');
            }

            if ($end_in === 'yesterday') {
                $end_in = Carbon::yesterday()->format('Y-m-d');
            }

            $start      = Carbon::createFromFormat('Y-m-d', $start_in);
            $start->subSeconds($start->secondsSinceMidnight());

            $end        = Carbon::createFromFormat('Y-m-d', $end_in);
            $end->addDay()->subSeconds($end->secondsSinceMidnight()+1);

            if ($start >= $end || $end >= Carbon::today()) {
                throw new RuntimeException('Start must be less than end, and end must be in the past');
            }

            $attributes = explode("__", $attributes_in);
            if ($attributes[0] === 'all') {
                $attributes = $this->statement_search_service->getAllowedAggregateAttributes();
            }
            $this->statement_search_service->sanitizeAggregateAttributes($attributes);
            $key = $start->format('Y-m-d') . '__' . $end->format('Y-m-d') . '__' . implode('__', $attributes);

            if ((int)$request->query('cache', 1) === 0) {
                Cache::delete($key);
            }

            $cache   = 'hit';
            $results = Cache::rememberForever($key, function () use ($start, $end, $attributes, &$cache) {
                $query = $this->statement_search_service->aggregateQueryRange($start, $end, $attributes);
                $cache = 'miss';
                return $this->statement_search_service->processAggregateQuery($query);
            });

            $timeend = microtime(true);
            $timediff = $timeend - $timestart;


            $results['dates'] = [$start->format('Y-m-d'), $end->format('Y-m-d')];
            $results['attributes'] = $attributes;
            $results['key']   = $key;
            $results['cache'] = $cache;
            $results['duration'] = (float)number_format($timediff, 4);

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
            $timestart = microtime(true);

            if ($start_in === 'start') {
                $start_in = Carbon::createFromDate(2023, 9, 25)->format('Y-m-d');
            }

            if ($end_in === 'yesterday') {
                $end_in = Carbon::yesterday()->format('Y-m-d');
            }

            $start      = Carbon::createFromFormat('Y-m-d', $start_in);
            $start->subSeconds($start->secondsSinceMidnight());

            $end        = Carbon::createFromFormat('Y-m-d', $end_in);
            $end->addDay()->subSeconds($end->secondsSinceMidnight()+1);

            if ($start >= $end || $end >= Carbon::today()) {
                throw new RuntimeException('Start must be less than end, and end must be in the past');
            }

            $attributes = explode("__", $attributes_in);
            if ($attributes[0] === 'all') {
                $attributes = $this->statement_search_service->getAllowedAggregateAttributes();
            }
            $this->statement_search_service->sanitizeAggregateAttributes($attributes);
            $key = $start->format('Y-m-d') . '__' . $end->format('Y-m-d') . '__' . implode('__', $attributes);

            $caching = (bool)$request->query('cache', 1);
            if (!$caching) {
                Cache::delete($key);
            }

            $cache   = 'hit';
            $days = Cache::rememberForever($key, function () use ($start, $end, $attributes, $caching, &$cache) {
                $days = [];
                $current = $end->clone();
                while($current > $start) {
                    $days[] = $this->statement_search_service->processDateAggregate($current, $attributes, $caching);
                    $current->subDay();
                }
                $cache = 'miss';
                return $days;
            });

            $total = array_sum(array_map(function($day){
                return $day['total'];
            }, $days));

            $timeend = microtime(true);
            $timediff = $timeend - $timestart;

            $results['days'] = $days;
            $results['total'] = $total;
            $results['dates'] = [$start->format('Y-m-d'), $end->format('Y-m-d')];
            $results['attributes'] = $attributes;
            $results['key']   = $key;
            $results['cache'] = $cache;
            $results['duration'] = (float)number_format($timediff, 4);

            return response()->json($results);
        } catch (Exception $e) {
            Log::error('OpenSearch SQL Exception: ' . $e->getMessage());

            return response()->json(['error' => 'invalid aggregate range attempt, see logs.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse|array|null
     */
    public function platforms(Request $request): JsonResponse|array|null
    {
        try {
            $platforms = Platform::all()->pluck('name', 'id')->toArray();
            $out       = [];
            foreach ($platforms as $id => $name) {
                $out[] = [
                    'id'   => $id,
                    'name' => $name
                ];
            }

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
}