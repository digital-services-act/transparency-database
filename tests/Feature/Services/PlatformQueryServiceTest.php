<?php

namespace Tests\Feature\Services;

use App\Exceptions\PuidNotUniqueSingleException;
use App\Models\PlatformPuid;
use App\Services\PlatformQueryService;
use App\Services\PlatformUniqueIdService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Override;
use Tests\TestCase;

class PlatformQueryServiceTest extends TestCase
{

    use RefreshDatabase;

    protected PlatformQueryService $platformQueryService;

    #[Override] protected function setUp(): void
    {
        parent::setUp();
        $this->platformQueryService = app(PlatformQueryService::class);
        $this->assertNotNull($this->platformQueryService);
    }


    /**
     * @return void
     * @test
     */
    public function it_queries_on_s(): void
    {
        $filters = [];
        $filters['s'] = 'zaphod';
        $query = $this->platformQueryService->query($filters);
        $sql = $query->toRawSql();
        $this->assertStringContainsString('"name" LIKE \'%zaphod%\'', $sql);
    }

    /**
     * @return void
     * @test
     */
    public function it_queries_on_has_tokens(): void
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

    /**
     * @return void
     * @test
     */
    public function it_queries_on_has_statements(): void
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

    /**
     * @return void
     * @test
     */
    public function it_queries_on_vlop(): void
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

    /**
     * @return void
     * @test
     */
    public function it_queries_on_onboarded(): void
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
}
