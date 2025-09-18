<?php

namespace Tests\Feature\Services;

use App\Models\DayArchive;
use App\Models\Platform;
use App\Models\Statement;
use App\Services\DayArchiveService;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class DayArchiveServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DayArchiveService $day_archive_service;

    private array $required_fields;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->day_archive_service = app(DayArchiveService::class);
        $this->assertNotNull($this->day_archive_service);

        // Set the connection to use the testing database (sqlite)
        $this->day_archive_service->connection = config('database.default');

        $this->required_fields = [
            'decision_visibility' => ['DECISION_VISIBILITY_CONTENT_DISABLED', 'DECISION_VISIBILITY_CONTENT_AGE_RESTRICTED'],
            'decision_ground' => 'DECISION_GROUND_ILLEGAL_CONTENT',
            'category' => 'STATEMENT_CATEGORY_ANIMAL_WELFARE',
            'illegal_content_legal_ground' => 'foo',
            'illegal_content_explanation' => 'bar',
            'puid' => 'TK421',
            'territorial_scope' => ['BE', 'DE', 'FR'],
            'source_type' => 'SOURCE_ARTICLE_16',
            'source_identity' => 'foo',
            'decision_facts' => 'decision and facts',
            'content_type' => ['CONTENT_TYPE_SYNTHETIC_MEDIA'],
            'automated_detection' => 'No',
            'automated_decision' => 'AUTOMATED_DECISION_PARTIALLY',
            'application_date' => '2023-05-18',
            'content_date' => '2023-05-18',
        ];
    }

    /**
     * @test
     */
    public function it_builds_an_exports_array(): void
    {
        $result = $this->day_archive_service->buildBasicExportsArray();
        $this->assertNotNull($result);
        $this->assertIsArray($result);
        $this->assertCount(20, $result);
    }

    /**
     * @test
     */
    public function it_retrieves_global_list(): void
    {
        DayArchive::create([
            'date' => '2023-10-02',
            'total' => 1,
            'completed_at' => Carbon::now(),
        ]);
        DayArchive::create([
            'date' => '2023-10-01',
            'total' => 2,
            'completed_at' => Carbon::now(),
        ]);

        $list = $this->day_archive_service->globalList()->get();
        $this->assertCount(2, $list);

        // First one needs the 2
        $first = $list->first();
        $this->assertEquals('2023-10-02', $first->date->format('Y-m-d'));

        // Needs to be in the right order.
        $last = $list->last();
        $this->assertEquals('2023-10-01', $last->date->format('Y-m-d'));
    }

    /**
     * @test
     */
    public function gloabl_list_must_be_completed_day_archive(): void
    {
        DayArchive::create([
            'date' => '2023-10-02',
            'total' => 1,
        ]);
        DayArchive::create([
            'date' => '2023-10-01',
            'total' => 2,
        ]);

        $list = $this->day_archive_service->globalList()->get();
        $this->assertCount(0, $list);
    }

    /**
     * @test
     */
    public function it_retrieves_an_archive_by_date(): void
    {
        DayArchive::create([
            'date' => '2023-10-02',
            'total' => 1,
        ]);

        DayArchive::create([
            'date' => '2023-10-02',
            'total' => 5,
            'platform_id' => 5,
        ]);

        DayArchive::create([
            'date' => '2023-10-01',
            'total' => 1,
        ]);

        $dayarchive = $this->day_archive_service->getDayArchiveByDate(Carbon::createFromFormat('Y-m-d', '2023-10-02'));
        $this->assertNotNull($dayarchive);
        $this->assertEquals('2023-10-02', $dayarchive->date->format('Y-m-d'));
        $this->assertEquals(1, $dayarchive->total);
    }

    /**
     * @test
     */
    public function it_gets_the_first_id_from_date(): void
    {

        $admin = $this->signInAsAdmin();
        $fields_one = $this->required_fields;
        $fields_two = $this->required_fields;

        $fields_one['user_id'] = $admin->id;
        $fields_one['platform_id'] = $admin->platform->id;
        $fields_one['created_at'] = '2030-01-01 00:00:00'; // also prove the while loop works

        $fields_two['puid'] = 'TK422';
        $fields_two['user_id'] = $admin->id;
        $fields_two['platform_id'] = $admin->platform->id;
        $fields_two['created_at'] = '2030-01-01 00:00:00';

        $statement_one = Statement::create($fields_one);
        $statement_two = Statement::create($fields_two);

        $this->assertLessThan($statement_two->id, $statement_one->id);

        $first_id = $this->day_archive_service->getFirstIdOfDate(Carbon::createFromDate(2030, 1, 1));

        $this->assertEquals($statement_one->id, $first_id);
    }

    /**
     * @test
     */
    public function it_gets_false_on_first(): void
    {
        $this->signInAsAdmin();
        $first_id = $this->day_archive_service->getFirstIdOfDate(Carbon::createFromDate(2030, 1, 1));
        $this->assertFalse($first_id);
    }

    /**
     * @test
     */
    public function it_gets_the_first_id_from_date_in_the_first_minute(): void
    {

        $admin = $this->signInAsAdmin();
        $fields_one = $this->required_fields;
        $fields_two = $this->required_fields;

        $fields_one['user_id'] = $admin->id;
        $fields_one['platform_id'] = $admin->platform->id;
        $fields_one['created_at'] = '2030-01-01 00:00:05';

        $fields_two['puid'] = 'TK422';
        $fields_two['user_id'] = $admin->id;
        $fields_two['platform_id'] = $admin->platform->id;
        $fields_two['created_at'] = '2030-01-01 00:00:10';

        $statement_one = Statement::create($fields_one);
        $statement_two = Statement::create($fields_two);

        $first_id = $this->day_archive_service->getFirstIdOfDate(Carbon::createFromDate(2030, 1, 1));

        $this->assertEquals($statement_one->id, $first_id);
    }

    /**
     * @test
     */
    public function it_gets_the_last_id_from_date(): void
    {
        $admin = $this->signInAsAdmin();
        $fields_one = $this->required_fields;
        $fields_two = $this->required_fields;

        $fields_one['user_id'] = $admin->id;
        $fields_one['platform_id'] = $admin->platform->id;
        $fields_one['created_at'] = '2030-01-01 23:59:59'; // also prove the while loop works

        $fields_two['puid'] = 'TK422';
        $fields_two['user_id'] = $admin->id;
        $fields_two['platform_id'] = $admin->platform->id;
        $fields_two['created_at'] = '2030-01-01 23:59:59';

        $statement_one = Statement::create($fields_one);
        $statement_two = Statement::create($fields_two);

        $this->assertLessThan($statement_two->id, $statement_one->id);
        $last_id = $this->day_archive_service->getLastIdOfDate(Carbon::createFromDate(2030, 1, 1));

        $this->assertEquals($statement_two->id, $last_id);
    }

    /**
     * @test
     */
    public function it_gets_the_last_id_from_date_in_the_last_minute(): void
    {

        $admin = $this->signInAsAdmin();
        $fields_one = $this->required_fields;
        $fields_two = $this->required_fields;

        $fields_one['user_id'] = $admin->id;
        $fields_one['platform_id'] = $admin->platform->id;
        $fields_one['created_at'] = '2030-01-01 23:59:45'; // also prove the while loop works

        $fields_two['puid'] = 'TK422';
        $fields_two['user_id'] = $admin->id;
        $fields_two['platform_id'] = $admin->platform->id;
        $fields_two['created_at'] = '2030-01-01 23:59:45';

        $statement_one = Statement::create($fields_one);
        $statement_two = Statement::create($fields_two);

        $first_id = $this->day_archive_service->getLastIdOfDate(Carbon::createFromDate(2030, 1, 1));

        $this->assertEquals($statement_two->id, $first_id);
    }

    /**
     * @test
     */
    public function it_gets_zero_on_last(): void
    {
        $this->signInAsAdmin();
        $last_id = $this->day_archive_service->getLastIdOfDate(Carbon::createFromDate(2030, 1, 1));
        $this->assertFalse($last_id);
    }

    /**
     * @test
     */
    public function it_gets_a_query_by_platform(): void
    {
        $platform = Platform::first();
        $result = $this->day_archive_service->platformList($platform);
        $this->assertNotNull($result);
        $this->assertInstanceOf(Builder::class, $result);
        $sql = $result->toSql();
        $this->assertEquals('select * from "day_archives" where "platform_id" = ? and "completed_at" is not null order by "date" desc', $sql);
    }

    /**
     * @test
     */
    public function it_gets_a_day_archive_by_platform_date(): void
    {
        $platform = Platform::first();
        $date = Carbon::createFromDate(2024, 6, 15);

        $day_archive = DayArchive::create([
            'date' => $date,
            'platform_id' => $platform->id,
        ]);

        $result = $this->day_archive_service->getDayArchiveByPlatformDate($platform, $date);

        $this->assertNotNull($result);
        $this->assertEquals($day_archive->id, $result->id);

        $date_other = Carbon::createFromDate(2024, 6, 16);

        $result = $this->day_archive_service->getDayArchiveByPlatformDate($platform, $date_other);

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function it_gets_a_query_for_a_date(): void
    {
        $date = Carbon::createFromDate(2024, 6, 15);
        $result = $this->day_archive_service->getDayArchivesByDate($date);

        $this->assertNotNull($result);
        $this->assertInstanceOf(Builder::class, $result);
        $sql = $result->toSql();
        $this->assertEquals('select * from "day_archives" where strftime(\'%Y-%m-%d\', "date") = cast(? as text)', $sql);
    }

    /**
     * @test
     */
    public function it_gets_a_big_raw_select_string(): void
    {
        $result = $this->day_archive_service->getSelectRawString();
        $this->assertNotNull($result);
        $this->assertIsString($result);
    }

    /**
     * @test
     */
    public function it_prepares_headings_array_for_both_versions(): void
    {
        $result = $this->day_archive_service->prepareHeadingsArray();

        $this->assertNotNull($result);
        $this->assertIsArray($result);

        // Should contain both 'full' and 'light' versions
        $this->assertArrayHasKey('full', $result);
        $this->assertArrayHasKey('light', $result);

        // Both should be arrays of headings
        $this->assertIsArray($result['full']);
        $this->assertIsArray($result['light']);

        // Full version should have more headings than light version
        $this->assertGreaterThan(count($result['light']), count($result['full']));

        // Should contain some expected headings
        $this->assertContains('uuid', $result['full']);
        $this->assertContains('uuid', $result['light']);
        $this->assertContains('decision_ground', $result['full']);
        $this->assertContains('platform_name', $result['full']);
    }

    /**
     * @test
     */
    public function it_converts_array_to_csv_string(): void
    {
        $fields = ['field1', 'field2', 'field with spaces', 'field,with,commas'];

        $result = $this->day_archive_service->csvstr($fields);

        $this->assertNotNull($result);
        $this->assertIsString($result);

        // Should contain all fields
        $this->assertStringContainsString('field1', $result);
        $this->assertStringContainsString('field2', $result);
        $this->assertStringContainsString('field with spaces', $result);

        // Should properly escape fields with commas
        $this->assertStringContainsString('"field,with,commas"', $result);
    }

    /**
     * @test
     */
    public function it_converts_array_with_special_characters_to_csv_string(): void
    {
        $fields = ['normal', 'with"quote', "with\nnewline", 'with,comma'];

        $result = $this->day_archive_service->csvstr($fields);

        $this->assertNotNull($result);
        $this->assertIsString($result);

        // Should handle quotes and newlines properly
        $this->assertStringContainsString('normal', $result);
        $this->assertStringContainsString('"with""quote"', $result); // CSV escapes quotes by doubling them
    }

    /**
     * @test
     */
    public function it_maps_statement_to_rows_for_all_versions(): void
    {
        $admin = $this->signInAsAdmin();

        // Create a real statement in the database
        $statement = Statement::create(array_merge($this->required_fields, [
            'user_id' => $admin->id,
            'platform_id' => 1, // Use platform ID that definitely exists
            'created_at' => '2030-01-01 12:00:00',
        ]));

        // Use the service's getRawStatements method to get the raw SQL result
        $rawStatements = $this->day_archive_service->getRawStatements($statement->id, $statement->id);
        $this->assertNotEmpty($rawStatements);

        $rawStatement = $rawStatements->first();

        $result = $this->day_archive_service->mapRows($rawStatement);

        $this->assertNotNull($result);
        $this->assertIsArray($result);

        // Should contain both 'full' and 'light' versions
        $this->assertArrayHasKey('full', $result);
        $this->assertArrayHasKey('light', $result);

        // Both should be arrays of mapped data
        $this->assertIsArray($result['full']);
        $this->assertIsArray($result['light']);

        // Full version should have more fields than light version
        $this->assertGreaterThan(count($result['light']), count($result['full']));

        // Both should contain some basic fields
        $this->assertNotEmpty($result['full']);
        $this->assertNotEmpty($result['light']);
    }

    /**
     * @test
     */
    public function it_builds_csv_lines_for_statement(): void
    {
        $admin = $this->signInAsAdmin();

        // Create a real statement in the database
        $statement = Statement::create(array_merge($this->required_fields, [
            'user_id' => $admin->id,
            'platform_id' => 1, // Use platform ID that definitely exists
            'created_at' => '2030-01-01 12:00:00',
        ]));

        // Use the service's getRawStatements method to get the raw SQL result
        $rawStatements = $this->day_archive_service->getRawStatements($statement->id, $statement->id);
        $this->assertNotEmpty($rawStatements);

        $rawStatement = $rawStatements->first();

        $result = $this->day_archive_service->buildCsvLines($rawStatement);

        $this->assertNotNull($result);
        $this->assertIsArray($result);

        // Should contain both 'full' and 'light' versions
        $this->assertArrayHasKey('full', $result);
        $this->assertArrayHasKey('light', $result);

        // Both should be CSV strings
        $this->assertIsString($result['full']);
        $this->assertIsString($result['light']);

        // Should contain some of the statement data
        $this->assertStringContainsString('TK421', $result['full']); // puid from required_fields
        $this->assertStringContainsString('TK421', $result['light']); // puid should be in both versions

        // Full version should be longer than light version
        $this->assertGreaterThan(strlen($result['light']), strlen($result['full']));
    }

    /**
     * @test
     */
    public function it_builds_csv_lines_with_special_characters(): void
    {
        $admin = $this->signInAsAdmin();

        // Create a real statement in the database with special characters
        $statement = Statement::create(array_merge($this->required_fields, [
            'user_id' => $admin->id,
            'platform_id' => 1, // Use platform ID that definitely exists
            'created_at' => '2030-01-01 12:00:00',
            'puid' => 'Special,Puid"With\'Quotes',
            'decision_facts' => "Facts with\nnewlines and \"quotes\"",
        ]));

        // Use the service's getRawStatements method to get the raw SQL result
        $rawStatements = $this->day_archive_service->getRawStatements($statement->id, $statement->id);
        $this->assertNotEmpty($rawStatements);

        $rawStatement = $rawStatements->first();

        $result = $this->day_archive_service->buildCsvLines($rawStatement);

        $this->assertNotNull($result);
        $this->assertIsArray($result);

        // Should properly escape special characters in CSV
        $this->assertIsString($result['full']);
        $this->assertIsString($result['light']);

        // Should contain escaped special characters
        $this->assertStringContainsString('Special,Puid', $result['full']);
    }

    /**
     * @test
     */
    public function it_gets_raw_statements_with_proper_format(): void
    {
        $admin = $this->signInAsAdmin();

        // Create multiple statements
        $statement1 = Statement::create(array_merge($this->required_fields, [
            'user_id' => $admin->id,
            'platform_id' => 1,
            'created_at' => '2030-01-01 12:00:00',
            'puid' => 'FIRST_STATEMENT',
        ]));

        $statement2 = Statement::create(array_merge($this->required_fields, [
            'user_id' => $admin->id,
            'platform_id' => 1,
            'created_at' => '2030-01-01 12:00:01',
            'puid' => 'SECOND_STATEMENT',
        ]));

        // Get raw statements using the service method
        $rawStatements = $this->day_archive_service->getRawStatements($statement1->id, $statement2->id);

        $this->assertNotNull($rawStatements);
        $this->assertInstanceOf(Collection::class, $rawStatements);
        $this->assertCount(2, $rawStatements);

        // Check that the statements are stdClass objects (raw SQL results)
        $firstRaw = $rawStatements->first();
        $this->assertInstanceOf(\stdClass::class, $firstRaw);

        // Should have expected properties from the raw select
        $this->assertObjectHasProperty('id', $firstRaw);
        $this->assertObjectHasProperty('uuid', $firstRaw);
        $this->assertObjectHasProperty('puid', $firstRaw);
        $this->assertObjectHasProperty('platform_id', $firstRaw);
        $this->assertObjectHasProperty('created_at', $firstRaw);

        // Should be ordered by ID
        $this->assertEquals('FIRST_STATEMENT', $firstRaw->puid);
        $this->assertEquals('SECOND_STATEMENT', $rawStatements->last()->puid);
    }

    /**
     * @test
     */
    public function it_handles_empty_array_in_csvstr(): void
    {
        $result = $this->day_archive_service->csvstr([]);

        $this->assertNotNull($result);
        $this->assertIsString($result);
        $this->assertEquals('', $result);
    }

    /**
     * @test
     */
    public function it_handles_single_field_in_csvstr(): void
    {
        $result = $this->day_archive_service->csvstr(['single_field']);

        $this->assertNotNull($result);
        $this->assertIsString($result);
        $this->assertEquals('single_field', $result);
    }

    /**
     * @test
     */
    public function it_handles_csvstr_with_problematic_data(): void
    {
        // Try with data that has special CSV characters but are still strings
        $problematicFields = [
            'field,with,commas',
            'field"with"quotes',
            "field\nwith\nnewlines",
            "field\twith\ttabs",
            "\x00\x01\x02\x03", // binary data as string
            'normal_field',
        ];

        $result = $this->day_archive_service->csvstr($problematicFields);

        // The method should handle this gracefully and return a string
        $this->assertIsString($result);

        // Should contain escaped versions of the problematic data
        $this->assertStringContainsString('"field,with,commas"', $result);
        $this->assertStringContainsString('normal_field', $result);
    }
}
