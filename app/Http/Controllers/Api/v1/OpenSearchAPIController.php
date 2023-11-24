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
use JsonException;
use OpenSearch\Client;
use stdClass;

class OpenSearchAPIController extends Controller
{
    private Client $client;
    private string $index_name;

    public function __construct(Client $client)
    {
        $this->client     = $client;
        $this->index_name = 'statement_' . config('app.env');
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
    public function aggregates(Request $request, string $date_in, string $attributes_in = null): JsonResponse|array
    {
        try {


            $start = Carbon::createFromFormat('Y-m-d', $date_in);
            $end = $start->clone();
            $attributes = explode("__", $attributes_in);
            $query = $this->aggregateQuery($start, $end, $attributes);

            $results = $this->processAggregateQuery($query);

            return response()->json($results);

        } catch (Exception $e) {
            Log::error('OpenSearch SQL Exception: ' . $e->getMessage());
            return response()->json(['error' => 'invalid query attempt'], Response::HTTP_UNPROCESSABLE_ENTITY);
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
    public function aggregatesRange(Request $request, string $start_in, string $end_in, string $attributes_in = null): JsonResponse|array
    {
        try {

            $start = Carbon::createFromFormat('Y-m-d', $start_in);
            $end = Carbon::createFromFormat('Y-m-d', $end_in);
            $attributes = explode("__", $attributes_in);
            $query = $this->aggregateQuery($start, $end, $attributes);

            $results = $this->processAggregateQuery($query);

            return response()->json($results);

        } catch (Exception $e) {
            Log::error('OpenSearch SQL Exception: ' . $e->getMessage());
            return response()->json(['error' => 'invalid query attempt'], Response::HTTP_UNPROCESSABLE_ENTITY);
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
            $platforms = Platform::Vlops()->pluck('name', 'id')->toArray();
            $out = [];
            foreach ($platforms as $id => $name)
            {
                $out[] = [
                    'id' => $id,
                    'name' => $name
                ];
            }
            return response()->json(['platforms' => $out]);
        } catch (Exception $e) {
            Log::error('OpenSearch SQL Exception: ' . $e->getMessage());

            return response()->json(['error' => 'invalid query attempt'], Response::HTTP_UNPROCESSABLE_ENTITY);
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

            return response()->json(['error' => 'invalid query attempt'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @param stdClass $query
     *
     * @return array
     */
    private function processAggregateQuery(stdClass $query): array
    {
        $result = $this->client->search([
            'index' => $this->index_name,
            'body'  => $query,
        ]);
        $buckets = $result['aggregations']['composite_buckets']['buckets'];
        $out = [];
        $total = 0;
        $total_aggregates = 0;
        foreach ($buckets as $bucket) {
            $item = [];
            $attributes = $bucket['key'];

            // Manipulate the results
            if (isset($attributes['automated_detection'])) {
                $attributes['automated_detection'] = (int)$attributes['automated_detection'];
            }

            if (isset($attributes['received_date'])) {
                $attributes['received_date'] = date( 'Y-m-d', ($attributes['received_date'] /  1000));
            }

            // Put the attributes on the root item
            foreach ($attributes as $key => $value) {
                $item[$key] = $value;
            }

            // build a permutation string
            $item['permutation'] = implode(',', array_map(function($key, $value) {
                return $key . ":" . $value;
            }, array_keys($attributes), array_values($attributes)));

            $item['total'] = $bucket['doc_count'];
            $total += $bucket['doc_count'];
            $total_aggregates++;
            $out[] = $item;
        }
        return ['aggregates' => $out, 'total' => $total, 'total_aggregates' => $total_aggregates];
    }


    /**
     * @throws JsonException
     */
    private function aggregateQuery(Carbon $start, Carbon $end, $attributes) {


        $allowed_attributes = [
            'platform_id',
            'category',
            'decision_visibility_single',
            'decision_monetary',
            'decision_provision',
            'decision_account',
            'decision_ground',
            'automated_detection',
            'automated_decision',
            'content_type_single',
            'source_type',
            'received_date'
        ];



        $query_string = <<<JSON
{
  "from": 0,
  "size": 0,
  "timeout": "1m",
  "query": {
    "bool": {
      "filter": [
        {
          "range": {
            "created_at": {
              "from": 1700092800000,
              "to": null,
              "include_lower": true,
              "include_upper": true,
              "boost": 1.0
            }
          }
        },
        {
          "range": {
            "created_at": {
              "from": null,
              "to": 1700179199000,
              "include_lower": true,
              "include_upper": true,
              "boost": 1.0
            }
          }
        }
      ],
      "adjust_pure_negative": true,
      "boost": 1.0
    }
  },
  "sort": [
    {
      "_doc": {
        "order": "asc"
      }
    }
  ],
  "aggregations": {
    "composite_buckets": {
      "composite": {
        "size": 1000,
        "sources": []
      },
      "aggregations": {
        "count(*)": {
          "value_count": {
            "field": "_index"
          }
        }
      }
    }
  }
}
JSON;
        $query = json_decode($query_string, false, 512, JSON_THROW_ON_ERROR);

        $start->hour = 0;
        $start->minute = 0;
        $start->second = 0;

        $end->hour = 23;
        $end->minute = 59;
        $end->second = 59;

        $query->query->bool->filter[0]->range->created_at->from = $start->getTimestampMs();
        $query->query->bool->filter[1]->range->created_at->to = $end->getTimestampMs();

        if (!is_array($attributes)) {
            $attributes = $allowed_attributes;
        }

        $sources = [];
        if (!in_array('platform_id', $attributes, true)) {
            $sources[] = $this->queryBucket('platform_id');
        }

        foreach ($attributes as $attribute) {
            if (in_array($attribute, $allowed_attributes, true)) {
                $sources[] = $this->queryBucket($attribute);
            }
        }

        $query->aggregations->composite_buckets->composite->sources = $sources;
        return $query;
    }

    private function queryBucket($attribute): stdClass
    {
        $source = new stdClass();
        $source->$attribute = new stdClass();
        $source->$attribute->terms = new stdClass();
        $source->$attribute->terms->field = $attribute;
        $source->$attribute->terms->missing_bucket = true;
        $source->$attribute->terms->missing_order = "first";
        $source->$attribute->terms->order = "asc";

        return $source;
    }
}