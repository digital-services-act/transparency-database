<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;
use App\Models\Platform;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DayArchiveControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpFullySeededDatabase();
        // Sign in as admin to ensure we have access to all functionality
        $this->signInAsAdmin();
    }

    /**
     * @test
     */
    public function test_can_view_day_archive_index_page()
    {
        $response = $this->get('/data-download');
        $response->assertStatus(200);
        $response->assertViewIs('dayarchive.index');
        $response->assertViewHas(['dayarchives', 'options', 'platform', 'reindexing']);
    }

    /**
     * @test
     */
    public function test_can_view_day_archive_index_page_with_platform_uuid()
    {
        $platform = Platform::factory()->create();
        $response = $this->get('/data-download/?uuid=' . $platform->uuid);
        $response->assertStatus(200);
        $response->assertViewIs('dayarchive.index');
    }
}
