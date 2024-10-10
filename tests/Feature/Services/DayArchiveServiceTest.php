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
    public function it_gets_the_first_id_from_date_in_the_first_minute(): void
    {

        $admin      = $this->signInAsAdmin();
        $fields_one = $this->required_fields;
        $fields_two = $this->required_fields;

        $fields_one['user_id']     = $admin->id;
        $fields_one['platform_id'] = $admin->platform->id;
        $fields_one['created_at']  = '2030-01-01 00:00:05';

        $fields_two['puid']        = 'TK422';
        $fields_two['user_id']     = $admin->id;
        $fields_two['platform_id'] = $admin->platform->id;
        $fields_two['created_at']  = '2030-01-01 00:00:10';

        $statement_one = Statement::create($fields_one);
        $statement_two = Statement::create($fields_two);

        $first_id = $this->day_archive_service->getFirstIdOfDate(Carbon::createFromDate(2030, 1, 1));

        $this->assertEquals($statement_one->id, $first_id);
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
    public function it_gets_the_last_id_from_date_in_the_last_minute(): void
    {

        $admin      = $this->signInAsAdmin();
        $fields_one = $this->required_fields;
        $fields_two = $this->required_fields;

        $fields_one['user_id']     = $admin->id;
        $fields_one['platform_id'] = $admin->platform->id;
        $fields_one['created_at']  = '2030-01-01 23:59:45'; // also prove the while loop works

        $fields_two['puid']        = 'TK422';
        $fields_two['user_id']     = $admin->id;
        $fields_two['platform_id'] = $admin->platform->id;
        $fields_two['created_at']  = '2030-01-01 23:59:45';

        $statement_one = Statement::create($fields_one);
        $statement_two = Statement::create($fields_two);

        $first_id = $this->day_archive_service->getLastIdOfDate(Carbon::createFromDate(2030, 1, 1));

        $this->assertEquals($statement_two->id, $first_id);
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
}
