<?php

namespace Tests\Feature\Services;

use App\Services\PlatformQueryService;
use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Override;
use Tests\TestCase;

class PlatformQueryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PlatformQueryService $platformQueryService;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->platformQueryService = app(PlatformQueryService::class);
        $this->assertNotNull($this->platformQueryService);
    }

    public function test_it_queries_on_s(): void
    {
        $filters = [];
        $filters['s'] = 'zaphod';
        $query = $this->platformQueryService->query($filters);
        $sql = $query->toRawSql();
        $this->assertStringContainsString('"name" like \'%zaphod%\'', $sql);
    }

    public function test_it_uses_case_insensitive_search_on_postgres(): void
    {
        $connection = DB::connection();
        $originalGrammar = $connection->getQueryGrammar();

        try {
            $connection->setQueryGrammar(new PostgresGrammar($connection));

            $query = $this->platformQueryService->query(['s' => 'zaphod']);

            $this->assertStringContainsString('"name"::text ilike \'%zaphod%\'', $query->toRawSql());
        } finally {
            $connection->setQueryGrammar($originalGrammar);
        }
    }

    public function test_it_queries_on_has_tokens(): void
    {
        $filters = [];
        $filters['has_tokens'] = 0;
        $query = $this->platformQueryService->query($filters);
        $sql = $query->toRawSql();
        $this->assertStringContainsString('"has_tokens" = 0', $sql);

        $filters = [];
        $filters['has_tokens'] = 1;
        $query = $this->platformQueryService->query($filters);
        $sql = $query->toRawSql();
        $this->assertStringContainsString('"has_tokens" = 1', $sql);

        $filters = [];
        $filters['has_tokens'] = 'cleveland';
        $query = $this->platformQueryService->query($filters);
        $sql = $query->toRawSql();
        $this->assertStringNotContainsString('"has_tokens"', $sql);
    }

    public function test_it_queries_on_has_statements(): void
    {
        $filters = [];
        $filters['has_statements'] = 0;
        $query = $this->platformQueryService->query($filters);
        $sql = $query->toRawSql();
        $this->assertStringContainsString('"has_statements" = 0', $sql);

        $filters = [];
        $filters['has_statements'] = 1;
        $query = $this->platformQueryService->query($filters);
        $sql = $query->toRawSql();
        $this->assertStringContainsString('"has_statements" = 1', $sql);

        $filters = [];
        $filters['has_statements'] = 'cleveland';
        $query = $this->platformQueryService->query($filters);
        $sql = $query->toRawSql();
        $this->assertStringNotContainsString('"has_statements"', $sql);
    }

    public function test_it_queries_on_vlop(): void
    {
        $filters = [];
        $filters['vlop'] = 0;
        $query = $this->platformQueryService->query($filters);
        $sql = $query->toRawSql();
        $this->assertStringContainsString('"vlop" = 0', $sql);

        $filters = [];
        $filters['vlop'] = 1;
        $query = $this->platformQueryService->query($filters);
        $sql = $query->toRawSql();
        $this->assertStringContainsString('"vlop" = 1', $sql);

        $filters = [];
        $filters['vlop'] = 'cleveland';
        $query = $this->platformQueryService->query($filters);
        $sql = $query->toRawSql();
        $this->assertStringNotContainsString('"vlop"', $sql);
    }

    public function test_it_queries_on_onboarded(): void
    {
        $filters = [];
        $filters['onboarded'] = 0;
        $query = $this->platformQueryService->query($filters);
        $sql = $query->toRawSql();
        $this->assertStringContainsString('"onboarded" = 0', $sql);

        $filters = [];
        $filters['onboarded'] = 1;
        $query = $this->platformQueryService->query($filters);
        $sql = $query->toRawSql();
        $this->assertStringContainsString('"onboarded" = 1', $sql);

        $filters = [];
        $filters['onboarded'] = 'cleveland';
        $query = $this->platformQueryService->query($filters);
        $sql = $query->toRawSql();
        $this->assertStringNotContainsString('"onboarded"', $sql);
    }

    public function test_it_gets_platform_dropdown_options(): void
    {
        $options = $this->platformQueryService->getPlatformDropDownOptions();
        $this->assertIsArray($options);
    }

    public function test_it_gets_platforms_by_id(): void
    {
        $platforms_by_id = $this->platformQueryService->getPlatformsById();
        $this->assertIsArray($platforms_by_id);
    }

    public function test_it_gets_platform_ids(): void
    {
        $platform_ids = $this->platformQueryService->getPlatformIds();
        $this->assertIsArray($platform_ids);
    }

    public function test_it_gets_platform_vlop_ids(): void
    {
        $platform_ids = $this->platformQueryService->getVlopPlatformIds();
        $this->assertIsArray($platform_ids);
    }
}
