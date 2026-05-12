<?php

namespace Tests\Feature\Services;

use App\Services\StatementBackfillTargetService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;
use Tests\CreatesApplication;

class StatementBackfillTargetServiceTest extends BaseTestCase
{
    use CreatesApplication;

    public function test_ascending_boundary_prefers_highest_imported_id(): void
    {
        $this->configureBackfillTarget();

        Http::fake([
            'https://target.test/api/v1/backfill/last-imported-id' => Http::response([
                'highest_imported_id' => 250,
                'lowest_id' => 100,
            ]),
        ]);

        $service = new StatementBackfillTargetService;

        $this->assertSame(250, $service->getImportedBoundaryId('asc'));
    }

    public function test_descending_boundary_prefers_lowest_id(): void
    {
        $this->configureBackfillTarget();

        Http::fake([
            'https://target.test/api/v1/backfill/last-imported-id' => Http::response([
                'highest_imported_id' => 250,
                'lowest_id' => 100,
            ]),
        ]);

        $service = new StatementBackfillTargetService;

        $this->assertSame(100, $service->getImportedBoundaryId('desc'));
    }

    private function configureBackfillTarget(): void
    {
        config()->set('backfill.base_url', 'https://target.test');
        config()->set('backfill.token', 'test-token');
        config()->set('backfill.last_imported_path', '/api/v1/backfill/last-imported-id');
    }
}
