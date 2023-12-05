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
use OpenSearch\Client;
use RuntimeException;

class OpenSearchAPIController extends Controller
{
    private Client $client;
    private StatementSearchService $statement_search_service;
    private string $index_name;

    private int $error_code = Response::HTTP_UNPROCESSABLE_ENTITY;

    private int $response_size_limit = 5242880;

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
            return response()->json(['error' => 'invalid query attempt: ' . $e->getMessage()], $this->error_code);
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
            return response()->json(['error' => 'invalid count attempt: ' . $e->getMessage()], $this->error_code);
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
            return response()->json(['error' => 'invalid sql attempt: ' . $e->getMessage()], $this->error_code);
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
            return response()->json(['error' => 'invalid query attempt: ' . $e->getMessage()], $this->error_code);
        }
    }

    public function clearAggregateCache(): string
    {
        $this->statement_search_service->clearOSACache();

        return 'ok';
    }

    /**
     * @param string $date_in
     * @param string $attributes_in
     *
     * @return JsonResponse|array
     */
    public function aggregatesForDate(string $date_in, string $attributes_in = ''): JsonResponse|array
    {
        try {
            $date = $this->sanitizeDateString($date_in);

            if ($date >= Carbon::today()) {
                throw new RuntimeException('Aggregate date must be in the past.');
            }

            $attributes = $this->sanitizeAttributes($attributes_in, true);

            $results = $this->statement_search_service->processDateAggregate(
                $date,
                $attributes,
                $this->booleanizeQueryParam('cache')
            );

            $json = json_encode($results, JSON_THROW_ON_ERROR);
            $size = mb_strlen($json);
            $results['size'] = $size;

            if ($size > $this->response_size_limit) {
                return response()->json(['error' => 'Your request will return too much data (5mb), please ask less'], $this->error_code);
            }

            return response()->json($results);
        } catch (Exception $e) {
            return response()->json(['error' => 'invalid aggregates date attempt: ' . $e->getMessage()], $this->error_code);
        }
    }

    /**
     * @param string $start_in
     * @param string $end_in
     * @param string $attributes_in
     *
     * @return JsonResponse|array
     */
    public function aggregatesForRange(string $start_in, string $end_in, string $attributes_in = ''): JsonResponse|array
    {
        try {
            $dates = $this->sanitizeDateStartEndStrings($start_in, $end_in, true);

            $this->verifyStartEndOrderAndPast($dates['start'], $dates['end']);

            $attributes = $this->sanitizeAttributes($attributes_in);

            $results = $this->statement_search_service->processRangeAggregate(
                $dates['start'],
                $dates['end'],
                $attributes,
                $this->booleanizeQueryParam('cache')
            );

            $json = json_encode($results, JSON_THROW_ON_ERROR);
            $size = mb_strlen($json);
            $results['size'] = $size;

            if ($size > $this->response_size_limit) {
                return response()->json(['error' => 'Your request will return too much data (5mb), please ask less'], $this->error_code);
            }

            return response()->json($results);
        } catch (Exception $e) {
            return response()->json(['error' => 'invalid aggregates range attempt: ' . $e->getMessage()], $this->error_code);
        }
    }

    /**
     * @param string $start_in
     * @param string $end_in
     * @param string $attributes_in
     *
     * @return JsonResponse|array
     */
    public function aggregatesForRangeDates(string $start_in, string $end_in, string $attributes_in = ''): JsonResponse|array
    {
        try {
            $dates = $this->sanitizeDateStartEndStrings($start_in, $end_in);

            $this->verifyStartEndOrderAndPast($dates['start'], $dates['end']);

            $attributes = $this->sanitizeAttributes($attributes_in);

            $results = $this->statement_search_service->processDatesAggregate(
                $dates['start'],
                $dates['end'],
                $attributes,
                $this->booleanizeQueryParam('cache'),
                $this->booleanizeQueryParam('daycache')
            );

            $json = json_encode($results, JSON_THROW_ON_ERROR);
            $size = mb_strlen($json);
            $results['size'] = $size;

            if ($size > $this->response_size_limit) {
                return response()->json(['error' => 'Your request will return too much data (5mb), please ask less'], $this->error_code);
            }

            return response()->json($results);
        } catch (Exception $e) {
            return response()->json(['error' => 'invalid aggregates range dates attempt: ' . $e->getMessage()], $this->error_code);
        }
    }

    /**
     *
     * @return JsonResponse|array
     */
    public function platforms(): JsonResponse|array
    {
        try {
            $platforms = Platform::all()->pluck('name', 'id')->toArray();
            $out       = array_map(static function ($id, $name) {
                return ['id' => $id, 'name' => $name];
            }, array_keys($platforms), array_values($platforms));

            return response()->json(['platforms' => $out]);
        } catch (Exception $e) {
            return response()->json(['error' => 'invalid platforms attempt: ' . $e->getMessage()], $this->error_code);
        }
    }

    /**
     *
     * @return JsonResponse|array
     */
    public function labels(): JsonResponse|array
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
            return response()->json(['error' => 'invalid labels attempt: ' . $e->getMessage()], $this->error_code);
        }
    }

    public function total(): JsonResponse
    {
        return response()->json($this->statement_search_service->grandTotal());
    }

    public function dateTotal(string $date_in): JsonResponse
    {
        try {
            $date = $this->sanitizeDateString($date_in);

            return response()->json($this->statement_search_service->totalForDate($date));
        } catch (Exception $e) {
            return response()->json(['error' => 'invalid date total attempt: ' . $e->getMessage()], $this->error_code);
        }
    }

    public function platformDateTotal(string $platform_id_in, string $date_in): JsonResponse
    {
        try {
            $date = $this->sanitizeDateString($date_in);

            /** @var Platform $platform */
            $platform = Platform::find($platform_id_in);
            if (!$platform) {
                throw new RuntimeException('Platform not found');
            }

            return response()->json($this->statement_search_service->totalForPlatformDate($platform, $date));
        } catch (Exception $e) {
            return response()->json(['error' => 'invalid date total platform attempt: ' . $e->getMessage()], $this->error_code);
        }
    }

    public function dateTotalRange(string $start_in, string $end_in): JsonResponse
    {
        try {
            $dates = $this->sanitizeDateStartEndStrings($start_in, $end_in);

            return response()->json($this->statement_search_service->totalForDateRange($dates['start'], $dates['end']));
        } catch (Exception $e) {
            return response()->json(['error' => 'invalid date total range attempt: ' . $e->getMessage()], $this->error_code);
        }
    }

    public function dateTotalsRange(string $start_in, string $end_in): JsonResponse
    {
        try {
            $dates = $this->sanitizeDateStartEndStrings($start_in, $end_in);

            return response()->json($this->statement_search_service->datesTotalsForRange($dates['start'], $dates['end']));
        } catch (Exception $e) {
            return response()->json(['error' => 'invalid date totals range attempt: ' . $e->getMessage()], $this->error_code);
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

            if ($date_in === 'today') {
                $date_in = Carbon::today()->format('Y-m-d');
            }

            $date = Carbon::createFromFormat('Y-m-d', $date_in);
            $date->subSeconds($date->secondsSinceMidnight());

            return $date;
        } catch (Exception $e) {
            throw new RuntimeException("Can't sanitize this date: '" . $date_in . "' " . $e->getMessage());
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

    private function verifyStartEndOrderAndPast(Carbon $start, Carbon $end): void
    {
        if ($start >= $end || $end >= Carbon::today()) {
            throw new RuntimeException('Start must be less than end, and end must be in the past');
        }
    }

    private function booleanizeQueryParam(string $param): bool
    {
        return (bool)request($param, 1);
    }
}