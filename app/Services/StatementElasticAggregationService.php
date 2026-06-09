<?php

namespace App\Services;

use Elastic\Elasticsearch\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use JsonException;
use RuntimeException;
use stdClass;

/**
 * @codeCoverageIgnore This service does Elasticsearch aggregation calls. Mocking the returns is not practical.
 */
class StatementElasticAggregationService
{
    private array $allowed_aggregate_attributes = [
        'automated_decision',
        'automated_detection',
        'category',
        'content_type_single',
        'decision_account',
        'decision_ground',
        'decision_monetary',
        'decision_provision',
        'decision_visibility_single',
        'platform_id',
        'received_date',
        'source_type',
    ];

    public function __construct(
        protected PlatformQueryService $platformQueryService,
        private readonly StatementElasticConnectionService $connectionService,
    ) {}

    private function client(): Client
    {
        return $this->connectionService->client();
    }

    private function indexName(): string
    {
        return $this->connectionService->statementIndexName();
    }

    public function pushESAKey($key): void
    {
        $keys = Cache::get('esa_cache', []);
        $keys[] = $key;
        Cache::forever('esa_cache', array_unique($keys));
    }

    public function clearESACache(): void
    {
        $keys = Cache::get('esa_cache', []);
        foreach ($keys as $key) {
            Cache::delete($key);
        }

        Cache::delete('esa_cache');
    }

    public function processRangeAggregate(Carbon $start, Carbon $end, array $attributes, bool $caching = true): array
    {
        $timestart = microtime(true);

        $this->sanitizeAggregateAttributes($attributes);
        $key = 'esar__'.$start->format('Y-m-d').'__'.$end->format('Y-m-d').'__'.implode('__', $attributes);

        if (! $caching) {
            Cache::delete($key);
        }

        $cache = 'hit';
        $results = Cache::rememberForever($key, function () use ($start, $end, $attributes, $key, &$cache) {
            $query = $this->aggregateQueryRange($start, $end, $attributes);
            $cache = 'miss';
            $this->pushESAKey($key);

            return $this->processAggregateQuery($query);
        });

        $timeend = microtime(true);
        $timediff = $timeend - $timestart;

        $results['dates'] = [$start->format('Y-m-d'), $end->format('Y-m-d')];
        $results['attributes'] = $attributes;
        $results['key'] = $key;
        $results['cache'] = $cache;
        $results['duration'] = (float) number_format($timediff, 4);

        return $results;
    }

    public function processDatesAggregate(Carbon $start, Carbon $end, array $attributes, bool $caching = true, bool $daycache = true): array
    {
        $timestart = microtime(true);

        $this->sanitizeAggregateAttributes($attributes);
        $key = 'esad__'.$start->format('Y-m-d').'__'.$end->format('Y-m-d').'__'.implode('__', $attributes);

        if (! $caching) {
            Cache::delete($key);
        }

        $cache = 'hit';
        $days = Cache::rememberForever($key, function () use ($start, $end, $attributes, $daycache, $key, &$cache) {
            $days = [];
            $current = $end->clone();

            while ($current >= $start) {
                $days[] = $this->processDateAggregate($current, $attributes, $daycache);
                $current->subDay();
            }

            $cache = 'miss';
            $this->pushESAKey($key);

            return $days;
        });

        $total = array_sum(array_map(static fn ($day) => $day['total'], $days));

        $timeend = microtime(true);
        $timediff = $timeend - $timestart;

        $results['days'] = $days;
        $results['total'] = $total;
        $results['dates'] = [$start->format('Y-m-d'), $end->format('Y-m-d')];
        $results['attributes'] = $attributes;
        $results['key'] = $key;
        $results['cache'] = $cache;
        $results['duration'] = (float) number_format($timediff, 4);

        return $results;
    }

    public function processDateAggregate(Carbon $date, array $attributes, bool $caching = true): array
    {
        $timestart = microtime(true);

        $this->sanitizeAggregateAttributes($attributes);
        $key = 'esa__'.$date->format('Y-m-d').'__'.implode('__', $attributes);

        if ($date > Carbon::yesterday()) {
            throw new RuntimeException('aggregates must done on dates in the past');
        }

        if (! $caching) {
            Cache::delete($key);
        }

        $cache = 'hit';
        $results = Cache::rememberForever($key, function () use ($date, $attributes, $key, &$cache) {
            $query = $this->aggregateQuerySingleDate($date, $attributes);
            $cache = 'miss';
            $this->pushESAKey($key);

            return $this->processAggregateQuery($query);
        });

        $timeend = microtime(true);
        $timediff = $timeend - $timestart;

        $results['date'] = $date->format('Y-m-d');
        $results['attributes'] = $attributes;
        $results['key'] = $key;
        $results['cache'] = $cache;
        $results['duration'] = (float) number_format($timediff, 4);

        return $results;
    }

    public function getAllowedAggregateAttributes(bool $remove_received_date = false): array
    {
        $out = $this->allowed_aggregate_attributes;
        if ($remove_received_date) {
            $out = array_diff($out, ['received_date']);
        }

        return $out;
    }

    /**
     * @throws JsonException
     */
    private function aggregateQueryRange(Carbon $start, Carbon $end, $attributes)
    {
        $query_string = <<<'JSON'
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
              "from": null,
              "to": null,
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
  "aggregations": {
    "composite_buckets": {
      "composite": {
        "size": 5000,
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
        $query->query->bool->filter[0]->range->created_at->to = $end->getTimestampMs();

        $sources = [];
        foreach ($attributes as $attribute) {
            $sources[] = $this->aggregateQueryBucket($attribute);
        }

        if ($sources === []) {
            $sources[] = $this->aggregateQueryBucket('received_date');
        }

        $query->aggregations->composite_buckets->composite->sources = $sources;

        return $query;
    }

    /**
     * @throws JsonException
     */
    private function aggregateQuerySingleDate(Carbon $date, $attributes)
    {
        $query_string = <<<'JSON'
{
  "from": 0,
  "size": 0,
  "timeout": "1m",
  "query": {
    "term": {
      "received_date": {
        "value": null,
        "boost": 1.0
      }
    }
  },
  "aggregations": {
    "composite_buckets": {
      "composite": {
        "size": 5000,
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

        $date->hour = 0;
        $date->minute = 0;
        $date->second = 0;

        $query->query->term->received_date->value = $date->getTimestampMs();

        $sources = [];
        foreach ($attributes as $attribute) {
            $sources[] = $this->aggregateQueryBucket($attribute);
        }

        if ($sources === []) {
            $sources[] = $this->aggregateQueryBucket('received_date');
        }

        $query->aggregations->composite_buckets->composite->sources = $sources;

        return $query;
    }

    private function aggregateQueryBucket($attribute): stdClass
    {
        $source = new stdClass;
        $source->$attribute = new stdClass;
        $source->$attribute->terms = new stdClass;
        $source->$attribute->terms->field = $attribute;
        $source->$attribute->terms->missing_bucket = true;
        $source->$attribute->terms->missing_order = 'first';
        $source->$attribute->terms->order = 'asc';

        return $source;
    }

    public function processAggregateQuery(stdClass $query): array
    {
        $result = $this->client()->search([
            'index' => $this->indexName(),
            'body' => $query,
        ])->asArray();
        $buckets = $result['aggregations']['composite_buckets']['buckets'];

        $platforms = [];
        // Do we need platforms
        if ($buckets[0]['key']['platform_id'] ?? false) {
            $platforms = $this->platformQueryService->getPlatformsById();
        }

        $out = [];
        $total = 0;
        $total_aggregates = 0;
        foreach ($buckets as $bucket) {
            $item = [];
            $attributes = $bucket['key'];

            // Manipulate the results
            if (isset($attributes['automated_detection'])) {
                $attributes['automated_detection'] = (int) $attributes['automated_detection'];
            }

            if (isset($attributes['received_date'])) {
                $attributes['received_date'] = date('Y-m-d', ($attributes['received_date'] / 1000));
            }

            // Put the attributes on the root item
            foreach ($attributes as $key => $value) {
                $item[$key] = $value;
            }

            // build a permutation string
            $item['permutation'] = implode(',', array_map(static fn ($key, $value) => $key.':'.$value, array_keys($attributes), array_values($attributes)));

            // add the platform name on at the end if we need to.
            if (isset($item['platform_id'])) {
                // yes we will need it.
                $item['platform_name'] = $platforms[$item['platform_id']] ?? '';
            }

            $item['total'] = $bucket['doc_count'];
            $total += $bucket['doc_count'];
            $total_aggregates++;
            $out[] = $item;
        }

        return ['aggregates' => $out, 'total' => $total, 'total_aggregates' => $total_aggregates];
    }

    public function sanitizeAggregateAttributes(array &$attributes): void
    {
        sort($attributes);
        $attributes = array_intersect($attributes, $this->allowed_aggregate_attributes);
        $attributes = array_unique($attributes);
        if ($attributes === []) {
            $attributes[] = 'received_date';
        }
    }
}
