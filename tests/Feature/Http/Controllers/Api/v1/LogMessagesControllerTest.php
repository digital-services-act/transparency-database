<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Yoeriboven\LaravelLogDb\Models\LogMessage;

class LogMessagesControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     */
    public function it_should_list_the_log_messages(): void
    {
        $this->signInAsAdmin();
        $response = $this->get(route('log-messages.index'));
        $response->assertStatus(Response::HTTP_OK);
        $response->assertViewIs('log_messages.index');
    }

    /**
     * @test
     */
    public function it_removes_log_messages_redirects_to_index(): void
    {
        // We should be at 0
        $log_messages = LogMessage::all();
        $this->assertCount(0, $log_messages);

        // Create One
        Log::info('this is a test');
        $log_messages = LogMessage::all();
        $this->assertCount(1, $log_messages);

        $this->signInAsAdmin();
        $response = $this->delete(route('log-messages.destroy'));
        $response->assertRedirect();

        // We should be back to 0
        $log_messages = LogMessage::all();
        $this->assertCount(0, $log_messages);
    }

    /**
     * @test
     */
    public function it_should_block_non_admins_from_truncating(): void
    {
        $this->signInAsSupport();
        $response = $this->delete(route('log-messages.destroy'));
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function it_should_block_non_admins_from_viewing(): void
    {
        $this->signInAsOnboarding();
        $response = $this->get(route('log-messages.index'));
        $response->assertForbidden();
    }
}
