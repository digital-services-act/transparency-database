<?php

namespace App\Console\Commands;

use App\Models\Platform;
use App\Services\StatementElasticStatsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class EnrichHomePageCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrich-home-page-cache {--all} {--grandtotal} {--automateddecisionspercentage} {--topcategories} {--topdecisionsvisibility} {--platformstotal}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will run the function and such for the home page so that cache is loaded. To be run in vapor!';

    protected $one_day = 25 * 60 * 60;

    /**
     * Execute the console command.
     */
    public function handle(StatementElasticStatsService $statement_elastic_stats_service): void
    {
        if ($this->option('all') || $this->option('grandtotal')) {
            $this->doGrandTotal($statement_elastic_stats_service);
        }

        if ($this->option('all') || $this->option('platformstotal')) {
            $this->doPlatformsTotal();
        }

        if ($this->option('all') || $this->option('automateddecisionspercentage')) {
            $this->doFullyAutomatedDecisionPercentage($statement_elastic_stats_service);
        }

        if ($this->option('all') || $this->option('topcategories')) {
            $this->doTopCategories($statement_elastic_stats_service);
        }

        if ($this->option('all') || $this->option('topdecisionsvisibility')) {
            $this->doTopDecisionsVisibility($statement_elastic_stats_service);
        }
    }

    public function doGrandTotal(StatementElasticStatsService $statement_elastic_stats_service): void
    {
        Cache::put('grand_total', $statement_elastic_stats_service->grandTotalNoCache(), $this->one_day);
    }

    public function doPlatformsTotal(): void
    {
        Cache::put('platforms_total', max(1, Platform::nonDsa()->count()), $this->one_day);
    }

    public function doFullyAutomatedDecisionPercentage(StatementElasticStatsService $statement_elastic_stats_service): void
    {
        Cache::put('automated_decisions_percentage', $statement_elastic_stats_service->fullyAutomatedDecisionPercentageNoCache(), $this->one_day);
    }

    public function doTopCategories(StatementElasticStatsService $statement_elastic_stats_service): void
    {
        Cache::put('top_categories', $statement_elastic_stats_service->topCategoriesNoCache(), $this->one_day);
    }

    public function doTopDecisionsVisibility(StatementElasticStatsService $statement_elastic_stats_service): void
    {
        Cache::put('top_decisions_visibility', $statement_elastic_stats_service->topDecisionVisibilitiesNoCache(), $this->one_day);
    }
}
