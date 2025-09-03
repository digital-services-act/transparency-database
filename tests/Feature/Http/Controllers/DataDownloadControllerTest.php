<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;
use App\Models\Platform;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertContains;

class DataDownloadControllerTest extends TestCase
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
        $response = $this->get('/explore-data/download');
        $response->assertStatus(200);
        $response->assertViewIs('explore-data.download');
        $response->assertViewHas(['dayarchives', 'options', 'platform', 'reindexing']);
    }

    /**
     * @test
     */
    public function test_can_view_day_archive_index_page_with_platform_id()
    {
        $platform = Platform::factory()->create();
        $response = $this->get('/explore-data/download/?platform_id=' . $platform->id);
        $response->assertStatus(200);
        $response->assertViewIs('explore-data.download');
    }

    /** @test */
    public function test_views_day_archive_global_page_for_inexisting_platform()
    {
        $platformId = 10000;
        $response = $this->get('/explore-data/download/?platform_id=' . $platformId);
        $response->assertStatus(200);
        $response->assertViewHas('platform', fn ($platform) => $platform === null);
    }

    /** @test */
    public function test_discord_is_ignored_from_platform_dropdown()
    {
        Platform::factory(['name' => 'Discord Netherlands B.V.'])->create();
        $response = $this->get('/explore-data/download');
        $response->assertStatus(200);
        $response->assertViewHas('options.platforms');
        $response->assertViewHas('options.platforms', function ($platforms) {
            return ! collect($platforms)->contains('label', 'Discord Netherlands B.V.');
        });
    }

}
