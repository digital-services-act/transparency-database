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

<<<<<<< HEAD
=======
    public function test_can_download_global_full_archive_by_deterministic_filename(): void
    {
        Storage::fake('s3ds');

        DayArchive::factory()->completed()->global()->create([
            'date' => '2026-06-01',
            'url' => 'https://example.com/bucket/sor-global-2026-06-01-full.zip',
        ]);

        $response = $this->get(route('dayarchive.download.filename', [
            'platformSlug' => 'global',
            'date' => '2026-06-01',
            'version' => 'full',
        ]));

        $response->assertRedirect();
    }

    public function test_can_download_global_sha1_archive_by_deterministic_filename(): void
    {
        Storage::fake('s3ds');

        DayArchive::factory()->completed()->global()->create([
            'date' => '2026-06-01',
            'sha1url' => 'https://example.com/bucket/sor-global-2026-06-01-full.zip.sha1',
        ]);

        $response = $this->get(route('dayarchive.download.filename.sha1', [
            'platformSlug' => 'global',
            'date' => '2026-06-01',
            'version' => 'full',
        ]));

        $response->assertRedirect();
    }

    public function test_can_download_platform_light_archive_by_deterministic_filename(): void
    {
        Storage::fake('s3ds');

        $platform = Platform::factory()->create([
            'name' => 'Example Platform',
        ]);

        DayArchive::factory()->completed()->forPlatform($platform)->create([
            'date' => '2026-06-01',
            'urllight' => 'https://example.com/bucket/sor-example-platform-2026-06-01-light.zip',
        ]);

        $response = $this->get(route('dayarchive.download.filename', [
            'platformSlug' => 'example-platform',
            'date' => '2026-06-01',
            'version' => 'light',
        ]));

        $response->assertRedirect();
    }

    public function test_deterministic_archive_download_returns_404_for_missing_completed_archive(): void
    {
        DayArchive::factory()->global()->create([
            'date' => '2026-06-01',
            'url' => 'https://example.com/bucket/sor-global-2026-06-01-full.zip',
            'completed_at' => null,
        ]);

        $response = $this->get(route('dayarchive.download.filename', [
            'platformSlug' => 'global',
            'date' => '2026-06-01',
            'version' => 'full',
        ]));

        $response->assertNotFound();
    }

    public function test_deterministic_archive_download_returns_404_for_invalid_version(): void
    {
        $response = $this->get('/explore-data/download/sor-global-2026-06-01-medium.zip');

        $response->assertNotFound();
    }

>>>>>>> dev
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

    public function test_aggregates_download_redirects_to_presigned_url_for_csv(): void
    {
        Storage::fake('s3ds');
        Storage::disk('s3ds')->put('aggregates-2026-06-01.csv', 'platform_id,count');

        $response = $this->get(route('aggregates.download', [
            'date' => '2026-06-01',
            'ext' => 'csv',
        ]));

        $response->assertRedirect();
    }

    public function test_aggregates_download_redirects_to_presigned_url_for_json(): void
    {
        Storage::fake('s3ds');
        Storage::disk('s3ds')->put('aggregates-2026-06-01.json', '{"aggregates":[]}');

        $response = $this->get(route('aggregates.download', [
            'date' => '2026-06-01',
            'ext' => 'json',
        ]));

        $response->assertRedirect();
    }

    public function test_aggregates_download_returns_404_when_file_is_missing(): void
    {
        Storage::fake('s3ds');

        $response = $this->get(route('aggregates.download', [
            'date' => '2026-06-01',
            'ext' => 'csv',
        ]));

        $response->assertNotFound();
    }

    public function test_aggregates_download_returns_404_for_invalid_extension(): void
    {
        Storage::fake('s3ds');
        Storage::disk('s3ds')->put('aggregates-2026-06-01.txt', 'unsupported');

        $response = $this->get(route('aggregates.download', [
            'date' => '2026-06-01',
            'ext' => 'txt',
        ]));

        $response->assertNotFound();
    }

    public function test_aggregates_download_returns_404_for_invalid_date_format(): void
    {
        Storage::fake('s3ds');
        Storage::disk('s3ds')->put('aggregates-2026-06-1.csv', 'platform_id,count');

        $response = $this->get(route('aggregates.download', [
            'date' => '2026-06-1',
            'ext' => 'csv',
        ]));

        $response->assertNotFound();
    }
}
