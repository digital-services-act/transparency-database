<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Platform;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class PlatformAPIControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    private array $requiredFields;

    private Platform $platform;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->requiredFields = [
            'name' => 'New Platform',
            'vlop' => 0,
        ];
    }

    /**
     * @test
     */
    public function api_platform_store_requires_auth(): void
    {
        // Not signing in.
        $this->assertCount(20, Platform::all());

        $response = $this->post(route('api.v1.platform.store'), $this->requiredFields, [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @test
     */
    public function api_platform_store_works(): void
    {

        $user = $this->signInAsOnboarding();
        $this->withoutExceptionHandling();

        $this->assertCount(20, Platform::all());

        $this->requiredFields['dsa_common_id'] = '123-ABC-456';

        $response = $this->post(route('api.v1.platform.store'), $this->requiredFields, [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $createdPlatform = Platform::firstWhere('id', $response->json('id'));

        $this->assertEquals('123-ABC-456', $createdPlatform->dsa_common_id);
        $this->assertCount(21, Platform::all());
    }

    /**
     * @test
     */
    public function api_platform_store_handles_duplicate_platform(): void
    {
        $user = $this->signInAsOnboarding();

        // Create a platform with a specific name
        Platform::create([
            'name' => 'Duplicate Platform',
            'vlop' => 0,
            'dsa_common_id' => 'unique-id-123'
        ]);

        // Try to create another platform with the same dsa_common_id
        $response = $this->post(route('api.v1.platform.store'), [
            'name' => 'Another Platform',
            'vlop' => 0,
            'dsa_common_id' => 'unique-id-123'
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure(['message', 'errors']);
    }

    /**
     * @test
     */
    public function api_platform_update_works(): void
    {

        $user = $this->signInAsOnboarding();
        $this->withoutExceptionHandling();

        $this->assertCount(20, Platform::all());

        Platform::factory()->create([
            'name' => 'my test platform',
            'dsa_common_id' => 'foobar',
            'vlop'=> 0
        ]);

        $response = $this->put(route('api.v1.platform.update', ['platform' => 'foobar']), [
            'name' => 'updated name for my test platform',
            'vlop' => 1
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $updatedPlatform = Platform::firstWhere('dsa_common_id', 'foobar');

        $this->assertEquals('updated name for my test platform', $updatedPlatform->fresh()->name);
        $this->assertEquals(true, $updatedPlatform->fresh()->vlop);
        $this->assertCount(21, Platform::all());
    }

    /**
     * @test
     */
    public function it_should_give_platform_data(): void
    {
        $this->withoutExceptionHandling();

        $this->signInAsOnboarding();

        $platform = Platform::factory()->create([
            'name' => 'test platform',
            'dsa_common_id' => 'foobar',
        ]);

        $response = $this->get(route('api.v1.platform.get', ['platform' => 'foobar']), $this->requiredFields);


        $response->assertStatus(Response::HTTP_OK);
    }
}
