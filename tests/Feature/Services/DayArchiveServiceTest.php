<?php

namespace Tests\Feature\Services;

use App\Models\ArchivedStatement;
use App\Models\DayArchive;
use App\Models\Statement;
use App\Services\DayArchiveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DayArchiveServiceTest extends TestCase
{

    use RefreshDatabase;

    protected DayArchiveService $day_archive_service;

    private array $required_fields;

    #[\Override]protected function setUp(): void
    {
        parent::setUp();
        $this->day_archive_service = app(DayArchiveService::class);
        $this->assertNotNull($this->day_archive_service);

        $this->required_fields = [
            'decision_visibility'          => ['DECISION_VISIBILITY_CONTENT_DISABLED', 'DECISION_VISIBILITY_CONTENT_AGE_RESTRICTED'],
            'decision_ground'              => 'DECISION_GROUND_ILLEGAL_CONTENT',
            'category'                     => 'STATEMENT_CATEGORY_ANIMAL_WELFARE',
            'illegal_content_legal_ground' => 'foo',
            'illegal_content_explanation'  => 'bar',
            'puid'                         => 'TK421',
            'territorial_scope'            => ['BE', 'DE', 'FR'],
            'source_type'                  => 'SOURCE_ARTICLE_16',
            'source_identity'              => 'foo',
            'decision_facts'               => 'decision and facts',
            'content_type'                 => ['CONTENT_TYPE_SYNTHETIC_MEDIA'],
            'automated_detection'          => 'No',
            'automated_decision'           => 'AUTOMATED_DECISION_PARTIALLY',
            'application_date'             => '2023-05-18',
            'content_date'                 => '2023-05-18'
        ];
    }

    /**
     * @test
     */
    public function it_retrieves_global_list(): void
    {
        DayArchive::create([
            'date'         => '2023-10-02',
            'total'        => 1,
            'completed_at' => Carbon::now()
        ]);
        DayArchive::create([
            'date'         => '2023-10-01',
            'total'        => 2,
            'completed_at' => Carbon::now()
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
            'date'  => '2023-10-02',
            'total' => 1
        ]);
        DayArchive::create([
            'date'  => '2023-10-01',
            'total' => 2
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
            'date'  => '2023-10-02',
            'total' => 1
        ]);

        DayArchive::create([
            'date'        => '2023-10-02',
            'total'       => 5,
            'platform_id' => 5
        ]);

        DayArchive::create([
            'date'  => '2023-10-01',
            'total' => 1
        ]);

        $dayarchive = $this->day_archive_service->getDayArchiveByDate(Carbon::createFromFormat('Y-m-d', '2023-10-02'));
        $this->assertNotNull($dayarchive);
        $this->assertEquals('2023-10-02', $dayarchive->date->format('Y-m-d'));
        $this->assertEquals(1, $dayarchive->total);
    }


    /**
     * @test
     * @return void
     */
    public function it_gets_the_first_id_from_date(): void
    {

        $admin      = $this->signInAsAdmin();
        $fields_one = $this->required_fields;
        $fields_two = $this->required_fields;

        $fields_one['user_id']     = $admin->id;
        $fields_one['platform_id'] = $admin->platform->id;
        $fields_one['created_at']  = '2030-01-01 00:00:00'; // also prove the while loop works

        $fields_two['puid']        = 'TK422';
        $fields_two['user_id']     = $admin->id;
        $fields_two['platform_id'] = $admin->platform->id;
        $fields_two['created_at']  = '2030-01-01 00:00:00';

        $statement_one = Statement::create($fields_one);
        $statement_two = Statement::create($fields_two);

        $this->assertLessThan($statement_two->id, $statement_one->id);

        $first_id = $this->day_archive_service->getFirstIdOfDate(Carbon::createFromDate(2030, 1, 1));

        $this->assertEquals($statement_one->id, $first_id);
    }

    /**
     * @test
     * @return void
     */
    public function it_gets_false_on_first(): void
    {
        $this->signInAsAdmin();
        $first_id = $this->day_archive_service->getFirstIdOfDate(Carbon::createFromDate(2030, 1, 1));
        $this->assertFalse($first_id);
    }



    /**
     * @test
     * @return void
     */
    public function it_gets_the_last_id_from_date(): void
    {
        $admin      = $this->signInAsAdmin();
        $fields_one = $this->required_fields;
        $fields_two = $this->required_fields;

        $fields_one['user_id']     = $admin->id;
        $fields_one['platform_id'] = $admin->platform->id;
        $fields_one['created_at']  = '2030-01-01 23:59:59'; // also prove the while loop works

        $fields_two['puid']        = 'TK422';
        $fields_two['user_id']     = $admin->id;
        $fields_two['platform_id'] = $admin->platform->id;
        $fields_two['created_at']  = '2030-01-01 23:59:59';

        $statement_one = Statement::create($fields_one);
        $statement_two = Statement::create($fields_two);

        $this->assertLessThan($statement_two->id, $statement_one->id);
        $last_id = $this->day_archive_service->getLastIdOfDate(Carbon::createFromDate(2030, 1, 1));

        $this->assertEquals($statement_two->id, $last_id);
    }

    /**
     * @test
     * @return void
     */
    public function it_gets_zero_on_last(): void
    {
        $this->signInAsAdmin();
        $last_id = $this->day_archive_service->getLastIdOfDate(Carbon::createFromDate(2030, 1, 1));
        $this->assertFalse($last_id);
    }


    /**
     * @test
     * @return void
     */
    public function it_can_archive_a_single_statement(): void
    {
        $admin      = $this->signInAsAdmin();
        $fields_one = $this->required_fields;
        $fields_two = $this->required_fields;

        $fields_one['user_id']     = $admin->id;
        $fields_one['platform_id'] = $admin->platform->id;
        $fields_one['puid'] = 'statement_one_puid';

        $fields_two['user_id']     = $admin->id;
        $fields_two['platform_id'] = $admin->platform->id;
        $fields_two['puid'] = 'statement_two_puid';

        $this->assertDatabaseCount(Statement::class, 10);

        $statement_one = Statement::create($fields_one);
        $statement_two = Statement::create($fields_two);

        $this->assertDatabaseCount(ArchivedStatement::class, 0);
        $this->assertDatabaseCount(Statement::class, 12);

        $statement_one_id = $statement_one->id;
        $statement_one_puid = $statement_one->puid;
        $statement_one_uuid = $statement_one->uuid;
        $statement_one_platform_id = $statement_one->platform_id;
        $statement_one_created_at = $statement_one->created_at->clone();


        $archived_statement_one = $this->day_archive_service->archiveStatement($statement_one);

        $this->assertDatabaseCount(Statement::class, 11);
        $this->assertDatabaseCount(ArchivedStatement::class, 1);

        $this->assertEquals($statement_one_id, $archived_statement_one->original_id);
        $this->assertEquals($statement_one_puid, $archived_statement_one->puid);
        $this->assertEquals($statement_one_uuid, $archived_statement_one->uuid);
        $this->assertEquals($statement_one_platform_id, $archived_statement_one->platform_id);
        $this->assertEquals($statement_one_created_at->timestamp, $archived_statement_one->date_received);

    }

    /**
     * @test
     * @return void
     */
    public function it_can_archive_a_batch_of_statements(): void
    {
        $admin      = $this->signInAsAdmin();
        $fields_one = $this->required_fields;
        $fields_two = $this->required_fields;

        $fields_one['user_id']     = $admin->id;
        $fields_one['platform_id'] = $admin->platform->id;
        $fields_one['puid'] = 'statement_one_puid';

        $fields_two['user_id']     = $admin->id;
        $fields_two['platform_id'] = $admin->platform->id;
        $fields_two['puid'] = 'statement_two_puid';

        $this->assertDatabaseCount(Statement::class, 10);

        $statement_one = Statement::create($fields_one);
        $statement_two = Statement::create($fields_two);

        $this->assertDatabaseCount(ArchivedStatement::class, 0);
        $this->assertDatabaseCount(Statement::class, 12);

        $statement_one_id = $statement_one->id;
        $statement_one_puid = $statement_one->puid;
        $statement_one_uuid = $statement_one->uuid;
        $statement_one_platform_id = $statement_one->platform_id;
        $statement_one_created_at = $statement_one->created_at->clone();


        $this->day_archive_service->archiveStatementsFromIds([$statement_one->id, $statement_two->id]);

        $this->assertDatabaseCount(Statement::class, 10);
        $this->assertDatabaseCount(ArchivedStatement::class, 2);

        $archived_statement_one = ArchivedStatement::first();

        $this->assertEquals($statement_one_id, $archived_statement_one->original_id);
        $this->assertEquals($statement_one_puid, $archived_statement_one->puid);
        $this->assertEquals($statement_one_uuid, $archived_statement_one->uuid);
        $this->assertEquals($statement_one_platform_id, $archived_statement_one->platform_id);
        $this->assertEquals($statement_one_created_at->timestamp, $archived_statement_one->date_received);
    }

    /**
     * @test
     * @return void
     */
    public function when_it_archives_it_clears_out_old(): void
    {
        $admin      = $this->signInAsAdmin();
        $fields_one = $this->required_fields;
        $fields_two = $this->required_fields;

        $fields_one['user_id']     = $admin->id;
        $fields_one['platform_id'] = $admin->platform->id;
        $fields_one['puid'] = 'statement_one_puid';

        $fields_two['user_id']     = $admin->id;
        $fields_two['platform_id'] = $admin->platform->id;
        $fields_two['puid'] = 'statement_two_puid';

        $this->assertDatabaseCount(Statement::class, 10);

        $statement_one = Statement::create($fields_one);
        $statement_two = Statement::create($fields_two);

        $old_archived_statement = ArchivedStatement::create([
            'original_id' => $statement_one->id,
            'uuid' => $statement_one->uuid,
            'platform_id' => $statement_one->platform_id,
            'puid' => $statement_one->puid,
            'date_received' => $statement_one->created_at
        ]);

        $old_non_related_archived_statement = ArchivedStatement::create([
            'original_id' => 999,
            'uuid' => $statement_one->uuid,
            'platform_id' => $statement_one->platform_id,
            'puid' => $statement_one->puid,
            'date_received' => $statement_one->created_at
        ]);

        $this->assertDatabaseCount(ArchivedStatement::class, 2);
        $this->assertDatabaseCount(Statement::class, 12);

        $statement_two_id = $statement_two->id;
        $statement_two_puid = $statement_two->puid;
        $statement_two_uuid = $statement_two->uuid;
        $statement_two_platform_id = $statement_two->platform_id;

        $archived_statement_one = ArchivedStatement::first();
        $this->assertEquals(1, $archived_statement_one->id);

        $this->day_archive_service->archiveStatementsFromIds([$statement_one->id, $statement_two->id, 'chewbacca']);

        $this->assertDatabaseCount(Statement::class, 10);
        $this->assertDatabaseCount(ArchivedStatement::class, 3); // This doesn't go to 4

        $archived_statement_one = ArchivedStatement::first();
        $archived_statement_two = ArchivedStatement::orderBy('id', 'DESC')->first();

        $this->assertEquals(2, $archived_statement_one->id); // 1 should have been deleted and now 2.
        $this->assertEquals(999, $archived_statement_one->original_id); // The non related one should be the first one and untouched.

        $this->assertEquals($statement_two_id, $archived_statement_two->original_id);
        $this->assertEquals($statement_two_puid, $archived_statement_two->puid);
        $this->assertEquals($statement_two_uuid, $archived_statement_two->uuid);
        $this->assertEquals($statement_two_platform_id, $archived_statement_two->platform_id);
    }
}
