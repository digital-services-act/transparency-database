<?php

namespace App\Services;

use App\Models\Platform;
use App\Models\Statement;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use JsonException;
use Laravel\Scout\Builder;
use OpenSearch\Client;
use Random\RandomException;
use RuntimeException;
use stdClass;


class StatementSearchService
{

    private Client $client;

    private string $index_name;

    // This service builds and does queries with elastic.
    // The elastic has to be setup and there needs to be a 'statements' index.
    // The index needs to have all the fields

    // These are the filters that we are allowed to filter on.
    // If there is to be a new filter, then add it here first and then make
    // a function. new_attribute -> applyNewAttributeFilter()

    private array $allowed_filters = [
        's',
        'decision_visibility',
        'decision_monetary',
        'decision_provision',
        'decision_account',
        'account_type',
        'decision_ground',
        'category',
        'content_type',
        'source_type',
        'content_language',
        'automated_detection',
        'automated_decision',
        'platform_id',
        'territorial_scope',
        'category_specification',
    ];

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

    public const ONE_DAY = 24 * 60 * 60;

    public function __construct(Client $client)
    {
        $this->client     = $client;
        $this->index_name = 'statement_index';
    }

    /**
     * @param array $filters
     * @param array $options
     *
     * @return Builder
     */
    public function query(array $filters, array $options = []): Builder
    {
        $query = $this->buildQuery($filters);

        return $this->basicQuery($query, $options);
    }

    private function basicQuery(string $query, array $options = []): Builder
    {
        return Statement::search($query)->options($options);
    }


    private function buildQuery(array $filters): string
    {
        $queryAndParts = [];
        $query         = '*';

        foreach ($this->allowed_filters as $filter_key) {
            if (isset($filters[$filter_key]) && $filters[$filter_key]) {
                $method = sprintf('apply%sFilter', ucfirst(Str::camel($filter_key)));
                $part   = false;
                if (method_exists($this, $method)) {
                    $part = $this->$method($filters[$filter_key]);
                }
                if ($part) {
                    $queryAndParts[] = $part;
                }
            }
        }

        // handle the date filters as needed.
        $created_at_filter = $this->applyCreatedAtFilter($filters);
        if ($created_at_filter) {
            $queryAndParts[] = $created_at_filter;
        }

        // if we have parts, then glue them together with AND
        if (count($queryAndParts)) {
            $query = "(" . implode(") AND (", $queryAndParts) . ")";
        }


        if (config('scout.driver', '') === 'database' && config('app.env') !== 'testing') {
            $query = $filters['s'] ?? '';
        }

        return $query;
    }

    private function applyCreatedAtFilter(array $filters): string
    {
        try {
            // Start but no end.
            if (($filters['created_at_start'] ?? false) && ! ($filters['created_at_end'] ?? false)) {
                $now   = date('Y-m-d\TH:i:s');
                $start = Carbon::createFromFormat('d-m-Y H:i:s', $filters['created_at_start'] . ' 00:00:00');

                return 'created_at:[' . $start->format('Y-m-d\TH:i:s') . ' TO ' . $now . ']';
            }

            // End but no start.
            if (($filters['created_at_end'] ?? false) && ! ($filters['created_at_start'] ?? false)) {
                $beginning = date('Y-m-d\TH:i:s', strtotime('2020-01-01'));
                $end       = Carbon::createFromFormat('d-m-Y H:i:s', $filters['created_at_end'] . ' 23:59:59');

                return 'created_at:[' . $beginning . ' TO ' . $end->format('Y-m-d\TH:i:s') . ']';
            }

            // both start and end.
            if (($filters['created_at_start'] ?? false) && ($filters['created_at_end'] ?? false)) {
                $start = Carbon::createFromFormat('d-m-Y H:i:s', $filters['created_at_start'] . ' 00:00:00');
                $end   = Carbon::createFromFormat('d-m-Y H:i:s', $filters['created_at_end'] . ' 23:59:59');

                return 'created_at:[' . $start->format('Y-m-d\TH:i:s') . ' TO ' . $end->format('Y-m-d\TH:i:s') . ']';
            }
        } catch (Exception $e) {
            // Most likely the date supplied for the start or the end was bad.
            return '';
        }

        // Normally we don't get here.
        return '';
    }

    /**
     * @param string $filter_value
     *
     * @return string
     */
    private function applySFilter(string $filter_value): string
    {
        $filter_value = preg_replace("/[^a-zA-Z0-9\ \-\_]+/", "", $filter_value);
        $textfields   = [
            'decision_visibility_other',
            'decision_monetary_other',
            'illegal_content_legal_ground',
            'illegal_content_explanation',
            'incompatible_content_ground',
            'incompatible_content_explanation',
            'decision_facts',
            'content_type_other',
            'source_identity',
            'uuid',
            'puid',
        ];

        $ors = [];
        foreach ($textfields as $textfield) {
            $ors[] = $textfield . ':"' . $filter_value . '"';
        }

        if (config('scout.driver', '') === 'database' && config('app.env', '') !== 'testing') {
            return $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyDecisionVisibilityFilter(array $filter_values): string
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::DECISION_VISIBILITIES));
        $ors           = [];
        foreach ($filter_values as $filter_value) {
            $ors[] = 'decision_visibility:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyDecisionMonetaryFilter(array $filter_values): string
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::DECISION_MONETARIES));
        $ors           = [];
        foreach ($filter_values as $filter_value) {
            $ors[] = 'decision_monetary:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyDecisionProvisionFilter(array $filter_values): string
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::DECISION_PROVISIONS));
        $ors           = [];
        foreach ($filter_values as $filter_value) {
            $ors[] = 'decision_provision:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyTerritorialScopeFilter(array $filter_values): string
    {
        $filter_values = array_intersect($filter_values, EuropeanCountriesService::EUROPEAN_COUNTRY_CODES);
        $ors           = [];
        foreach ($filter_values as $filter_value) {
            $ors[] = 'territorial_scope:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }


    private function applyDecisionAccountFilter(array $filter_values): string
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::DECISION_ACCOUNTS));
        $ors           = [];
        foreach ($filter_values as $filter_value) {
            $ors[] = 'decision_account:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyAccountTypeFilter(array $filter_values): string
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::ACCOUNT_TYPES));
        $ors           = [];
        foreach ($filter_values as $filter_value) {
            $ors[] = 'account_type:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyCategorySpecificationFilter(array $filter_values): string
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::KEYWORDS));

        $ors = [];
        foreach ($filter_values as $filter_value) {
            $ors[] = 'category_specification:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyDecisionGroundFilter(array $filter_values): string
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::DECISION_GROUNDS));
        $ors           = [];
        foreach ($filter_values as $filter_value) {
            $ors[] = 'decision_ground:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyCategoryFilter(array $filter_values): string
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::STATEMENT_CATEGORIES));
        $ors           = [];
        foreach ($filter_values as $filter_value) {
            $ors[] = 'category:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyContentTypeFilter(array $filter_values): string
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::CONTENT_TYPES));
        $ors           = [];
        foreach ($filter_values as $filter_value) {
            $ors[] = 'content_type:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyContentLanguageFilter(array $filter_values): string
    {
        $ors           = [];
        $all_isos      = array_keys(EuropeanLanguagesService::ALL_LANGUAGES);
        $filter_values = array_intersect($filter_values, $all_isos);
        foreach ($filter_values as $filter_value) {
            $ors[] = 'content_language:"' . $filter_value . '"';
        }

        return implode(' OR ', $ors);
    }

    private function applyAutomatedDetectionFilter(array $filter_values): string
    {
        $filter_values = array_intersect($filter_values, Statement::AUTOMATED_DETECTIONS);
        $ors           = [];
        foreach ($filter_values as $filter_value) {
            $ors[] = 'automated_detection:' . ($filter_value === Statement::AUTOMATED_DETECTION_YES ? 'true' : 'false');
        }

        return implode(' OR ', $ors);
    }

    private function applyAutomatedDecisionFilter(array $filter_values): string
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::AUTOMATED_DECISIONS));
        $ors           = [];
        foreach ($filter_values as $filter_value) {
            $ors[] = 'automated_decision:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    /**
     * @param array $filter_values
     *
     * @return string
     */
    private function applyPlatformIdFilter(array $filter_values): string
    {
        $ors           = [];
        $platform_ids  = Platform::nonDsa()->pluck('id')->toArray();
        $filter_values = array_intersect($platform_ids, $filter_values);
        foreach ($filter_values as $filter_value) {
            $ors[] = 'platform_id:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applySourceTypeFilter(array $filter_values): string
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::SOURCE_TYPES));
        $ors           = [];
        foreach ($filter_values as $filter_value) {
            $ors[] = 'source_type:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }


    public function bulkIndexStatements(Collection $statements): void
    {
        if ($statements->count()) {
            $bulk = [];
            /** @var Statement $statement */
            foreach ($statements as $statement) {
                $doc    = $statement->toSearchableArray();
                $bulk[] = json_encode([
                    'index' => [
                        '_index' => 'statement_index',
                        '_id'    => $statement->id
                    ]
                ], JSON_THROW_ON_ERROR);
                $bulk[] = json_encode($doc, JSON_THROW_ON_ERROR);
            }
            // Call the bulk and make them searchable.
            $this->client->bulk(['require_alias' => true, 'body' => implode("\n", $bulk)]);
        }
    }

    public function startCountQuery(): string
    {
        return "SELECT CAST(count(*) AS BIGINT) as count FROM " . $this->index_name;
    }

    public function extractCountQueryResult($result): int
    {
        return (int)($result['datarows'][0][0] ?? 0);
    }

    public function runSql(string $sql): array
    {
        if (config('scout.driver') === 'opensearch') {
            return $this->client->sql()->query([
                'query' => $sql
            ]);
        }

        return [
            'datarows' => [
                [
                    0
                ]
            ]
        ];
    }

    public function grandTotal(): int
    {
        return Cache::remember('grand_total', self::ONE_DAY, function () {
            $sql = $this->startCountQuery();
            return $this->extractCountQueryResult($this->runSql($sql));
        });
    }

    public function buildWheres(array $conditions): string
    {
        return " WHERE " . implode(" AND ", $conditions);
    }

    public function receivedDateCondition(Carbon $date): string
    {
        return "received_date = '" . $date->format('Y-m-d') . "'";
    }

    public function totalForDate(Carbon $date): int
    {
        $sql = $this->startCountQuery() . $this->buildWheres([$this->receivedDateCondition($date)]);
        return $this->extractCountQueryResult($this->runSql($sql));
    }

    public function totalForPlatformDate(Platform $platform, Carbon $date): int
    {
        $sql = $this->startCountQuery() . $this->buildWheres([
            "platform_id = " . $platform->id,
            $this->receivedDateCondition($date)
        ]);
        return $this->extractCountQueryResult($this->runSql($sql));
    }

    public function receivedDateRangeCondition(Carbon $start, Carbon $end): string
    {
        return "received_date BETWEEN '" . $start->format('Y-m-d') . "' AND '" . $end->format('Y-m-d') . "'";
    }

    public function totalForDateRange(Carbon $start, Carbon $end): int
    {
        $sql = $this->startCountQuery() . $this->buildWheres([$this->receivedDateRangeCondition($start, $end)]);
        return $this->extractCountQueryResult($this->runSql($sql));
    }

    public function datesTotalsForRange(Carbon $start, Carbon $end): array
    {
        $prepare = [];
        $current = $start->clone();
        while ($current <= $end) {
            $prepare[$current->format('Y-m-d')] = 0;
            $current->addDay();
        }

        $results = $this->processRangeAggregate($start, $end, ['received_date']);

        foreach ($results['aggregates'] as $aggregate) {
            $prepare[$aggregate['received_date']] = $aggregate['total'];
        }

        return array_map(static function ($date, $total) {
            return [
                'date'  => $date,
                'total' => $total
            ];
        }, array_keys($prepare), array_values($prepare));
    }

    /**
     * @return array
     */
    public function topCategories(): array
    {
        if (config('scout.driver') === 'opensearch') {
            return Cache::remember('top_categories', self::ONE_DAY, function () {
                $ten_days_ago = Carbon::now()->subDays(10);
                $sql          = "SELECT category, count(*) AS count FROM statement_index GROUP BY category ORDER BY count DESC";
                $result       = $this->runSql($sql);
                $datarows     = $result['datarows'];
                $out          = [];
                foreach ($datarows as $index => $row) {
                    $out[] = [
                        'value' => $row[0],
                        'total' => $row[1]
                    ];
                }

                return $out;
            });
        }

        try {
            return [
                [
                    'value' => 'STATEMENT_CATEGORY_ANIMAL_WELFARE',
                    'total' => random_int(100, 200)
                ],
                [
                    'value' => 'STATEMENT_CATEGORY_INTELLECTUAL_PROPERTY_INFRINGEMENTS',
                    'total' => random_int(100, 200)
                ],
                [
                    'value' => 'STATEMENT_CATEGORY_ILLEGAL_OR_HARMFUL_SPEECH',
                    'total' => random_int(100, 200)
                ]
            ];
        } catch (RandomException $re) {
            Log::error($re->getMessage());

            return [];
        }
    }

    /**
     * @return array
     */
    public function topDecisionVisibilities(): array
    {
        if (config('scout.driver') === 'opensearch') {
            return Cache::remember('top_decisions_visibility', self::ONE_DAY, function () {
                $ten_days_ago = Carbon::now()->subDays(10);

                $results = [];
                $decision_visibilities = array_keys(Statement::DECISION_VISIBILITIES);
                foreach ($decision_visibilities as $decision_visibility) {
                    $results[] = [
                        'value' => $decision_visibility,
                        'total' => $this->extractCountQueryResult($this->runSql($this->startCountQuery() . " WHERE decision_visibility_single = '".$decision_visibility."'"))
                    ];
                }
                uasort($results, function($a, $b){
                    return ($a['total'] <=> $b['total']) * -1;
                });

                return $results;
            });
        }

        try {
            return [
                [
                    'value' => 'DECISION_VISIBILITY_CONTENT_DEMOTED',
                    'total' => random_int(100, 200)
                ],
                [
                    'value' => 'DECISION_VISIBILITY_CONTENT_REMOVED',
                    'total' => random_int(100, 200)
                ],
                [
                    'value' => 'DECISION_VISIBILITY_CONTENT_DISABLED',
                    'total' => random_int(100, 200)
                ]
            ];
        } catch (RandomException $re) {
            Log::error($re->getMessage());

            return [];
        }
    }

    /**
     * @return int
     */
    public function fullyAutomatedDecisionPercentage(): int
    {
        if (config('scout.driver') === 'opensearch') {
            return Cache::remember('automated_decisions_percentage', self::ONE_DAY, function () {
                $ten_days_ago                 = Carbon::now()->subDays(10);
                $now                          = Carbon::now();
                $automated_decision_count_sql = $this->startCountQuery() .
                                                " WHERE automated_decision = 'AUTOMATED_DECISION_FULLY'";
                $automated_decision_count     = $this->extractCountQueryResult($this->runSql($automated_decision_count_sql));
                $total                        = $this->grandTotal();

                return (int)(($automated_decision_count / max(1, $total)) * 100);
            });
        }

        try {
            return random_int(0, 100);
        } catch (RandomException $re) {
            Log::error($re->getMessage());

            return 5;
        }
    }

    /**
     * @param $key
     *
     * @return void
     */
    public function pushOSAKey($key): void
    {
        $keys   = Cache::get('osa_cache', []);
        $keys[] = $key;
        Cache::forever('osa_cache', array_unique($keys));
    }

    /**
     * @return void
     */
    public function clearOSACache(): void
    {
        $keys = Cache::get('osa_cache', []);
        foreach ($keys as $key) {
            Cache::delete($key);
        }
        Cache::delete('osa_cache');
    }

    /**
     * @param Carbon $start
     * @param Carbon $end
     * @param array $attributes
     * @param bool $caching
     *
     * @return array
     */
    public function processRangeAggregate(Carbon $start, Carbon $end, array $attributes, bool $caching = true): array
    {
        $timestart = microtime(true);

        $this->sanitizeAggregateAttributes($attributes);
        $key = 'osar__' . $start->format('Y-m-d') . '__' . $end->format('Y-m-d') . '__' . implode('__', $attributes);

        if ( ! $caching) {
            Cache::delete($key);
        }

        $cache   = 'hit';
        $results = Cache::rememberForever($key, function () use ($start, $end, $attributes, $key, &$cache) {
            $query = $this->aggregateQueryRange($start, $end, $attributes);
            $cache = 'miss';
            $this->pushOSAKey($key);

            return $this->processAggregateQuery($query);
        });

        $timeend  = microtime(true);
        $timediff = $timeend - $timestart;


        $results['dates']      = [$start->format('Y-m-d'), $end->format('Y-m-d')];
        $results['attributes'] = $attributes;
        $results['key']        = $key;
        $results['cache']      = $cache;
        $results['duration']   = (float)number_format($timediff, 4);

        return $results;
    }

    public function processDatesAggregate(Carbon $start, Carbon $end, array $attributes, bool $caching = true, bool $daycache = true): array
    {
        $timestart = microtime(true);

        $this->sanitizeAggregateAttributes($attributes);
        $key = 'osad__' . $start->format('Y-m-d') . '__' . $end->format('Y-m-d') . '__' . implode('__', $attributes);

        if ( ! $caching) {
            Cache::delete($key);
        }

        $cache = 'hit';
        $days  = Cache::rememberForever($key, function () use ($start, $end, $attributes, $daycache, $key, &$cache) {
            $days    = [];
            $current = $end->clone();

            while ($current >= $start) {
                $days[] = $this->processDateAggregate($current, $attributes, $daycache);
                $current->subDay();
            }

            $cache = 'miss';
            $this->pushOSAKey($key);

            return $days;
        });

        $total = array_sum(array_map(static function ($day) {
            return $day['total'];
        }, $days));

        $timeend  = microtime(true);
        $timediff = $timeend - $timestart;

        $results['days']       = $days;
        $results['total']      = $total;
        $results['dates']      = [$start->format('Y-m-d'), $end->format('Y-m-d')];
        $results['attributes'] = $attributes;
        $results['key']        = $key;
        $results['cache']      = $cache;
        $results['duration']   = (float)number_format($timediff, 4);

        return $results;
    }

    public function processDateAggregate(Carbon $date, array $attributes, bool $caching = true): array
    {
        $timestart = microtime(true);

        $this->sanitizeAggregateAttributes($attributes);
        $key = 'osa__' . $date->format('Y-m-d') . '__' . implode('__', $attributes);

        if ($date > Carbon::yesterday()) {
            throw new RuntimeException('aggregates must done on dates in the past');
        }
        if ( ! $caching) {
            Cache::delete($key);
        }
        $cache   = 'hit';
        $results = Cache::rememberForever($key, function () use ($date, $attributes, $key, &$cache) {
            $query = $this->aggregateQuerySingleDate($date, $attributes);
            $cache = 'miss';
            $this->pushOSAKey($key);

            return $this->processAggregateQuery($query);
        });

        $timeend  = microtime(true);
        $timediff = $timeend - $timestart;

        $results['date']       = $date->format('Y-m-d');
        $results['attributes'] = $attributes;
        $results['key']        = $key;
        $results['cache']      = $cache;
        $results['duration']   = (float)number_format($timediff, 4);

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
        $query        = json_decode($query_string, false, 512, JSON_THROW_ON_ERROR);

        $start->hour   = 0;
        $start->minute = 0;
        $start->second = 0;

        $end->hour   = 23;
        $end->minute = 59;
        $end->second = 59;

        $query->query->bool->filter[0]->range->created_at->from = $start->getTimestampMs();
        $query->query->bool->filter[0]->range->created_at->to   = $end->getTimestampMs();

        $sources = [];
        foreach ($attributes as $attribute) {
            $sources[] = $this->aggregateQueryBucket($attribute);
        }

        if (count($sources) === 0) {
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
        $query_string = <<<JSON
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
        $query        = json_decode($query_string, false, 512, JSON_THROW_ON_ERROR);

        $date->hour   = 0;
        $date->minute = 0;
        $date->second = 0;

        $query->query->term->received_date->value = $date->getTimestampMs();

        $sources = [];
        foreach ($attributes as $attribute) {
            $sources[] = $this->aggregateQueryBucket($attribute);
        }

        if (count($sources) === 0) {
            $sources[] = $this->aggregateQueryBucket('received_date');
        }

        $query->aggregations->composite_buckets->composite->sources = $sources;

        return $query;
    }

    private function aggregateQueryBucket($attribute): stdClass
    {
        $source                                    = new stdClass();
        $source->$attribute                        = new stdClass();
        $source->$attribute->terms                 = new stdClass();
        $source->$attribute->terms->field          = $attribute;
        $source->$attribute->terms->missing_bucket = true;
        $source->$attribute->terms->missing_order  = "first";
        $source->$attribute->terms->order          = "asc";

        return $source;
    }

    /**
     * @param stdClass $query
     *
     * @return array
     */
    public function processAggregateQuery(stdClass $query): array
    {
        $result  = $this->client->search([
            'index' => $this->index_name,
            'body'  => $query,
        ]);
        $buckets = $result['aggregations']['composite_buckets']['buckets'];

        $platforms = [];
        // Do we need platforms
        if ($buckets[0]['key']['platform_id'] ?? false) {
            $platforms = Platform::all()->pluck('name', 'id')->toArray();
        }

        $out              = [];
        $total            = 0;
        $total_aggregates = 0;
        foreach ($buckets as $bucket) {
            $item       = [];
            $attributes = $bucket['key'];

            // Manipulate the results
            if (isset($attributes['automated_detection'])) {
                $attributes['automated_detection'] = (int)$attributes['automated_detection'];
            }

            if (isset($attributes['received_date'])) {
                $attributes['received_date'] = date('Y-m-d', ($attributes['received_date'] / 1000));
            }

            // Put the attributes on the root item
            foreach ($attributes as $key => $value) {
                $item[$key] = $value;
            }

            // build a permutation string
            $item['permutation'] = implode(',', array_map(static function ($key, $value) {
                return $key . ":" . $value;
            }, array_keys($attributes), array_values($attributes)));

            // add the platform name on at the end if we need to.
            if (isset($item['platform_id'])) {
                // yes we will need it.
                $item['platform_name'] = $platforms[$item['platform_id']] ?? '';
            }


            $item['total'] = $bucket['doc_count'];
            $total         += $bucket['doc_count'];
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
        if (count($attributes) === 0) {
            $attributes[] = 'received_date';
        }
    }

    public function statementIndexProperties(): array
    {
        return [
            'properties' =>
                [
                    'automated_decision'               =>
                        [
                            'type' => 'keyword'
                        ],
                    'automated_detection'              =>
                        [
                            'type' => 'boolean'
                        ],
                    'category'                         =>
                        [
                            'type' => 'keyword'
                        ],
                    'category_specification'           =>
                        [
                            'type' => 'text'
                        ],
                    'content_type'                     =>
                        [
                            'type' => 'text'
                        ],
                    'content_type_single'              =>
                        [
                            'type' => 'keyword'
                        ],
                    'content_type_other'               =>
                        [
                            'type' => 'text'
                        ],
                    'content_language'                 =>
                        [
                            'type' => 'keyword'
                        ],
                    'created_at'                       =>
                        [
                            'type' => 'date'
                        ],
                    'received_date'                    =>
                        [
                            'type' => 'date'
                        ],
                    'content_date'                     =>
                        [
                            'type' => 'date'
                        ],
                    'application_date'                 =>
                        [
                            'type' => 'date'
                        ],
                    'decision_account'                 =>
                        [
                            'type' => 'keyword'
                        ],
                    'account_type'                     =>
                        [
                            'type' => 'keyword'
                        ],
                    'decision_facts'                   =>
                        [
                            'type' => 'text'
                        ],
                    'decision_ground'                  =>
                        [
                            'type' => 'keyword'
                        ],
                    'decision_monetary'                =>
                        [
                            'type' => 'keyword'
                        ],
                    'decision_provision'               =>
                        [
                            'type' => 'keyword'
                        ],
                    'decision_visibility'              =>
                        [
                            'type' => 'text'
                        ],
                    'decision_visibility_single'       =>
                        [
                            'type' => 'keyword'
                        ],
                    'id'                               =>
                        [
                            'type' => 'long'
                        ],
                    'illegal_content_explanation'      =>
                        [
                            'type' => 'text'
                        ],
                    'illegal_content_legal_ground'     =>
                        [
                            'type' => 'text'
                        ],
                    'incompatible_content_explanation' =>
                        [
                            'type' => 'text'
                        ],
                    'incompatible_content_ground'      =>
                        [
                            'type' => 'text'
                        ],
                    'platform_id'                      =>
                        [
                            'type' => 'long',
                        ],
                    'platform_name'                    =>
                        [
                            'type' => 'text',
                        ],
                    'platform_uuid'                    =>
                        [
                            'type' => 'text',
                        ],
                    'source_identity'                  =>
                        [
                            'type' => 'text'
                        ],
                    'source_type'                      =>
                        [
                            'type' => 'keyword'
                        ],
                    'url'                              =>
                        [
                            'type' => 'text'
                        ],
                    'uuid'                             =>
                        [
                            'type' => 'text'
                        ],
                    'puid'                             =>
                        [
                            'type' => 'text'
                        ],
                    'decision_visibility_other'        =>
                        [
                            'type' => 'text'
                        ],
                    'decision_monetary_other'          =>
                        [
                            'type' => 'text'
                        ],
                    'territorial_scope'                =>
                        [
                            'type' => 'text'
                        ],
                    'method'                           =>
                        [
                            'type' => 'keyword'
                        ],
                ]
        ];
    }
}
