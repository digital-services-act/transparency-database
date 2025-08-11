<?php

namespace App\Console\Commands;

use App\Services\StatementElasticSearchService;
use Illuminate\Console\Command;
use Symfony\Component\VarDumper\VarDumper;

/**
 * @codeCoverageIgnore
 */
class PidPuid2Ids extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pidpuid2ids {platform_id} {puid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the id for an uuid';

    /**
     * Execute the console command.
     */
    public function handle(StatementElasticSearchService $statement_elastic_search_service): void
    {
        $platform_id = $this->intifyArgument('platform_id');
        $puid = $this->argument('puid');
        VarDumper::dump($statement_elastic_search_service->PlatformIdPuidToIds($platform_id, $puid));
    }
}
