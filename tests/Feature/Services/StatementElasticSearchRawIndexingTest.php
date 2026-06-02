<?php

namespace Tests\Feature\Services;

use App\Models\Platform;
use App\Models\Statement;
use App\Services\StatementElasticSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class StatementElasticSearchRawIndexingTest extends TestCase
{
    use RefreshDatabase;

    public function test_raw_statement_payload_matches_current_searchable_payload(): void
    {
        $statement = Statement::factory()->create();
        $service = app(StatementElasticSearchService::class);

        $rawStatement = $service->rawStatementRowsForIdRange($statement->id, $statement->id)->first();

        $this->assertSame(
            $this->normalize($statement->toSearchableArray()),
            $this->normalize($service->rawStatementRowToSearchableArray($rawStatement)),
        );
    }

    public function test_raw_statement_payload_matches_current_payload_for_deleted_platform(): void
    {
        $statement = Statement::factory()->create();
        Platform::query()->findOrFail($statement->platform_id)->delete();
        Cache::flush();

        $service = app(StatementElasticSearchService::class);
        $rawStatement = $service->rawStatementRowsForIdRange($statement->id, $statement->id)->first();

        $this->assertSame(
            $this->normalize($statement->toSearchableArray()),
            $this->normalize($service->rawStatementRowToSearchableArray($rawStatement)),
        );
    }

    private function normalize(array $payload): array
    {
        return json_decode(json_encode($payload, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }
}
