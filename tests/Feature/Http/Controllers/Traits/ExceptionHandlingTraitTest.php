<?php

namespace Tests\Feature\Http\Controllers\Traits;

use App\Http\Controllers\Traits\ExceptionHandlingTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use PDOException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Throwable;

class ExceptionHandlingTraitTest extends TestCase
{
    use ExceptionHandlingTrait;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        // Prevent actual logging during tests
        Log::spy();
    }

    public function test_handle_query_exception_returns_correct_response(): void
    {
        // Create a PDOException (which QueryException wraps)
        $pdoException = new PDOException('SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry \'123-456\' for key \'platform_puids_platform_id_puid_unique\'');
        
        // Create a QueryException with the PDOException
        $queryException = new QueryException(
            'mysql', // connection name
            'insert into `platform_puids` (`platform_id`, `puid`) values (?, ?)',
            [1, '456'],
            $pdoException
        );

        // Call the method
        $response = $this->handleQueryException($queryException, 'Statement');

        // Assert response is correct type
        $this->assertInstanceOf(JsonResponse::class, $response);
        
        // Assert status code is 500
        $this->assertEquals(500, $response->status());

        // Assert response structure
        $content = json_decode($response->content(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertArrayHasKey('errors', $content);
        $this->assertArrayHasKey('uncaught_exception', $content['errors']);

        // Assert message content
        $this->assertEquals(
            'Statement Creation Query Exception Occurred, information has been passed on to the development team.',
            $content['message']
        );

        // Verify logging occurred
        Log::shouldHaveReceived('error')
            ->with('Statement Creation Query Exception Thrown', ['exception' => $queryException])
            ->once();
    }

    public function test_extract_puid_from_message_with_valid_message(): void
    {
        $exception = new PDOException('SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry \'123-456\' for key \'platform_puids_platform_id_puid_unique\'');
        
        $puid = $this->extractPUIDFromMessage($exception->getMessage());
        
        $this->assertEquals('456', $puid);
    }

    public function test_extract_puid_from_message_with_no_match(): void
    {
        $exception = new PDOException('Some other database error message');
        
        $puid = $this->extractPUIDFromMessage($exception->getMessage());
        
        $this->assertEquals('Unknown Exception', $puid);
    }

    public function test_extract_puid_from_message_with_different_format(): void
    {
        $exception = new PDOException('SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry \'999-888\' for key \'platform_puids_platform_id_puid_unique\'');
        
        $puid = $this->extractPUIDFromMessage($exception->getMessage());
        
        $this->assertEquals('888', $puid);
    }

    public function test_handle_query_exception_with_different_subject(): void
    {
        $queryException = new QueryException(
            'mysql',
            'select * from users',
            [],
            new PDOException('Database connection failed')
        );

        $response = $this->handleQueryException($queryException, 'User');

        $content = json_decode($response->content(), true);
        
        $this->assertEquals(
            'User Creation Query Exception Occurred, information has been passed on to the development team.',
            $content['message']
        );
    }
}
