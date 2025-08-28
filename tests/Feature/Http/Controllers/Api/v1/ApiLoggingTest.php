<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\ApiLog;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiLoggingTest extends TestCase
{
    use RefreshDatabase;

    private Platform $platform;

    private User $user;

    private User $existingUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create and authenticate a user
        $this->user = User::factory()->create();
        $this->user->assignRole('Admin');
        Sanctum::actingAs($this->user);

        // Create a platform with a DSA common ID
        $this->platform = Platform::factory()->create([
            'name' => 'Test Platform',
            'vlop' => true,
            'dsa_common_id' => 'test-platform',
        ]);

        // Create an existing user for this platform
        $this->existingUser = User::factory()->create([
            'email' => 'existing@example.com',
            'platform_id' => $this->platform->id,
        ]);
    }

    public function test_platform_users_store_logs_duplicate_user_error()
    {
        // Try to add a user that already exists for this platform
        $response = $this->postJson("/api/v1/platform/{$this->platform->dsa_common_id}/users", [
            'emails' => ['existing@example.com'],
        ]);

        $this->assertEquals(422, $response->status());

        $log = ApiLog::where('endpoint', "api/v1/platform/{$this->platform->dsa_common_id}/users")
            ->where('method', 'POST')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(422, $log->response_code);
        $this->assertEquals($this->platform->id, $log->platform_id);
        $this->assertNotNull($log->error_message);

        // Verify the response data contains validation errors
        $responseData = $log->response_data;
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('emails.0', $responseData['errors']);
        $this->assertStringContainsString('already known', $responseData['errors']['emails.0'][0]);
    }

    public function test_platform_users_store_logs_multiple_duplicate_users_error()
    {
        // Create another existing user
        User::factory()->create([
            'email' => 'another@example.com',
            'platform_id' => $this->platform->id,
        ]);

        // Try to add multiple users that already exist for this platform
        $response = $this->postJson("/api/v1/platform/{$this->platform->dsa_common_id}/users", [
            'emails' => ['existing@example.com', 'another@example.com'],
        ]);

        $this->assertEquals(422, $response->status());

        $log = ApiLog::where('endpoint', "api/v1/platform/{$this->platform->dsa_common_id}/users")
            ->where('method', 'POST')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(422, $log->response_code);
        $this->assertEquals($this->platform->id, $log->platform_id);
        $this->assertNotNull($log->error_message);

        // Verify the response data contains validation errors for both emails
        $responseData = $log->response_data;
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('emails.0', $responseData['errors']);
        $this->assertArrayHasKey('emails.1', $responseData['errors']);
    }
}
