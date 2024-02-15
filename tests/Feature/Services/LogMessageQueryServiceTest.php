<?php

namespace Tests\Feature\Services;

use App\Services\DayArchiveService;
use App\Services\LogMessageQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogMessageQueryServiceTest extends TestCase
{

    use RefreshDatabase;

    protected LogMessageQueryService $log_message_query_service;

    private array $required_fields;

    #[\Override]protected function setUp(): void
    {
        parent::setUp();
        $this->log_message_query_service = app(LogMessageQueryService::class);
        $this->assertNotNull($this->log_message_query_service);

    }

    /**
     * @test
     * @return void
     */
    public function it_filters_on_s() : void
    {
        $result = $this->log_message_query_service->query(['s' => 'cow']);
        $raw = $result->toRawSql();
        $this->assertEquals('select * from "log_messages" where "message" LIKE \'%cow%\' or "context" LIKE \'%cow%\'', $raw);
    }

}
