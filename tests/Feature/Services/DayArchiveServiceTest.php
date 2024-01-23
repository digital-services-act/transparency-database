<?php

namespace Tests\Feature\Services;

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

    public function setUp(): void
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
        $this->setUpFullySeededDatabase();
        $admin      = $this->signInAsAdmin();
        $fields_one = $this->required_fields;
        $fields_two = $this->required_fields;

        $fields_one['user_id']     = $admin->id;
        $fields_one['platform_id'] = $admin->platform->id;
        $fields_one['created_at']  = '2030-01-01 00:00:09'; // also prove the while loop works

        $fields_two['puid']        = 'TK422';
        $fields_two['user_id']     = $admin->id;
        $fields_two['platform_id'] = $admin->platform->id;
        $fields_two['created_at']  = '2030-01-01 00:00:09';

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
    public function it_gets_zero_on_first(): void
    {
        $this->setUpFullySeededDatabase();
        $this->signInAsAdmin();

        $first_id = $this->day_archive_service->getFirstIdOfDate(Carbon::createFromDate(2030, 1, 1));

        $this->assertEquals(0, $first_id);
    }

    /**
     * @return void
     * @test
     */
    public function it_builds_a_nice_array_for_the_start_of_a_date(): void
    {
        $in = $this->day_archive_service->buildStartOfDateArray(Carbon::now());
        $this->assertNotNull($in);
        $this->assertCount(10, $in);
        $this->assertContains(Carbon::now()->format('Y-m-d 00:00:00'), $in);
        $this->assertContains(Carbon::now()->format('Y-m-d 00:00:09'), $in);
    }

    /**
     * @return void
     * @test
     */
    public function it_builds_a_nice_array_for_the_end_of_a_date(): void
    {
        $in = $this->day_archive_service->buildEndOfDateArray(Carbon::now());
        $this->assertNotNull($in);
        $this->assertCount(10, $in);
        $this->assertContains(Carbon::now()->format('Y-m-d 23:59:59'), $in);
        $this->assertContains(Carbon::now()->format('Y-m-d 23:59:50'), $in);
    }

    /**
     * @return void
     * @test
     */
    public function it_builds_a_starting_array(): void
    {
        $this->setUpFullySeededDatabase();
        $result = $this->day_archive_service->buildStartingDayArchivesArray(Carbon::yesterday());
        $this->assertNotNull($result);
        $this->assertCount(20, $result);
        $this->assertEquals('global', $result[0]['slug']);
        $this->assertCount(20, DayArchive::all());
    }

    /**
     * @test
     * @return void
     */
    public function it_gets_the_last_id_from_date(): void
    {
        $this->setUpFullySeededDatabase();
        $admin      = $this->signInAsAdmin();
        $fields_one = $this->required_fields;
        $fields_two = $this->required_fields;

        $fields_one['user_id']     = $admin->id;
        $fields_one['platform_id'] = $admin->platform->id;
        $fields_one['created_at']  = '2030-01-01 23:59:51'; // also prove the while loop works

        $fields_two['puid']        = 'TK422';
        $fields_two['user_id']     = $admin->id;
        $fields_two['platform_id'] = $admin->platform->id;
        $fields_two['created_at']  = '2030-01-01 23:59:51';

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
        $this->setUpFullySeededDatabase();
        $this->signInAsAdmin();

        $last_id = $this->day_archive_service->getLastIdOfDate(Carbon::createFromDate(2030, 1, 1));

        $this->assertEquals(0, $last_id);
    }
}
