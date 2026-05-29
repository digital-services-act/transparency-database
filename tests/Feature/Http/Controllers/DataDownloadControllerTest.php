<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\DayArchive;
use App\Models\Platform;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

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

    public function test_can_view_day_archive_index_page()
    {
        $response = $this->get('/explore-data/download');
        $response->assertStatus(200);
        $response->assertViewIs('explore-data.download');
        $response->assertViewHas(['dayarchives', 'options', 'platform', 'reindexing']);
    }

    public function test_can_view_day_archive_index_page_with_platform_uuid()
    {
        $platform = Platform::factory()->create();
        $response = $this->get('/explore-data/download/?uuid='.$platform->uuid);
        $response->assertStatus(200);
        $response->assertViewIs('explore-data.download');
    }

    public function test_day_archive_table_links_to_internal_download_route(): void
    {
        $dayArchive = DayArchive::factory()->completed()->global()->create([
            'url' => 'https://example.com/bucket/archive-2024-01-01-full.zip',
            'urllight' => 'https://example.com/bucket/archive-2024-01-01-light.zip',
            'sha1url' => 'https://example.com/bucket/archive-2024-01-01-full.zip.sha1',
            'sha1urllight' => 'https://example.com/bucket/archive-2024-01-01-light.zip.sha1',
        ]);

        $response = $this->get('/explore-data/download');

        $response->assertStatus(200);
        $response->assertSee(route('dayarchive.download', ['dayArchive' => $dayArchive->id, 'type' => 'full']), false);
        $response->assertSee(route('dayarchive.download', ['dayArchive' => $dayArchive->id, 'type' => 'light']), false);
        $response->assertSee(route('dayarchive.download', ['dayArchive' => $dayArchive->id, 'type' => 'sha1']), false);
        $response->assertSee(route('dayarchive.download', ['dayArchive' => $dayArchive->id, 'type' => 'sha1light']), false);
        $response->assertDontSee('https://example.com/bucket/archive-2024-01-01-full.zip', false);
        $response->assertDontSee('https://example.com/bucket/archive-2024-01-01-light.zip', false);
    }

    public function test_download_redirects_to_presigned_url_for_full_type(): void
    {
        Storage::fake('s3ds');

        $dayArchive = DayArchive::factory()->completed()->create([
            'url' => 'https://example.com/bucket/archive-2024-01-01-full.zip',
        ]);

        $response = $this->get(route('dayarchive.download', [
            'dayArchive' => $dayArchive->id,
            'type' => 'full',
        ]));

        $response->assertRedirect();
    }

    public function test_download_redirects_to_presigned_url_for_light_type(): void
    {
        Storage::fake('s3ds');

        $dayArchive = DayArchive::factory()->completed()->create([
            'urllight' => 'https://example.com/bucket/archive-2024-01-01-light.zip',
        ]);

        $response = $this->get(route('dayarchive.download', [
            'dayArchive' => $dayArchive->id,
            'type' => 'light',
        ]));

        $response->assertRedirect();
    }

    public function test_download_redirects_to_presigned_url_for_sha1_type(): void
    {
        Storage::fake('s3ds');

        $dayArchive = DayArchive::factory()->completed()->create([
            'sha1url' => 'https://example.com/bucket/archive-2024-01-01-full.zip.sha1',
        ]);

        $response = $this->get(route('dayarchive.download', [
            'dayArchive' => $dayArchive->id,
            'type' => 'sha1',
        ]));

        $response->assertRedirect();
    }

    public function test_download_redirects_to_presigned_url_for_sha1light_type(): void
    {
        Storage::fake('s3ds');

        $dayArchive = DayArchive::factory()->completed()->create([
            'sha1urllight' => 'https://example.com/bucket/archive-2024-01-01-light.zip.sha1',
        ]);

        $response = $this->get(route('dayarchive.download', [
            'dayArchive' => $dayArchive->id,
            'type' => 'sha1light',
        ]));

        $response->assertRedirect();
    }

    public function test_download_redirects_directly_for_legacy_amazonaws_url(): void
    {
        $legacyUrl = 'https://dsa-transparency-database.s3.amazonaws.com/archive-2024-01-01-full.zip';
        $dayArchive = DayArchive::factory()->completed()->create([
            'url' => $legacyUrl,
        ]);

        $response = $this->get(route('dayarchive.download', [
            'dayArchive' => $dayArchive->id,
            'type' => 'full',
        ]));

        $response->assertRedirect($legacyUrl);
    }

    public function test_download_returns_404_for_invalid_type(): void
    {
        $dayArchive = DayArchive::factory()->completed()->create();

        $response = $this->get(route('dayarchive.download', [
            'dayArchive' => $dayArchive->id,
            'type' => 'invalid',
        ]));

        $response->assertNotFound();
    }

    public function test_download_returns_404_for_nonexistent_archive(): void
    {
        $response = $this->get(route('dayarchive.download', [
            'dayArchive' => 99999,
            'type' => 'full',
        ]));

        $response->assertNotFound();
    }

    public function test_download_returns_404_when_url_is_empty(): void
    {
        $dayArchive = DayArchive::factory()->completed()->create([
            'url' => null,
        ]);

        $response = $this->get(route('dayarchive.download', [
            'dayArchive' => $dayArchive->id,
            'type' => 'full',
        ]));

        $response->assertNotFound();
    }

    public function test_download_returns_404_when_urllight_is_empty_string(): void
    {
        $dayArchive = DayArchive::factory()->completed()->create([
            'urllight' => '',
        ]);

        $response = $this->get(route('dayarchive.download', [
            'dayArchive' => $dayArchive->id,
            'type' => 'light',
        ]));

        $response->assertNotFound();
    }

    public function test_download_returns_404_when_sha1url_is_empty_string(): void
    {
        $dayArchive = DayArchive::factory()->completed()->create([
            'sha1url' => '',
        ]);

        $response = $this->get(route('dayarchive.download', [
            'dayArchive' => $dayArchive->id,
            'type' => 'sha1',
        ]));

        $response->assertNotFound();
    }

    public function test_download_returns_404_when_sha1urllight_is_empty_string(): void
    {
        $dayArchive = DayArchive::factory()->completed()->create([
            'sha1urllight' => '',
        ]);

        $response = $this->get(route('dayarchive.download', [
            'dayArchive' => $dayArchive->id,
            'type' => 'sha1light',
        ]));

        $response->assertNotFound();
    }
}
