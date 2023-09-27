<?php

namespace App\Services;

use App\Models\Platform;
use App\Models\Statement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Scout\Builder;
use OpenSearch\Client;
use OpenSearch\Endpoints\Search;


class StatementSearchService
{

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

    /**
     * @param array $filters
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
        $query = '*';

        foreach ($this->allowed_filters as $filter_key) {
            if (isset($filters[$filter_key]) && $filters[$filter_key]) {
                $method = sprintf('apply%sFilter', ucfirst(Str::camel($filter_key)));
                $part = false;
                if( method_exists($this,$method)) {
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
        $filter_value = preg_replace("/[^a-zA-Z0-9\ ]+/", "", $filter_value);
        $textfields = [
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
        foreach ($textfields as $textfield)
        {
            $ors[] = $textfield . ':"' . $filter_value . '"';
        }

        if (env('SCOUT_DRIVER', '') === 'database' && env('APP_ENV', '') !== 'testing') {
            return $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyDecisionVisibilityFilter(array $filter_values)
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::DECISION_VISIBILITIES));
        $ors = [];
        foreach ($filter_values as $filter_value)
        {
            $ors[] = 'decision_visibility:'.$filter_value;
        }
        return implode(' OR ', $ors);
    }

    private function applyDecisionMonetaryFilter(array $filter_values)
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::DECISION_MONETARIES));
        $ors = [];
        foreach ($filter_values as $filter_value)
        {
            $ors[] = 'decision_monetary:'.$filter_value;
        }
        return implode(' OR ', $ors);
    }

    private function applyDecisionProvisionFilter(array $filter_values)
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::DECISION_PROVISIONS));
        $ors = [];
        foreach ($filter_values as $filter_value)
        {
            $ors[] = 'decision_provision:'.$filter_value;
        }
        return implode(' OR ', $ors);
    }

    private function applyTerritorialScopeFilter(array $filter_values)
    {
        $filter_values = array_intersect($filter_values, EuropeanCountriesService::EUROPEAN_COUNTRY_CODES);
        $ors = [];
        foreach ($filter_values as $filter_value)
        {
            $ors[] = 'territorial_scope:'.$filter_value;
        }
        return implode(' OR ', $ors);
    }



    private function applyDecisionAccountFilter(array $filter_values)
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::DECISION_ACCOUNTS));
        $ors = [];
        foreach ($filter_values as $filter_value)
        {
            $ors[] = 'decision_account:'.$filter_value;
        }
        return implode(' OR ', $ors);
    }

    private function applyAccountTypeFilter(array $filter_values)
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::ACCOUNT_TYPES));
        $ors = [];
        foreach ($filter_values as $filter_value)
        {
            $ors[] = 'account_type:'.$filter_value;
        }
        return implode(' OR ', $ors);
    }

    private function applyCategorySpecificationFilter(array $filter_values)
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::KEYWORDS));

        $ors = [];
        foreach ($filter_values as $filter_value)
        {
            $ors[] = 'category_specification:'.$filter_value;
        }
        return implode(' OR ', $ors);
    }

    private function applyDecisionGroundFilter(array $filter_values)
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::DECISION_GROUNDS));
        $ors = [];
        foreach ($filter_values as $filter_value)
        {
            $ors[] = 'decision_ground:'.$filter_value;
        }
        return implode(' OR ', $ors);
    }

    private function applyCategoryFilter(array $filter_values)
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::STATEMENT_CATEGORIES));
        $ors = [];
        foreach ($filter_values as $filter_value)
        {
            $ors[] = 'category:'.$filter_value;
        }
        return implode(' OR ', $ors);
    }

    private function applyContentTypeFilter(array $filter_values)
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::CONTENT_TYPES));
        $ors = [];
        foreach ($filter_values as $filter_value)
        {
            $ors[] = 'content_type:'.$filter_value;
        }
        return implode(' OR ', $ors);
    }

    private function applyContentLanguageFilter(array $filter_values)
    {
        $ors = [];
        foreach ($filter_values as $filter_value)
        {
            $ors[] = 'content_language:"'.$filter_value.'"';
        }
        return implode(' OR ', $ors);
    }

    private function applyAutomatedDetectionFilter(array $filter_values)
    {
        $filter_values = array_intersect($filter_values, Statement::AUTOMATED_DETECTIONS);
        $ors = [];
        foreach ($filter_values as $filter_value)
        {
            $ors[] = 'automated_detection:' . ( $filter_value === Statement::AUTOMATED_DETECTION_YES ? 'true' : 'false' );
        }
        return implode(' OR ', $ors);
    }

    private function applyAutomatedDecisionFilter(array $filter_values)
    {
        $filter_values = array_intersect($filter_values, array_keys(Statement::AUTOMATED_DECISIONS));
        $ors = [];
        foreach ($filter_values as $filter_value)
        {
            $ors[] = 'automated_decision:'.$filter_value;
        }
        return implode(' OR ', $ors);

    }

    private function applyPlatformIdFilter(array $filter_values)
    {
        $ors = [];
        foreach ($filter_values as $filter_value)
        {
            $ors[] = 'platform_id:' . $filter_value;
        }
        return implode(' OR ', $ors);
    }
}
