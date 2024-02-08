<?php

namespace App\Console\Commands;

use App\Models\Platform;
use App\Services\StatementSearchService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class EnrichHomePageCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrich-home-page-cache {--invalidate} {--nobuild}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will run the function and such for the home page so that cache is loaded. To be run in vapor!';

    /**
     * Execute the console command.
     */
    public function handle(StatementSearchService $statement_search_service): void
    {
        $day = 60 * 60 * 24;

        if ($this->option('invalidate')) {
            $this->info('Invalidating home page cache');
            Cache::delete('platforms_total');
            Cache::delete('grand_total');
            Cache::delete('top_categories');
            Cache::delete('top_decisions_visibility');
            Cache::delete('automated_decisions_percentage');
        }

        if (!$this->option('nobuild')) {
            $this->info('Building home page cache');
            // Now run and reset them if needed.
            $statement_search_service->grandTotal();
            Cache::remember('platforms_total', $day, fn() => max(1, Platform::nonDsa()->count()));
            $statement_search_service->topCategories();
            $statement_search_service->topDecisionVisibilities();
            $statement_search_service->fullyAutomatedDecisionPercentage();
        }
    }
}
