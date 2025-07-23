<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ApiLoggingTrait;
use App\Http\Controllers\Traits\ExceptionHandlingTrait;
use App\Models\Platform;
use App\Models\Statement;
use App\Services\StatementElasticSearchService;
use Elastic\Elasticsearch\Client;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use RuntimeException;

/**
 * @codeCoverageIgnore This is an admin WIP route and thus we are not going to worry about coverage
 */
class ElasticSearchAPIController extends Controller
{
    
    use ApiLoggingTrait;
    use ExceptionHandlingTrait;

    private Client $client;

    private string $index_name = 'statement_index';

    private int $error_code = Response::HTTP_UNPROCESSABLE_ENTITY;

    private int $response_size_limit = 5242880;

    public function __construct(private readonly StatementElasticSearchService $statement_elastic_search_service)
    {
        $this->client = $this->statement_elastic_search_service->client();
    }


    public function indices(Request $request): JsonResponse
    {
        return $this->handleApiOperation(
            $request,
            function () use ($request) {
                try {
                    $response = $this->client->cat()->indices([
                        'index' => '*',
                        'format' => 'json'
                    ])->asArray();
                    return response()->json($response);
                } catch (Exception $exception) {
                    return response()->json(['error' => 'failed to retrieve indices: ' . $exception->getMessage()], $this->error_code);
                }
            }
        );        
    }   
    
     


    
    /**
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        return $this->handleApiOperation(
            $request,
            function () use ($request) {
                try {
                    return response()->json($this->client->search([
                        'index' => $this->index_name,
                        'body' => $request->toArray(),
                    ])->asArray());
                } catch (Exception $exception) {
                    return response()->json(['error' => 'invalid query attempt: ' . $exception->getMessage()], $this->error_code);
                }
            }
        );
    }

    /**
     * @return JsonResponse
     */
    public function count(Request $request): JsonResponse
    {
        return $this->handleApiOperation(
            $request,
            function () use ($request) {
                try {
                    return response()->json($this->client->count([
                        'index' => $this->index_name,
                        'body' => $request->toArray(),
                    ])->asArray());
                } catch (Exception $exception) {
                    return response()->json(['error' => 'invalid count attempt: ' . $exception->getMessage()], $this->error_code);
                }
            }
        );
    }


    /**
     * @return JsonResponse
     */
    public function sql(Request $request): JsonResponse
    {
        return $this->handleApiOperation(
            $request,
            function () use ($request) {
                try {
                    $response = $this->client->sql()->query([
                        'body' => [
                            'query' => $request->input('query')
                        ]
                    ])->asArray();
                    return response()->json($response);
                } catch (Exception $exception) {
                    return response()->json(['error' => 'invalid sql attempt: ' . $exception->getMessage()], $this->error_code);
                }
            }
        );
    }

    public function lucene(Request $request): JsonResponse
    {
        return $this->handleApiOperation(
            $request,
            function () use ($request) {
                try {
                    $response = $this->client->search([
                        'index' => $this->index_name,
                        'body' => [
                            'query' => [
                                'query_string' => [
                                    'query' => $request->input('query')
                                ]
                            ]
                        ]
                    ])->asArray();
                    return response()->json($response);
                } catch (Exception $exception) {
                    return response()->json(['error' => 'invalid lucene query attempt: ' . $exception->getMessage()], $this->error_code);
                }
            }
        );
    }

    

    

    public function clearAggregateCache(): string
    {
        $this->statement_elastic_search_service->clearOSACache();

        return 'ok';
    }

    /**
     * @return JsonResponse|void
     */
    public function aggregatesCsvForDate(string $date_in)
    {
        try {
            $date = $this->sanitizeDateString($date_in);

            if ($date >= Carbon::today()) {
                throw new RuntimeException('Aggregate date must be in the past.');
            }

            $attributes = $this->sanitizeAttributes('all', false);

            $results = $this->statement_elastic_search_service->processDateAggregate(
                $date,
                $attributes,
                $this->booleanizeQueryParam('cache')
            );

            $headers = $this->statement_elastic_search_service->getAllowedAggregateAttributes(false);
            $headers[] = 'platform_name';
            $headers[] = 'total';
            $headers = array_diff($headers, ['platform_id']);

            $rows = [];
            foreach ($results['aggregates'] as $result) {
                $row = [];
                foreach ($headers as $header) {
                    $row[] = $result[$header];
                }

                $rows[] = $row;
            }

            header('Content-Type: text/csv; charset=utf-8');

            $out = fopen('php://output', 'wb');
            if (request('headers', true)) {
                fputcsv($out, $headers);
            }

            foreach ($rows as $row) {
                fputcsv($out, $row);
            }

            fclose($out);

        } catch (Exception $exception) {
            return response()->json(['error' => 'invalid aggregates csv date attempt: ' . $exception->getMessage()], $this->error_code);
        }
    }

    /**
     *
     * @return JsonResponse|array
     */
    public function aggregatesForDate(string $date_in, string $attributes_in = ''): JsonResponse
    {
        return $this->handleApiOperation(
            request(),
            function () use ($date_in, $attributes_in) {
                try {
                    $date = $this->sanitizeDateString($date_in);

                    if ($date >= Carbon::today()) {
                        throw new RuntimeException('Aggregate date must be in the past.');
                    }

                    $attributes = $this->sanitizeAttributes($attributes_in, true);

                    $results = $this->statement_elastic_search_service->processDateAggregate(
                        $date,
                        $attributes,
                        $this->booleanizeQueryParam('cache')
                    );

                    $json = json_encode($results, JSON_THROW_ON_ERROR);
                    $size = mb_strlen($json);

                    if ($size > $this->response_size_limit) {
                        throw new RuntimeException('Response size exceeds limit.');
                    }

                    return response()->json($results);
                } catch (Exception $exception) {
                    return response()->json(['error' => 'invalid aggregates date attempt: ' . $exception->getMessage()], $this->error_code);
                }
            }
        );
    }

    /**
     *
     * @return JsonResponse|array
     */
    public function aggregatesForRange(string $start_in, string $end_in, string $attributes_in = ''): JsonResponse|array
    {
        try {
            $dates = $this->sanitizeDateStartEndStrings($start_in, $end_in, true);

            $this->verifyStartEndOrderAndPast($dates['start'], $dates['end']);

            $attributes = $this->sanitizeAttributes($attributes_in);

            $results = $this->statement_elastic_search_service->processRangeAggregate(
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
        } catch (Exception $exception) {
            return response()->json(['error' => 'invalid aggregates range attempt: ' . $exception->getMessage()], $this->error_code);
        }
    }

    /**
     *
     * @return JsonResponse|array
     */
    public function aggregatesForRangeDates(string $start_in, string $end_in, string $attributes_in = ''): JsonResponse|array
    {
        try {
            $dates = $this->sanitizeDateStartEndStrings($start_in, $end_in);

            $this->verifyStartEndOrderAndPast($dates['start'], $dates['end']);

            $attributes = $this->sanitizeAttributes($attributes_in);

            $results = $this->statement_elastic_search_service->processDatesAggregate(
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
        } catch (Exception $exception) {
            return response()->json(['error' => 'invalid aggregates range dates attempt: ' . $exception->getMessage()], $this->error_code);
        }
    }

    /**
     *
     * @return JsonResponse|array
     */
    public function platforms(): JsonResponse
    {
        return $this->handleApiOperation(
            request(),
            function () {
                try {
                    $platforms = Platform::all(['id', 'name', 'vlop']);
                    return response()->json($platforms);
                } catch (Exception $exception) {
                    return response()->json(['error' => 'invalid platforms attempt: ' . $exception->getMessage()], $this->error_code);
                }
            }
        );
    }

    /**
     *
     * @return JsonResponse|array
     */
    public function labels(): JsonResponse
    {
        return $this->handleApiOperation(
            request(),
            function () {
                try {
                    return response()->json([
                        'decision_visibilities' => Statement::DECISION_VISIBILITIES,
                        'decision_monetaries' => Statement::DECISION_MONETARIES,
                        'decision_provisions' => Statement::DECISION_PROVISIONS,
                        'decision_accounts' => Statement::DECISION_ACCOUNTS,
                        'categories' => Statement::STATEMENT_CATEGORIES,
                        'decision_grounds' => Statement::DECISION_GROUNDS,
                        'automated_detections' => Statement::AUTOMATED_DETECTIONS,
                        'automated_decisions' => Statement::AUTOMATED_DECISIONS,
                        'content_types' => Statement::CONTENT_TYPES,
                        'source_types' => Statement::SOURCE_TYPES,
                    ]);
                } catch (Exception $exception) {
                    return response()->json(['error' => 'invalid labels attempt: ' . $exception->getMessage()], $this->error_code);
                }
            }
        );
    }

    public function total(): JsonResponse
    {
        return response()->json($this->statement_elastic_search_service->grandTotal());
    }

    public function dateTotal(string $date_in): JsonResponse
    {
        try {
            $date = $this->sanitizeDateString($date_in);

            return response()->json($this->statement_elastic_search_service->totalForDate($date));
        } catch (Exception $exception) {
            return response()->json(['error' => 'invalid date total attempt: ' . $exception->getMessage()], $this->error_code);
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

            return response()->json($this->statement_elastic_search_service->totalForPlatformDate($platform, $date));
        } catch (Exception $exception) {
            return response()->json(['error' => 'invalid date total platform attempt: ' . $exception->getMessage()], $this->error_code);
        }
    }

    public function dateTotalRange(string $start_in, string $end_in): JsonResponse
    {
        try {
            $dates = $this->sanitizeDateStartEndStrings($start_in, $end_in);

            return response()->json($this->statement_elastic_search_service->totalForDateRange($dates['start'], $dates['end']));
        } catch (Exception $exception) {
            return response()->json(['error' => 'invalid date total range attempt: ' . $exception->getMessage()], $this->error_code);
        }
    }

    public function dateTotalsRange(string $start_in, string $end_in): JsonResponse
    {
        try {
            $dates = $this->sanitizeDateStartEndStrings($start_in, $end_in);

            return response()->json($this->statement_elastic_search_service->datesTotalsForRange($dates['start'], $dates['end']));
        } catch (Exception $exception) {
            return response()->json(['error' => 'invalid date totals range attempt: ' . $exception->getMessage()], $this->error_code);
        }
    }

    private function sanitizeDateString(string $date_in): bool|Carbon
    {
        try {
            if ($date_in === 'start') {
                $date_in = (string) config('dsa.start_date');
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
        } catch (Exception $exception) {
            throw new RuntimeException("Can't sanitize this date: '" . $date_in . "' " . $exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    private function sanitizeDateStartEndStrings(string $start_in, string $end_in, bool $range = false): array
    {
        try {
            $start = $this->sanitizeDateString($start_in);
            $end = $this->sanitizeDateString($end_in);
        } catch (Exception $exception) {
            throw new RuntimeException($exception->getMessage(), $exception->getCode(), $exception);
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
            $attributes = $this->statement_elastic_search_service->getAllowedAggregateAttributes($remove_received_date);
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
        return (bool) request($param, 1);
    }
}