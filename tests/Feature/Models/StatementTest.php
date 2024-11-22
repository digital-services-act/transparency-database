<?php

namespace Tests\Feature\Models;

use App\Models\Statement;
use App\Models\Platform;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StatementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     * @test
     */
    public function territorial_scope_is_always_an_array(): void
    {

        $statement = Statement::all()->random()->first();
        $this->assertIsArray($statement->territorial_scope);
        $this->assertNotCount(0, $statement->territorial_scope);

        $statement->territorial_scope = null;
        $statement->save();
        $statement->refresh();

        $this->assertIsArray($statement->territorial_scope);
        $this->assertCount(0, $statement->territorial_scope);

        // empty array
        $statement->territorial_scope = [];
        $statement->save();
        $statement->refresh();

        $this->assertIsArray($statement->territorial_scope);
        $this->assertCount(0, $statement->territorial_scope);


        // very bad json
        $statement->territorial_scope = 'hello mr. fox';
        $statement->save();
        $statement->refresh();

        $this->assertIsArray($statement->territorial_scope);
        $this->assertCount(0, $statement->territorial_scope);
    }

    /**
     * @return void
     * @test
     */
    public function territorial_scope_is_always_sorted(): void
    {

        $statement = Statement::all()->random()->first();

        // Store in non alpha order
        $statement->territorial_scope = ['SK', 'BE', 'AU'];
        $statement->save();
        $statement->refresh();

        // Get it back in alpha order
        $territorial_scope = $statement->territorial_scope;
        $this->assertEquals(["AU", "BE", "SK"], $territorial_scope);
    }

    /**
     * @return void
     * @test
     */
    public function territorial_scope_is_always_unique(): void
    {
        $statement = Statement::all()->random()->first();

        // Store in non alpha order
        $statement->territorial_scope = ['SK', 'BE', 'AU', 'SK', 'BE', 'AU', 'SK', 'BE', 'AU', 'SK', 'BE', 'AU', 'AU', 'AU', 'AU'];
        $statement->save();
        $statement->refresh();


        // Get it back in alpha order
        $territorial_scope = $statement->territorial_scope;
        $this->assertEquals(["AU", "BE", "SK"], $territorial_scope);
    }

    /**
     * @test
     */
    public function it_generates_uuid_on_creation(): void
    {
        $statement = Statement::factory()->create();
        $this->assertNotNull($statement->uuid);
        $this->assertTrue(Str::isUuid($statement->uuid));
    }

    /**
     * @test
     */
    public function it_has_correct_relationships(): void
    {
        $statement = Statement::factory()->create();
        
        $this->assertInstanceOf(BelongsTo::class, $statement->user());
        $this->assertInstanceOf(HasOne::class, $statement->platform());
    }

    /**
     * @test
     */
    public function it_casts_attributes_correctly(): void
    {
        $statement = Statement::factory()->create([
            'content_date' => now(),
            'application_date' => now(),
            'end_date_account_restriction' => now(),
            'end_date_monetary_restriction' => now(),
            'end_date_service_restriction' => now(),
            'end_date_visibility_restriction' => now(),
            'territorial_scope' => ['US', 'EU'],
            'content_type' => ['TEXT', 'IMAGE'],
            'decision_visibility' => ['REMOVED'],
            'category_addition' => ['EXTRA'],
            'category_specification' => ['SPEC']
        ]);

        $this->assertIsString($statement->uuid);
        $this->assertInstanceOf(Carbon::class, $statement->content_date);
        $this->assertInstanceOf(Carbon::class, $statement->application_date);
        $this->assertInstanceOf(Carbon::class, $statement->end_date_account_restriction);
        $this->assertInstanceOf(Carbon::class, $statement->end_date_monetary_restriction);
        $this->assertInstanceOf(Carbon::class, $statement->end_date_service_restriction);
        $this->assertInstanceOf(Carbon::class, $statement->end_date_visibility_restriction);
        $this->assertIsArray($statement->territorial_scope);
        $this->assertIsArray($statement->content_type);
        $this->assertIsArray($statement->decision_visibility);
        $this->assertIsArray($statement->category_addition);
        $this->assertIsArray($statement->category_specification);
    }

    /**
     * @test
     */
    public function it_handles_platform_name_caching(): void
    {
        $platform = Platform::factory()->create(['name' => 'Test Platform']);
        $statement = Statement::factory()->create(['platform_id' => $platform->id]);

        $this->assertEquals('Test Platform', $statement->platformNameCached());
        
        // Test cache hit
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn('Test Platform');
            
        $statement->platformNameCached();
    }

    /**
     * @test
     */
    public function it_generates_correct_permalink_and_self_urls(): void
    {
        $statement = Statement::factory()->create();
        
        $this->assertEquals(
            route('statement.show', [$statement]),
            $statement->permalink
        );
        
        $this->assertEquals(
            route('api.v' . config('app.api_latest') . '.statement.show', [$statement]),
            $statement->self
        );
    }

    /**
     * @test
     */
    public function it_formats_restrictions_correctly(): void
    {
        $statement = Statement::factory()->create([
            'decision_visibility' => ['REMOVED'],
            'decision_monetary' => 'DECISION_MONETARY_SUSPENSION',
            'decision_provision' => null,
            'decision_account' => null,
            'automated_detection' => Statement::AUTOMATED_DETECTION_YES
        ]);

        $statement->refresh();
        $this->assertEquals('Visibility, Monetary', $statement->restrictions());
    }

    /**
     * @test
     */
    public function it_handles_invalid_json_in_raw_keys(): void
    {
        $statement = Statement::factory()->create();
        
        // Test with invalid JSON
        $statement->territorial_scope = 'invalid-json';
        $statement->save();
        
        $this->assertIsArray($statement->territorial_scope);
        $this->assertEmpty($statement->territorial_scope);
    }

    /**
     * @test
     */
    public function it_converts_enum_values_correctly(): void
    {
        // Test valid keys
        $values = Statement::getEnumValues([
            'AUTOMATED_DETECTION_YES',
            'AUTOMATED_DETECTION_NO'
        ]);

        $this->assertEquals([
            Statement::AUTOMATED_DETECTION_NO,
            Statement::AUTOMATED_DETECTION_YES
        ], $values);

        // Test with invalid key - should be silently ignored due to try-catch
        $values = Statement::getEnumValues([
            'AUTOMATED_DETECTION_YES',
            'DOES_NOT_EXIST'
        ]);

        $this->assertEquals([
            Statement::AUTOMATED_DETECTION_YES
        ], $values);

        // Test with empty array
        $values = Statement::getEnumValues([]);
        $this->assertEquals([], $values);

        // Test with null values in array
        $values = Statement::getEnumValues([null, 'AUTOMATED_DETECTION_YES', null]);
        $this->assertEquals([
            Statement::AUTOMATED_DETECTION_YES
        ], $values);
    }

    /**
     * @test
     */
    public function it_prepares_searchable_array_correctly(): void
    {
        $statement = Statement::factory()->create([
            'decision_visibility' => ['REMOVED'],
            'content_type' => ['TEXT', 'IMAGE'],
            'automated_detection' => Statement::AUTOMATED_DETECTION_YES
        ]);

        $searchable = $statement->toSearchableArray();

        $this->assertArrayHasKey('id', $searchable);
        $this->assertArrayHasKey('decision_visibility', $searchable);
        $this->assertArrayHasKey('content_type', $searchable);
        $this->assertArrayHasKey('automated_detection', $searchable);
        $this->assertTrue($searchable['automated_detection']);
    }

    /**
     * @test
     */
    public function it_handles_platform_uuid_caching(): void
    {
        $platform = Platform::factory()->create(['uuid' => Str::uuid()]);
        $statement = Statement::factory()->create(['platform_id' => $platform->id]);

        $this->assertEquals($platform->uuid, $statement->platformUuidCached());
        
        // Test cache hit
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn($platform->uuid);
            
        $statement->platformUuidCached();
    }

    /**
     * @test
     */
    public function it_prepares_syncable_array_correctly(): void
    {
        $statement = Statement::factory()->create([
            'decision_visibility' => ['REMOVED'],
            'content_type' => ['TEXT', 'IMAGE'],
            'automated_detection' => Statement::AUTOMATED_DETECTION_YES
        ]);

        $syncable = $statement->toSyncableArray();

        $this->assertArrayHasKey('id', $syncable);
        $this->assertArrayHasKey('uuid', $syncable);
        $this->assertArrayHasKey('platform_id', $syncable);
        $this->assertArrayHasKey('decision_visibility', $syncable);
        $this->assertArrayHasKey('content_type', $syncable);
        $this->assertArrayHasKey('automated_detection', $syncable);
    }

    /**
     * @test
     */
    public function it_handles_scout_key_methods(): void
    {
        $statement = Statement::factory()->create();
        
        $this->assertEquals($statement->id, $statement->getScoutKey());
        $this->assertEquals('id', $statement->getScoutKeyName());
    }

    /**
     * @test
     */
    public function it_handles_platform_attributes_when_platform_missing(): void
    {
        // Create a statement without platform_id
        $statement = Statement::factory()->make(['platform_id' => null]);
        
        $this->assertEquals('', $statement->platform_name);
        $this->assertEquals('deleted-uuid-', $statement->platformUuidCached());
        $this->assertEquals('deleted-name-', $statement->platformNameCached());
    }

    /**
     * @test
     */
    public function it_handles_searchable_index_name(): void
    {
        $statement = Statement::factory()->create();
        
        $this->assertEquals('statement_index', $statement->searchableAs());
    }

    /**
     * @test
     */
    public function it_handles_non_array_json_decode_result(): void
    {
        $statement = Statement::factory()->create();

        // Test with JSON that decodes to a string
        $statement->territorial_scope = '"single_value"';
        $statement->save();
        $statement->refresh();
        
        $this->assertIsArray($statement->territorial_scope);
        $this->assertEmpty($statement->territorial_scope);

        // Test with JSON that decodes to a number
        $statement->territorial_scope = '123';
        $statement->save();
        $statement->refresh();
        
        $this->assertIsArray($statement->territorial_scope);
        $this->assertEmpty($statement->territorial_scope);

        // Test with JSON that decodes to a boolean
        $statement->territorial_scope = 'true';
        $statement->save();
        $statement->refresh();
        
        $this->assertIsArray($statement->territorial_scope);
        $this->assertEmpty($statement->territorial_scope);

        // Test with JSON that decodes to an object
        $statement->territorial_scope = '{"key": "value"}';
        $statement->save();
        $statement->refresh();
        
        $this->assertIsArray($statement->territorial_scope);
        $this->assertEmpty($statement->territorial_scope);
    }

    public function test_it_handles_non_array_json_decode_result()
    {
        $statement = Statement::factory()->create();
        $result = $statement->getRawKeys('uuid');
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_it_handles_null_key_result()
    {
        $statement = Statement::factory()->create();
        $result = $statement->getRawKeys('something_that_does_not_exist');
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
