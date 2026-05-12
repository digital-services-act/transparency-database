<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Statement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class BackfillAPIControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_last_imported_id_defaults_to_descending_boundary(): void
    {
        $this->configureBackfill('desc');
        $this->seedStatementsWithIds([120, 180, 250]);
        $this->signInAsAdmin();

        $response = $this->get(route('api.v1.backfill.last-imported-id'), [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'last_imported_id' => 120,
            'highest_imported_id' => 250,
            'lowest_id' => 120,
            'direction' => 'desc',
        ]);
    }

    public function test_last_imported_id_can_return_ascending_boundary(): void
    {
        $this->configureBackfill('asc');
        $this->seedStatementsWithIds([120, 180, 250]);
        $this->signInAsAdmin();

        $response = $this->get(route('api.v1.backfill.last-imported-id'), [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'last_imported_id' => 250,
            'highest_imported_id' => 250,
            'lowest_id' => 120,
            'direction' => 'asc',
        ]);
    }

    public function test_descending_empty_table_uses_end_id_boundary(): void
    {
        $this->configureBackfill('desc');
        Statement::query()->delete();
        $this->signInAsAdmin();

        $response = $this->get(route('api.v1.backfill.last-imported-id'), [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'last_imported_id' => 300,
            'highest_imported_id' => null,
            'lowest_id' => null,
            'direction' => 'desc',
        ]);
    }

    public function test_highest_imported_id_endpoint_ignores_descending_default(): void
    {
        $this->configureBackfill('desc');
        $this->seedStatementsWithIds([120, 180, 250]);
        $this->signInAsAdmin();

        $response = $this->get(route('api.v1.backfill.highest-imported-id'), [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'last_imported_id' => 250,
            'highest_imported_id' => 250,
            'lowest_id' => 120,
            'direction' => 'asc',
        ]);
    }

    private function configureBackfill(string $direction): void
    {
        config()->set('backfill.start_id', 100);
        config()->set('backfill.end_id', 300);
        config()->set('backfill.direction', $direction);
    }

    /**
     * @param  array<int, int>  $ids
     */
    private function seedStatementsWithIds(array $ids): void
    {
        Statement::query()->delete();

        foreach ($ids as $id) {
            Statement::factory()->create(['id' => $id]);
        }
    }
}
