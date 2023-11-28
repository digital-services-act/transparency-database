<?php

namespace App\Services;

use App\Models\Platform;
use App\Models\Statement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use JsonException;
use Laravel\Scout\Builder;
use OpenSearch\Client;
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

    public function __construct(Client $client)
    {
        $this->client     = $client;
        $this->index_name = 'statement_' . config('app.env');
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
        } catch (\Exception $e) {
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

    private function applyDecisionVisibilityFilter(array $filter_values)
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::DECISION_VISIBILITIES));
        $ors           = [];
        foreach ($filter_values as $filter_value) {
            $ors[] = 'decision_visibility:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyDecisionMonetaryFilter(array $filter_values)
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::DECISION_MONETARIES));
        $ors           = [];
        foreach ($filter_values as $filter_value) {
            $ors[] = 'decision_monetary:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyDecisionProvisionFilter(array $filter_values)
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::DECISION_PROVISIONS));
        $ors           = [];
        foreach ($filter_values as $filter_value) {
            $ors[] = 'decision_provision:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyTerritorialScopeFilter(array $filter_values)
    {
        $filter_values = array_intersect($filter_values, EuropeanCountriesService::EUROPEAN_COUNTRY_CODES);
        $ors           = [];
        foreach ($filter_values as $filter_value) {
            $ors[] = 'territorial_scope:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }


    private function applyDecisionAccountFilter(array $filter_values)
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::DECISION_ACCOUNTS));
        $ors           = [];
        foreach ($filter_values as $filter_value) {
            $ors[] = 'decision_account:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyAccountTypeFilter(array $filter_values)
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::ACCOUNT_TYPES));
        $ors           = [];
        foreach ($filter_values as $filter_value) {
            $ors[] = 'account_type:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyCategorySpecificationFilter(array $filter_values)
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::KEYWORDS));

        $ors = [];
        foreach ($filter_values as $filter_value) {
            $ors[] = 'category_specification:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyDecisionGroundFilter(array $filter_values)
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::DECISION_GROUNDS));
        $ors           = [];
        foreach ($filter_values as $filter_value) {
            $ors[] = 'decision_ground:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyCategoryFilter(array $filter_values)
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::STATEMENT_CATEGORIES));
        $ors           = [];
        foreach ($filter_values as $filter_value) {
            $ors[] = 'category:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyContentTypeFilter(array $filter_values)
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::CONTENT_TYPES));
        $ors           = [];
        foreach ($filter_values as $filter_value) {
            $ors[] = 'content_type:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyContentLanguageFilter(array $filter_values)
    {
        $ors           = [];
        $all_isos      = array_keys(EuropeanLanguagesService::ALL_LANGUAGES);
        $filter_values = array_intersect($filter_values, $all_isos);
        foreach ($filter_values as $filter_value) {
            $ors[] = 'content_language:"' . $filter_value . '"';
        }

        return implode(' OR ', $ors);
    }

    private function applyAutomatedDetectionFilter(array $filter_values)
    {
        $filter_values = array_intersect($filter_values, Statement::AUTOMATED_DETECTIONS);
        $ors           = [];
        foreach ($filter_values as $filter_value) {
            $ors[] = 'automated_detection:' . ($filter_value === Statement::AUTOMATED_DETECTION_YES ? 'true' : 'false');
        }

        return implode(' OR ', $ors);
    }

    private function applyAutomatedDecisionFilter(array $filter_values)
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::AUTOMATED_DECISIONS));
        $ors           = [];
        foreach ($filter_values as $filter_value) {
            $ors[] = 'automated_decision:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyPlatformIdFilter(array $filter_values)
    {
        $ors           = [];
        $platform_ids  = Platform::nonDsa()->pluck('id')->toArray();
        $filter_values = array_intersect($platform_ids, $filter_values);
        foreach ($filter_values as $filter_value) {
            $ors[] = 'platform_id:' . $filter_value;
        }

        return implode(' OR ', $ors);
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
    public function aggregateQueryRange(Carbon $start, Carbon $end, $attributes)
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
            $sources[] = $this->queryBucket($attribute);
        }

        if (count($sources) === 0) {
            $sources[] = $this->queryBucket('received_date');
        }

        $query->aggregations->composite_buckets->composite->sources = $sources;

        return $query;
    }

    /**
     * @throws JsonException
     */
    public function aggregateQuerySingleDate(Carbon $date, $attributes)
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
        $query        = json_decode($query_string, false, 512, JSON_THROW_ON_ERROR);

        $date->hour   = 0;
        $date->minute = 0;
        $date->second = 0;

        $query->query->term->received_date->value = $date->getTimestampMs();

        $sources = [];
        foreach ($attributes as $attribute) {
            $sources[] = $this->queryBucket($attribute);
        }

        if (count($sources) === 0) {
            $sources[] = $this->queryBucket('received_date');
        }

        $query->aggregations->composite_buckets->composite->sources = $sources;

        return $query;
    }

    private function queryBucket($attribute): stdClass
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
            $item['permutation'] = implode(',', array_map(function ($key, $value) {
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
        if (count($attributes) === 0) {
            $attributes[] = 'received_date';
        }
    }
}
