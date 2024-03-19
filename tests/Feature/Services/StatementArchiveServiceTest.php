<?php

namespace Feature\Services;

use App\Models\ArchivedStatement;
use App\Models\Statement;
use App\Services\StatementArchiveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatementArchiveServiceTest extends TestCase
{

    use RefreshDatabase;

    protected StatementArchiveService $statement_archive_service;

    private array $required_fields;

    protected function setUp(): void
    {
        parent::setUp();
        $this->statement_archive_service = app(StatementArchiveService::class);
        $this->assertNotNull($this->statement_archive_service);

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

        $statement_two_id = $statement_two->id;
        $statement_two_puid = $statement_two->puid;
        $statement_two_uuid = $statement_two->uuid;
        $statement_two_platform_id = $statement_two->platform_id;
        $statement_two_created_at = $statement_two->created_at->clone();


        $archived_statement_one = $this->statement_archive_service->archiveStatement($statement_one);
        $archived_statement_two = $this->statement_archive_service->archiveStatement($statement_two);

        $this->assertDatabaseCount(Statement::class, 10);
        $this->assertDatabaseCount(ArchivedStatement::class, 2);

        $this->assertEquals($statement_one_id, $archived_statement_one->original_id);
        $this->assertEquals($statement_one_puid, $archived_statement_one->puid);
        $this->assertEquals($statement_one_uuid, $archived_statement_one->uuid);
        $this->assertEquals($statement_one_platform_id, $archived_statement_one->platform_id);
        $this->assertEquals($statement_one_created_at->timestamp, $archived_statement_one->date_received);

        $this->assertEquals($statement_two_id, $archived_statement_two->original_id);
        $this->assertEquals($statement_two_puid, $archived_statement_two->puid);
        $this->assertEquals($statement_two_uuid, $archived_statement_two->uuid);
        $this->assertEquals($statement_two_platform_id, $archived_statement_two->platform_id);
        $this->assertEquals($statement_two_created_at->timestamp, $archived_statement_two->date_received);

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

        $statement_two_id = $statement_two->id;
        $statement_two_puid = $statement_two->puid;
        $statement_two_uuid = $statement_two->uuid;
        $statement_two_platform_id = $statement_two->platform_id;
        $statement_two_created_at = $statement_two->created_at->clone();


        $this->statement_archive_service->archiveStatementsFromIds([$statement_one->id, $statement_two->id, 'chewbacca']);

        $this->assertDatabaseCount(Statement::class, 10);
        $this->assertDatabaseCount(ArchivedStatement::class, 2);

        $archived_statement_one = ArchivedStatement::first();
        $archived_statement_two = ArchivedStatement::orderBy('id', 'desc')->first();

        $this->assertEquals($statement_one_id, $archived_statement_one->original_id);
        $this->assertEquals($statement_one_puid, $archived_statement_one->puid);
        $this->assertEquals($statement_one_uuid, $archived_statement_one->uuid);
        $this->assertEquals($statement_one_platform_id, $archived_statement_one->platform_id);
        $this->assertEquals($statement_one_created_at->timestamp, $archived_statement_one->date_received);

        $this->assertEquals($statement_two_id, $archived_statement_two->original_id);
        $this->assertEquals($statement_two_puid, $archived_statement_two->puid);
        $this->assertEquals($statement_two_uuid, $archived_statement_two->uuid);
        $this->assertEquals($statement_two_platform_id, $archived_statement_two->platform_id);
        $this->assertEquals($statement_two_created_at->timestamp, $archived_statement_two->date_received);
    }
}
