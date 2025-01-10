<?php

namespace Tests\Feature\Models;

use App\Models\ApiLog;
use App\Models\Platform;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class ApiLogTest extends TestCase
{
    use RefreshDatabase;

    private Platform $platform;
    private array $defaultLogData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->platform = Platform::factory()->create();
        $this->defaultLogData = [
            'endpoint' => '/api/v1/platform',
            'method' => 'POST',
            'request_data' => ['name' => 'Test Platform'],
            'response_data' => ['id' => 1, 'name' => 'Test Platform'],
            'response_code' => 201,
        ];
    }

    public function test_api_log_can_be_created_with_minimum_data()
    {
        $apiLog = ApiLog::create($this->defaultLogData);

        $this->assertDatabaseHas('api_logs', [
            'id' => $apiLog->id,
            'endpoint' => '/api/v1/platform',
            'method' => 'POST',
            'response_code' => 201,
        ]);

        // Test that all attributes are set correctly
        $this->assertEquals('/api/v1/platform', $apiLog->endpoint);
        $this->assertEquals('POST', $apiLog->method);
        $this->assertEquals(['name' => 'Test Platform'], $apiLog->request_data);
        $this->assertEquals(['id' => 1, 'name' => 'Test Platform'], $apiLog->response_data);
        $this->assertEquals(201, $apiLog->response_code);
        $this->assertNull($apiLog->error_message);
    }

    public function test_api_log_can_be_created_with_platform_relationship()
    {
        $apiLog = ApiLog::create(array_merge($this->defaultLogData, [
            'platform_id' => $this->platform->id
        ]));

        $this->assertDatabaseHas('api_logs', [
            'id' => $apiLog->id,
            'platform_id' => $this->platform->id,
        ]);

        // Test the relationship
        $this->assertTrue($apiLog->platform()->exists());
        $this->assertEquals($this->platform->id, $apiLog->platform->id);
        $this->assertEquals($this->platform->name, $apiLog->platform->name);
    }

    public function test_api_log_can_be_created_with_error_message()
    {
        $apiLog = ApiLog::create(array_merge($this->defaultLogData, [
            'request_data' => ['invalid_data' => true],
            'response_data' => ['error' => 'Validation failed'],
            'response_code' => 422,
            'error_message' => 'The given data was invalid.',
        ]));

        $this->assertDatabaseHas('api_logs', [
            'id' => $apiLog->id,
            'error_message' => 'The given data was invalid.',
            'response_code' => 422,
        ]);

        // Test that error_message is set correctly
        $this->assertEquals('The given data was invalid.', $apiLog->error_message);
    }

    public function test_request_and_response_data_are_json_casted()
    {
        $requestData = ['name' => 'Test Platform', 'vlop' => true];
        $responseData = ['id' => 1, 'name' => 'Test Platform', 'vlop' => true];

        $apiLog = ApiLog::create(array_merge($this->defaultLogData, [
            'request_data' => $requestData,
            'response_data' => $responseData,
        ]));

        // Test JSON casting
        $this->assertIsArray($apiLog->request_data);
        $this->assertIsArray($apiLog->response_data);
        $this->assertEquals($requestData, $apiLog->request_data);
        $this->assertEquals($responseData, $apiLog->response_data);

        // Test that the data is actually stored as JSON in the database
        $rawApiLog = DB::table('api_logs')->where('id', $apiLog->id)->first();
        $this->assertJson($rawApiLog->request_data);
        $this->assertJson($rawApiLog->response_data);
    }

    public function test_platform_relationship_can_be_null()
    {
        $apiLog = ApiLog::create($this->defaultLogData);

        $this->assertNull($apiLog->platform);
        $this->assertDatabaseHas('api_logs', [
            'id' => $apiLog->id,
            'platform_id' => null,
        ]);

        // Test that the relationship method works with null
        $this->assertFalse($apiLog->platform()->exists());
    }

    public function test_fillable_attributes_are_mass_assignable()
    {
        $data = array_merge($this->defaultLogData, [
            'platform_id' => $this->platform->id,
            'error_message' => 'Test error'
        ]);

        $apiLog = new ApiLog($data);
        $apiLog->save();

        $this->assertEquals($data['endpoint'], $apiLog->endpoint);
        $this->assertEquals($data['method'], $apiLog->method);
        $this->assertEquals($data['platform_id'], $apiLog->platform_id);
        $this->assertEquals($data['request_data'], $apiLog->request_data);
        $this->assertEquals($data['response_data'], $apiLog->response_data);
        $this->assertEquals($data['response_code'], $apiLog->response_code);
        $this->assertEquals($data['error_message'], $apiLog->error_message);
    }

    public function test_timestamps_are_automatically_set()
    {
        $apiLog = ApiLog::create($this->defaultLogData);

        $this->assertNotNull($apiLog->created_at);
        $this->assertNotNull($apiLog->updated_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $apiLog->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $apiLog->updated_at);
    }

    public function test_api_log_can_be_updated()
    {
        $apiLog = ApiLog::create($this->defaultLogData);

        $newData = [
            'endpoint' => '/api/v1/platform/update',
            'method' => 'PUT',
            'request_data' => ['name' => 'Updated Platform'],
            'response_data' => ['id' => 1, 'name' => 'Updated Platform'],
            'response_code' => 200,
        ];

        $apiLog->update($newData);

        $this->assertDatabaseHas('api_logs', [
            'id' => $apiLog->id,
            'endpoint' => '/api/v1/platform/update',
            'method' => 'PUT',
            'response_code' => 200,
        ]);

        $this->assertEquals('Updated Platform', $apiLog->fresh()->request_data['name']);
        $this->assertEquals('Updated Platform', $apiLog->fresh()->response_data['name']);
    }

    public function test_api_log_can_be_deleted()
    {
        $apiLog = ApiLog::create($this->defaultLogData);
        $apiLogId = $apiLog->id;

        $this->assertDatabaseHas('api_logs', ['id' => $apiLogId]);

        $apiLog->delete();

        $this->assertDatabaseMissing('api_logs', ['id' => $apiLogId]);
    }

    public function test_api_log_rejects_invalid_json_data()
    {
        $this->expectException(\Illuminate\Database\Eloquent\JsonEncodingException::class);

        $invalidJson = "\xB1\x31"; // Invalid UTF-8 sequence

        ApiLog::create(array_merge($this->defaultLogData, [
            'request_data' => $invalidJson,
            'response_data' => $invalidJson,
        ]));
    }

    public function test_api_log_can_be_queried_by_date_range()
    {
        // Create logs with specific timestamps
        $oldLog = new ApiLog(array_merge($this->defaultLogData, []));
        $oldLog->created_at = '2025-01-09 16:37:48';
        $oldLog->save();

        $newLog = new ApiLog(array_merge($this->defaultLogData, []));
        $newLog->created_at = '2025-01-09 17:37:48';
        $newLog->save();

        // Query logs within a date range
        $logs = ApiLog::whereBetween('created_at', [
            '2025-01-09 17:00:00',
            '2025-01-09 18:00:00'
        ])->get();

        $this->assertCount(1, $logs);
        $this->assertEquals($newLog->id, $logs->first()->id);
    }

    public function test_api_log_can_be_queried_by_endpoint_and_method()
    {
        // Create a log with different endpoint and method
        ApiLog::create(array_merge($this->defaultLogData, [
            'endpoint' => '/api/v1/users',
            'method' => 'GET',
        ]));

        // Create our target log
        ApiLog::create($this->defaultLogData);

        $logs = ApiLog::where('endpoint', '/api/v1/platform')
                     ->where('method', 'POST')
                     ->get();

        $this->assertCount(1, $logs);
        $this->assertEquals('/api/v1/platform', $logs->first()->endpoint);
        $this->assertEquals('POST', $logs->first()->method);
    }

    public function test_api_log_can_be_queried_by_response_code()
    {
        // Create a successful log
        ApiLog::create($this->defaultLogData);

        // Create an error log
        ApiLog::create(array_merge($this->defaultLogData, [
            'response_code' => 500,
            'error_message' => 'Internal Server Error',
        ]));

        $errorLogs = ApiLog::where('response_code', '>=', 500)->get();
        $successLogs = ApiLog::where('response_code', '<', 300)->get();

        $this->assertCount(1, $errorLogs);
        $this->assertCount(1, $successLogs);
        $this->assertEquals(500, $errorLogs->first()->response_code);
        $this->assertEquals(201, $successLogs->first()->response_code);
    }
}
