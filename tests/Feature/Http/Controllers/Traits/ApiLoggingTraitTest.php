<?php

namespace Tests\Feature\Http\Controllers\Traits;

use App\Http\Controllers\Traits\ApiLoggingTrait;
use App\Models\ApiLog;
use App\Models\Platform;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tests\TestCase;

class ApiLoggingTraitTest extends TestCase
{
    use RefreshDatabase;

    private $controller;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test controller class using the trait
        $this->controller = new class {
            use ApiLoggingTrait;

            // Make the method public for testing
            public function logApiCallTest($request, $response, $platformId = null, $error = null)
            {
                return $this->logApiCall($request, $response, $platformId, $error);
            }
        };
    }

    public function test_logs_successful_api_call()
    {
        $request = new Request();
        $request->server->set('REQUEST_METHOD', 'POST');
        $request->server->set('REQUEST_URI', '/api/v1/platform');

        $requestData = ['name' => 'Test Platform'];
        $request->merge($requestData);

        $responseData = ['id' => 1, 'name' => 'Test Platform'];
        $response = new JsonResponse($responseData, 201);

        $platform = Platform::factory()->create();

        $this->controller->logApiCallTest($request, $response, $platform->id);

        $this->assertDatabaseHas('api_logs', [
            'endpoint' => 'api/v1/platform',
            'method' => 'POST',
            'platform_id' => $platform->id,
            'response_code' => 201,
            'error_message' => null,
        ]);

        $apiLog = ApiLog::first();
        $this->assertEquals($requestData, $apiLog->request_data);
        $this->assertEquals($responseData, $apiLog->response_data);
    }

    public function test_logs_failed_api_call()
    {
        $request = new Request();
        $request->server->set('REQUEST_METHOD', 'POST');
        $request->server->set('REQUEST_URI', '/api/v1/platform');

        $requestData = ['invalid_data' => true];
        $request->merge($requestData);

        $responseData = ['error' => 'Validation failed'];
        $response = new JsonResponse($responseData, 422);

        $error = new Exception('The given data was invalid.');

        $this->controller->logApiCallTest($request, $response, null, $error);

        $this->assertDatabaseHas('api_logs', [
            'endpoint' => 'api/v1/platform',
            'method' => 'POST',
            'platform_id' => null,
            'response_code' => 422,
            'error_message' => 'The given data was invalid.',
        ]);

        $apiLog = ApiLog::first();
        $this->assertEquals($requestData, $apiLog->request_data);
        $this->assertEquals($responseData, $apiLog->response_data);
    }

    public function test_logs_api_call_with_empty_response_data()
    {
        $request = new Request();
        $request->server->set('REQUEST_METHOD', 'DELETE');
        $request->server->set('REQUEST_URI', '/api/v1/platform/1');

        $response = new JsonResponse(null, 204);

        $this->controller->logApiCallTest($request, $response);

        $this->assertDatabaseHas('api_logs', [
            'endpoint' => 'api/v1/platform/1',
            'method' => 'DELETE',
            'response_code' => 204,
        ]);

        $apiLog = ApiLog::first();
        $this->assertEmpty($apiLog->request_data);
        $this->assertEmpty($apiLog->response_data);
    }

    public function test_logs_api_call_with_array_response_data()
    {
        $request = new Request();
        $request->server->set('REQUEST_METHOD', 'GET');
        $request->server->set('REQUEST_URI', '/api/v1/platform');

        $responseData = [
            ['id' => 1, 'name' => 'Platform 1'],
            ['id' => 2, 'name' => 'Platform 2'],
        ];
        $response = new JsonResponse($responseData, 200);

        $this->controller->logApiCallTest($request, $response);

        $apiLog = ApiLog::first();
        $this->assertEquals($responseData, $apiLog->response_data);
        $this->assertEquals(200, $apiLog->response_code);
    }
}
